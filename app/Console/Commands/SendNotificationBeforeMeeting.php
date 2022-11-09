<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\MasterClass;
use Illuminate\Console\Command;
use App\Notifications\BeforeMasterClass;
use Illuminate\Support\Facades\Notification;

class SendNotificationBeforeMeeting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:master-class-meeting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send master class notification before 15 minutes start time';

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

        MasterClass::status()
        ->whereDate('start_date', $today)
        ->whereBetween('start_time', [$today->format('H:i'), $today->addMinutes(15)->format('H:i')])
        ->where('notification_sent', 'no')
        ->with(['bookingUsers','user'])
        ->chunkById(10, function($masterClasses){

            foreach($masterClasses as $masterClass) {
                //notification to creator
                Notification::send($masterClass->user, new BeforeMasterClass("Reminder to start your master class - ".$masterClass->title, $masterClass));
                
                //notification to all booking users
                Notification::send($masterClass->bookingUsers, new BeforeMasterClass("Reminder to join your master class - ".$masterClass->title, $masterClass));
                
                $masterClass->notification_sent = 'yes';
                $masterClass->save();
            }
            
        });
    }
}
