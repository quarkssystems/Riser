<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\MasterClass;
use App\Models\PaymentPayout;
use Illuminate\Console\Command;
use App\Models\PaymentPercentage;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class MasterClassPayout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masterclass:payout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Master Class Payout after end date and time';

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

        $percentageArr = getPaymentPercentage('master_class_direct');

        if($percentageArr){
            PaymentTransaction::selectRaw('id, master_class_id, sum(total) as total')
            ->where('payment_settled', 'no')
            ->whereNull('affiliate_user_id')
            ->whereHas('masterClasses', function ($query) use($today) {
                $query->select(['id', 'payment_settled', DB::raw('CONCAT(start_date," ", start_time) AS master_class_date_time')]);
                $query->where('payment_settled', 'no');
                $query->having('master_class_date_time', '<=', $today);
            })
            ->groupBy('master_class_id')
            ->orderBy('master_class_id')
            ->chunkById(100, function($paymentTransactions) use($percentageArr){
                foreach($paymentTransactions as $transaction) {
                    if($transaction->total <= 0){
                        continue;
                    }
                    paymentSettle($transaction, $percentageArr, 'master_class_direct');

                }
            });
        }
    }
}
