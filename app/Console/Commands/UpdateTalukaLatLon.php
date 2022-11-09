<?php

namespace App\Console\Commands;

use App\Models\MasterTaluka;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateTalukaLatLon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taluka:lat-lon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Taluka latitude longitude';

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
        MasterTaluka::whereNull('latitude')
            ->where('longitude')
            ->with('district','district.state')
            ->chunkById(100, function($talukas) {
                foreach($talukas as $taluka) {

                    $address = $taluka->name." ".$taluka->district->name." ".$taluka->district->state->name;                   
                    $response = Http::get('http://api.positionstack.com/v1/forward?access_key=ade9f5766d0787b7f79e0c9bb36fb516&query='.$address);

                    if($response->ok()){
                        $data = $response->json();
                        if($data && count($data['data']) > 0){
                            $taluka->latitude = $data['data'][0]['latitude'];
                            $taluka->longitude = $data['data'][0]['longitude'];
                            $taluka->save();
                        }
                        
                    }
                }
            });
    }
}
