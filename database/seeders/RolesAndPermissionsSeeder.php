<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Four-tier authorization roles:
     * - user: no special permissions - regular member, cannot see the users page.
     * - admin: everything a user can do, plus assigning/editing servants only.
     * - superadmin: everything admin has, plus assigning admins and user types (father/servant).
     * - owner: can do anything in the app, including assigning superadmins.
     */
    public function run(): void
    {
        $manageUsers = Permission::firstOrCreate(['name' => 'manage-users']);
        $manageVisits = Permission::firstOrCreate(['name' => 'manage-visits']);
        $manageRoles = Permission::firstOrCreate(['name' => 'manage-roles']);

        Role::firstOrCreate(['name' => 'user']);

        Role::firstOrCreate(['name' => 'admin'])
            ->syncPermissions([$manageUsers, $manageVisits]);

        Role::firstOrCreate(['name' => 'superadmin'])
            ->syncPermissions([$manageUsers, $manageVisits, $manageRoles]);

        Role::firstOrCreate(['name' => 'owner'])
            ->syncPermissions([$manageUsers, $manageVisits, $manageRoles]);
    }
}
