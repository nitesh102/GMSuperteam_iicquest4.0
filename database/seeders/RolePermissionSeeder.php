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
        $admin      = Role::firstOrCreate(['name' => 'Admin']);
        $employee   = Role::firstOrCreate(['name' => 'Employee']);

        $technician   = Role::firstOrCreate(['name' => 'Technician']);

        // Assign permissions
        $superadmin->givePermissionTo(Permission::all());
        $admin->givePermissionTo([
            'index-category', 'create-category', 'edit-category', 'destroy-category', 'show-category',
            'index-sub_cateegory', 'create-sub_cateegory', 'edit-sub_cateegory', 'destroy-sub_cateegory', 'show-sub_cateegory',
            'index-measuring_unit', 'create-measuring_unit', 'edit-measuring_unit', 'destroy-measuring_unit', 'show-measuring_unit',
            'index-tax', 'create-tax', 'edit-tax', 'destroy-tax', 'show-tax',
            'index-product', 'create-product', 'edit-product', 'destroy-product', 'show-product',
        ]);

    }
}
