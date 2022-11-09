<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'create-user']);
        Permission::create(['name' => 'read-user']);
        Permission::create(['name' => 'update-user']);
        Permission::create(['name' => 'delete-user']);

        Permission::create(['name' => 'create-post']);
        Permission::create(['name' => 'read-post']);
        Permission::create(['name' => 'update-post']);
        Permission::create(['name' => 'delete-post']);

        Permission::create(['name' => 'create-hashtag']);
        Permission::create(['name' => 'read-hashtag']);
        Permission::create(['name' => 'update-hashtag']);
        Permission::create(['name' => 'delete-hashtag']);

        $role1 = Role::create(['name' => 'superadmin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create roles and assign existing permissions
        $role2 = Role::create(['name' => 'admin']);
        $role2->givePermissionTo('create-user');
        $role2->givePermissionTo('read-user');
        $role2->givePermissionTo('update-user');
        $role2->givePermissionTo('create-post');
        $role2->givePermissionTo('read-post');
        $role2->givePermissionTo('update-post');
        $role2->givePermissionTo('delete-post');
        $role2->givePermissionTo('create-hashtag');
        $role2->givePermissionTo('read-hashtag');
        $role2->givePermissionTo('update-hashtag');
        $role2->givePermissionTo('delete-hashtag');

        $role3 = Role::create(['name' => 'creator']);
        $role3->givePermissionTo('read-user');
        $role3->givePermissionTo('create-post');
        $role3->givePermissionTo('read-post');
        $role3->givePermissionTo('update-post');
        $role3->givePermissionTo('delete-post');
        $role3->givePermissionTo('create-hashtag');
        $role3->givePermissionTo('read-hashtag');

        $role4 = Role::create(['name' => 'agent']);
        $role4->givePermissionTo('read-user');
        $role4->givePermissionTo('read-post');
        $role4->givePermissionTo('read-hashtag');

        $role5 = Role::create(['name' => 'user']);
        $role5->givePermissionTo('read-user');
        $role5->givePermissionTo('read-post');
        $role5->givePermissionTo('read-hashtag');

        $role6 = Role::create(['name' => 'district-leader']);
        $role6->givePermissionTo('read-user');
        $role6->givePermissionTo('read-post');
        $role6->givePermissionTo('read-hashtag');

        $role7 = Role::create(['name' => 'state-leader']);
        $role7->givePermissionTo('read-user');
        $role7->givePermissionTo('read-post');
        $role7->givePermissionTo('read-hashtag');

        $role8 = Role::create(['name' => 'core-team']);
        $role8->givePermissionTo('read-user');
        $role8->givePermissionTo('create-post');
        $role8->givePermissionTo('read-post');
        $role8->givePermissionTo('update-post');
        $role8->givePermissionTo('delete-post');
        $role8->givePermissionTo('create-hashtag');
        $role8->givePermissionTo('read-hashtag');

        // create demo users
        $user = \App\Models\User::factory()->create([
            'vFirstName' => 'Super',
            'vLastName' => 'Admin',
            'vEmail' => 'superadmin@riserapp.in',
        ]);
        $user->assignRole($role1);

        $user = \App\Models\User::factory()->create([
            'vFirstName' => 'Admin',
            'vLastName' => 'User',
            'vEmail' => 'admin@riserapp.in',
        ]);
        $user->assignRole($role2);

        $user = \App\Models\User::factory()->create([
            'vFirstName' => 'Creator',
            'vLastName' => 'User',
            'vEmail' => 'creator@riserapp.in',
        ]);
        $user->assignRole($role3);

        $user = \App\Models\User::factory()->create([
            'vFirstName' => 'Agent',
            'vLastName' => 'User',
            'vEmail' => 'agent@riserapp.in',
        ]);
        $user->assignRole($role4);

        $user = \App\Models\User::factory()->create([
            'vFirstName' => 'User',
            'vLastName' => 'User',
            'vEmail' => 'user@riserapp.in',
        ]);
        $user->assignRole($role5);

        $user = \App\Models\User::factory()->create([
            'vFirstName' => 'District',
            'vLastName' => 'Leader',
            'vEmail' => 'dleader@riserapp.in',
        ]);
        $user->assignRole($role6);

        $user = \App\Models\User::factory()->create([
            'vFirstName' => 'State',
            'vLastName' => 'Leader',
            'vEmail' => 'sleader@riserapp.in',
        ]);
        $user->assignRole($role7);
    }
}
