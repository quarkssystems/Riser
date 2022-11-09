<?php

namespace Database\Seeders;

use App\Models\MasterBannerCategory;
use Illuminate\Database\Seeder;

class BannerCategoriesSeeder extends Seeder
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
                'name' => 'Search Screen',
                'slug' => 'search-screen',
            ],
            [
                'name' => 'Master Class Category Screen',
                'slug' => 'master-class-category-screen',
            ],
        ];

        if ($data) {
            foreach ($data as $value) {
                MasterBannerCategory::updateOrCreate(['name' => $value['name']], $value);
            }
        }
    }
}
