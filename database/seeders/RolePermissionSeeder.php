<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan cache permission Spatie agar seeder aman dijalankan berulang.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        // Modul CRUD standar: {modul}.{aksi}
        $crudModules = ['category', 'unit', 'supplier', 'product'];
        $crudActions = ['view', 'create', 'update', 'delete'];

        $permissions = [];

        foreach ($crudModules as $module) {
            foreach ($crudActions as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        // Permission khusus (di luar CRUD standar)
        $permissions = array_merge($permissions, [
            'inbound.view',
            'inbound.create',
            'outbound.view',
            'outbound.create',
            'stock-movement.view',
            'stock-adjustment.approve',
            'report.view',
            'user.manage',
            'role.manage',
        ]);

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // Super Admin: semua permission (selain itu juga lolos lewat Gate::before)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard]);
        $superAdmin->syncPermissions(Permission::all());

        // Kepala Gudang: laporan, approve penyesuaian stok, kelola supplier
        $kepalaGudang = Role::firstOrCreate(['name' => 'Kepala Gudang', 'guard_name' => $guard]);
        $kepalaGudang->syncPermissions([
            'supplier.view',
            'supplier.create',
            'supplier.update',
            'supplier.delete',
            'category.view',
            'unit.view',
            'product.view',
            'inbound.view',
            'outbound.view',
            'stock-movement.view',
            'stock-adjustment.approve',
            'report.view',
        ]);

        // Staff Gudang: catat inbound/outbound dan lihat daftar stok
        $staffGudang = Role::firstOrCreate(['name' => 'Staff Gudang', 'guard_name' => $guard]);
        $staffGudang->syncPermissions([
            'product.view',
            'inbound.view',
            'inbound.create',
            'outbound.view',
            'outbound.create',
        ]);
    }
}