<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterLanguages;

class MasterLanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         MasterLanguages::factory()->count(1)->create();
    }
}
