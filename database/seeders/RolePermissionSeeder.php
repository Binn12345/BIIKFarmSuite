<?php

namespace Database\Seeders;

use App\Support\FarmModules;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = FarmModules::permissions();

        $permissions = collect($permissionNames)
            ->map(fn (string $name) => Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]));

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo($permissions->values());

        Role::firstOrCreate(['name' => 'basic-user', 'guard_name' => 'web'])
            ->givePermissionTo(Permission::findByName('dashboard.view', 'web'));

        foreach ([
            'farm-supervisor',
            'farm-staff',
            'sales-staff',
        ] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])
                ->givePermissionTo(Permission::findByName('dashboard.view', 'web'));
        }
    }
}
