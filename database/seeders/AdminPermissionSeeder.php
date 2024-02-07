<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load permissions from JSON file
        $permissionsJson = file_get_contents(database_path('seeders/data/permissions.json'));
        $permissionsArray = json_decode($permissionsJson, true);

        // Create Admin role if not exists
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // Assign all permissions to Admin role
        foreach ($permissionsArray['permissions'] as $modulePermissions) {
            foreach ($modulePermissions as $permissionName => $permissionLabel) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                $adminRole->givePermissionTo($permission);
            }
        }
    }
}
