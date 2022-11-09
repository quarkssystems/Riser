<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CallBooking;
use Illuminate\Console\Command;

class CallBookingMissedUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callbooking:missed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update call booking to missed, if user did not join call';

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

        CallBooking::where('status', config('constant.status.booked_value'))
        ->whereRaw('CONCAT(booking_date," ", end_time) <= "'.$today.'"')
        ->chunkById(100, function($callBookings){
            foreach($callBookings as $callBooking) {
                $callBooking->status = config('constant.status.missed_value');
                $callBooking->save();
            }
        });
    }
}
