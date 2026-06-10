<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions (model based)
        $actions = [
            'create', 'index', 'edit', 'destroy', 'report', 'show', 'print'
        ];
        $models = [
            'permission', 'role', 'user',
        ];

        foreach ($actions as $action) {
            foreach ($models as $model) {
                $name = $action.'-'.$model;
                Permission::firstOrCreate(['name' => $name]);
            }
        }

        // Roles
        $superadmin = Role::firstOrCreate(['name' => 'Superadmin']);

        // Assign permissions
        $superadmin->givePermissionTo(Permission::all());

    }
}
