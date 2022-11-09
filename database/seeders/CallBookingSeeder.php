<?php

namespace Database\Seeders;

use App\Models\CallPackage;
use Illuminate\Database\Seeder;

class CallBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name'                => '15 Minutes',
                'duration_minutes'    => 15,
                'price'               => 100,
                'discount_percentage' => 0
            ],
            [
                'name'                => '30 Minutes',
                'duration_minutes'    => 30,
                'price'               => 200,
                'discount_percentage' => 30
            ],
            [
                'name'                => '60 Minutes',
                'duration_minutes'    => 60,
                'price'               => 400,
                'discount_percentage' => 50
            ],
            
        ];

        if ($data) {
            foreach ($data as $value) {
                CallPackage::updateOrCreate(['name' => $value['name']], $value);
            }
        }
    }
}
