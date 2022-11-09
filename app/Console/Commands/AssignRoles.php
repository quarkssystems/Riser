<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign roles to existing users based on value (eUserType) in database';

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
        $oldRoles = [
            'User' => config('constant.roles.user'),
            'Creator' => config('constant.roles.creator'),
            'Agent' => config('constant.roles.agent'),
            'DistrictLeader' => config('constant.roles.district-leader'),
            'StateLeader' => config('constant.roles.state-leader'),
            'CoreTeamMember' => config('constant.roles.core-team'),
            'Admin' => config('constant.roles.admin')
        ];

        User::chunkById(100, function($users) use($oldRoles) {
            foreach($users as $user) {
                if(array_key_exists($user->eUserType, $oldRoles)){
                    $user->assignRole(config('constant.roles.user'));
                    $user->assignRole($oldRoles[$user->eUserType]);
                }                
            }
        });
    }
}
