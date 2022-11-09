<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\PaymentTransaction;

class CallBookingPayout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callbooking:payout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call Booking Payout after based on status booked, attended or missed';

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

        $percentageArr = getPaymentPercentage('call_booking');

        if($percentageArr){
            PaymentTransaction::selectRaw('id, call_booking_id, sum(total) as total')
            ->where('payment_settled', 'no')
            ->whereHas('callBookings', function ($query) use($today) {
                $query->select(['id', 'payment_settled', 'status']);
                $query->where('payment_settled', 'no');
                $query->whereIn('status', ['booked','attended','missed']);
            })
            ->groupBy('call_booking_id')
            ->orderBy('call_booking_id')
            ->chunkById(100, function($paymentTransactions) use($percentageArr){
                foreach($paymentTransactions as $transaction) {
                    if($transaction->total <= 0){
                        continue;
                    }
                    paymentSettle($transaction, $percentageArr, 'call_booking');

                }
            });
        }
    }
}
