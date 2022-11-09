<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CallBooking;
use Illuminate\Console\Command;
use App\Notifications\BeforeCallBooking;
use Illuminate\Support\Facades\Notification;

class SendNotificationBeforeCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:call-booking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send one to one call booking notification before 10 minutes start time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->setTimezone('Asia/Kolkata');

        CallBooking::status(config('constant.status.booked_value'))
        ->whereDate('booking_date', $today)
        ->whereBetween('start_time', [$today->format('H:i'), $today->addMinutes(10)->format('H:i')])
        ->where('notification_sent', 'no')
        ->with(['user','creator'])
        ->chunkById(10, function($callBookings){

            foreach($callBookings as $callBooking) {
                //notification to creator
                Notification::send($callBooking->creator, new BeforeCallBooking("Reminder to start your call with - ".$callBooking->user->full_name, $callBooking));
                
                //notification to all booking users
                Notification::send($callBooking->user, new BeforeCallBooking("Reminder to join your call with - ".$callBooking->creator->full_name, $callBooking));
                
                $callBooking->notification_sent = 'yes';
                $callBooking->save();
            }
            
        });
    }
}
