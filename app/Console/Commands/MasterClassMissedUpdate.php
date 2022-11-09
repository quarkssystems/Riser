<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\MasterClassUser;
use Illuminate\Console\Command;

class MasterClassMissedUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masterclass:missed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update master class to missed, if user did not join call';

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

        MasterClassUser::where('status', config('constant.status.booked_value'))
        ->with('masterClass')
        ->whereHas('masterClass', function($query) use($today){
            $query->where('is_master_class_ended','1')
            ->whereRaw('CONCAT(start_date," ", start_time) <= "'.$today.'"');
        })
        ->chunkById(100, function($masterClasses){
            foreach($masterClasses as $masterClass) {
                $masterClass->status = config('constant.status.missed_value');
                $masterClass->save();
            }
        });
    }
}
