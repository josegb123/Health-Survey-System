<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'view surveys']);
        Permission::create(['name' => 'view patients']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'create survey templates']);
        Permission::create(['name' => 'edit surveys']);
        Permission::create(['name' => 'delete surveys']);
        Permission::create(['name' => 'delete db']);
        Permission::create(['name' => 'delete signatures']);

        // update cache to know about the newly created permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign created permissions
        Role::create(['name' => 'user'])
            ->givePermissionTo(['view surveys', 'view patients']);

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());


    }
}
