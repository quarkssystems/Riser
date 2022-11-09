<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentPercentage;

class PaymentPercentageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentPercentage = [
            //master_class_direct creator
            [
                'module_name' => 'master_class_direct',
                'role' => 'creator',
                'parent_role' => NULL,
                'percentage' => '60',
                'hiddent_cut_percent' => '10'
            ],
            //master_class_direct agent
            [
                'module_name' => 'master_class_direct',
                'role' => 'agent',
                'parent_role' => 'creator',
                'percentage' => '3',
                'hiddent_cut_percent' => '10'
            ],
            //master_class_direct district leader
            [
                'module_name' => 'master_class_direct',
                'role' => 'district-leader',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'master_class_direct',
                'role' => 'district-leader',
                'parent_role' => 'agent',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            //master_class_direct state leader
            [
                'module_name' => 'master_class_direct',
                'role' => 'state-leader',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'master_class_direct',
                'role' => 'state-leader',
                'parent_role' => 'agent',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'master_class_direct',
                'role' => 'state-leader',
                'parent_role' => 'district-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            //master_class_direct core team member
            [
                'module_name' => 'master_class_direct',
                'role' => 'core-team',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'master_class_direct',
                'role' => 'core-team',
                'parent_role' => 'district-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'master_class_direct',
                'role' => 'core-team',
                'parent_role' => 'state-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
            //master_class_direct admin
            [
                'module_name' => 'master_class_direct',
                'role' => 'admin',
                'parent_role' => NULL,
                'percentage' => '40',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'master_class_direct',
                'role' => 'admin',
                'parent_role' => 'creator',
                'percentage' => '5.5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'master_class_direct',
                'role' => 'admin',
                'parent_role' => 'state-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
            // -----------------------------
            //master_class_affiliate creator
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'creator',
                'parent_role' => NULL,
                'percentage' => '60',
                'hiddent_cut_percent' => '10'
            ],
            //master_class_affiliate agent
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'agent',
                'parent_role' => 'creator',
                'percentage' => '3',
                'hiddent_cut_percent' => '10'
            ],
            //master_class_affiliate district leader
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'district-leader',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'district-leader',
                'parent_role' => 'agent',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            //master_class_affiliate state leader
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'state-leader',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'state-leader',
                'parent_role' => 'agent',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'state-leader',
                'parent_role' => 'district-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            //master_class_affiliate core team member
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'core-team',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'core-team',
                'parent_role' => 'district-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'core-team',
                'parent_role' => 'state-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
            //master_class_affiliate admin
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'admin',
                'parent_role' => NULL,
                'percentage' => '10',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'admin',
                'parent_role' => 'creator',
                'percentage' => '5.5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'admin',
                'parent_role' => 'state-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
            //master_class_affiliate Affiliator
            [
                'module_name' => 'master_class_affiliate',
                'role' => 'affiliator',
                'parent_role' => NULL,
                'percentage' => '30',
                'hiddent_cut_percent' => '0'
            ],
            // ----------------------------------
            //call_booking creator
            [
                'module_name' => 'call_booking',
                'role' => 'creator',
                'parent_role' => NULL,
                'percentage' => '70',
                'hiddent_cut_percent' => '10'
            ],
            //call_booking agent
            [
                'module_name' => 'call_booking',
                'role' => 'agent',
                'parent_role' => 'creator',
                'percentage' => '3',
                'hiddent_cut_percent' => '10'
            ],
            //call_booking district leader
            [
                'module_name' => 'call_booking',
                'role' => 'district-leader',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'call_booking',
                'role' => 'district-leader',
                'parent_role' => 'agent',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            //call_booking state leader
            [
                'module_name' => 'call_booking',
                'role' => 'state-leader',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'call_booking',
                'role' => 'state-leader',
                'parent_role' => 'agent',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            [
                'module_name' => 'call_booking',
                'role' => 'state-leader',
                'parent_role' => 'district-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '10'
            ],
            //call_booking core team member
            [
                'module_name' => 'call_booking',
                'role' => 'core-team',
                'parent_role' => 'creator',
                'percentage' => '0.5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'call_booking',
                'role' => 'core-team',
                'parent_role' => 'district-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'call_booking',
                'role' => 'core-team',
                'parent_role' => 'state-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
            //call_booking admin
            [
                'module_name' => 'call_booking',
                'role' => 'admin',
                'parent_role' => NULL,
                'percentage' => '30',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'call_booking',
                'role' => 'admin',
                'parent_role' => 'creator',
                'percentage' => '5.5',
                'hiddent_cut_percent' => '0'
            ],
            [
                'module_name' => 'call_booking',
                'role' => 'admin',
                'parent_role' => 'state-leader',
                'percentage' => '5',
                'hiddent_cut_percent' => '0'
            ],
        ];

        if ($paymentPercentage) {
            foreach ($paymentPercentage as $percentage) {
                PaymentPercentage::updateOrCreate(['module_name' => $percentage['module_name'], 'role' => $percentage['role'], 'parent_role' => $percentage['parent_role']], $percentage);
            }
        }
    }
}
