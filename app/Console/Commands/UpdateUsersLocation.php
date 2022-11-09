<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\TblCity;
use App\Models\TblState;
use App\Models\MasterState;
use App\Models\TblDistrict;
use App\Models\MasterTaluka;
use App\Models\MasterDistrict;
use Illuminate\Console\Command;

class UpdateUsersLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user location to match state, city and taluka';

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
        User::select('iUserId','iStateId','iDistrictId','iCityId')
        ->chunkById(100, function($users) {
            foreach($users as $user) {
                //OLD Location data
                $state = TblState::where('iStateId', $user->iStateId)->first();
                $district = TblDistrict::where('iDistrictId', $user->iDistrictId)->first();
                $city = TblCity::where('iCityId', $user->iCityId)->first();
                
                //NEW Location data
                if($state && $state->iStateId != NULL){
                    $newState = MasterState::where('name','like', '%'.$state->vState.'%')->first();
                    if($newState){
                        $user->state_id = $newState->id;
                    }
                }
                if($district && $district->iDistrictId != NULL){
                    $newDistrict = MasterDistrict::where('name','like', '%'.$district->vDistrict.'%')->first();
                    if($newDistrict){
                        $user->district_id = $newDistrict->id;
                    }
                }
                if($city && $city->iCityId != NULL){
                    $newCity = MasterTaluka::where('name','like', '%'.$city->vCity.'%')->first();
                    if($newCity){
                        $user->taluka_id = $newCity->id;
                    }
                }
                $user->save();
            }
        });
    }
}
