<?php

namespace Database\Seeders;

use App\Models\CmsPages;
use Illuminate\Database\Seeder;

class CmsPagesSeeder extends Seeder
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
                'page_title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'page_content' => '<p>Privacy Policy</p>'
            ],
            [
                'page_title' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'page_content' => '<p>Terms & Conditions</p>'
            ],
        ];

        if ($data) {
            foreach ($data as $value) {
                CmsPages::updateOrCreate(['slug' => $value['slug']], $value);
            }
        }
    }
}
