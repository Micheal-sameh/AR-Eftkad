<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Three-tier authorization roles:
     * - user: no special permissions - regular member, can create/see all visits.
     * - admin: everything a user can do, plus managing users (list/view/change type).
     * - superadmin: everything admin has, plus assigning roles.
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
    }
}
