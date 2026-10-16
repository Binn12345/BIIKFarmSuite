<?php

namespace App\Support;

class PermissionCatalog
{
    /**
     * @return array<string, array{label: string, permissions: array<string, string>}>
     */
    public static function modules(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'permissions' => ['view' => 'dashboard.view'],
            ],
            'animals' => [
                'label' => 'Animal Management',
                'permissions' => [
                    'view' => 'animals.view',
                    'create' => 'animals.create',
                    'update' => 'animals.update',
                    'delete' => 'animals.delete',
                ],
            ],
            'animal-categories' => [
                'label' => 'Animal Categories',
                'permissions' => [
                    'view' => 'animal-categories.view',
                    'create' => 'animal-categories.create',
                    'update' => 'animal-categories.update',
                    'delete' => 'animal-categories.delete',
                ],
            ],
            'breeds' => [
                'label' => 'Breed Management',
                'permissions' => [
                    'view' => 'breeds.view',
                    'create' => 'breeds.create',
                    'update' => 'breeds.update',
                    'delete' => 'breeds.delete',
                ],
            ],
            'piglets' => [
                'label' => 'Piglet Records',
                'permissions' => [
                    'view' => 'piglets.view',
                    'create' => 'piglets.create',
                    'update' => 'piglets.update',
                    'delete' => 'piglets.delete',
                ],
            ],
            'pens' => [
                'label' => 'Pens Management',
                'permissions' => [
                    'view' => 'pens.view',
                    'create' => 'pens.create',
                    'update' => 'pens.update',
                    'delete' => 'pens.delete',
                ],
            ],
            'breeding' => [
                'label' => 'Breeding',
                'permissions' => [
                    'view' => 'breeding.view',
                    'create' => 'breeding.create',
                    'update' => 'breeding.update',
                    'delete' => 'breeding.delete',
                ],
            ],
            'farrowing' => [
                'label' => 'Farrowing',
                'permissions' => [
                    'view' => 'farrowing.view',
                    'create' => 'farrowing.create',
                    'update' => 'farrowing.update',
                    'delete' => 'farrowing.delete',
                ],
            ],
            'health' => [
                'label' => 'Health and Veterinary Records',
                'permissions' => [
                    'view' => 'health.view',
                    'create' => 'health.create',
                    'update' => 'health.update',
                    'delete' => 'health.delete',
                ],
            ],
            'feeding' => [
                'label' => 'Feeding Management',
                'permissions' => [
                    'view' => 'feeding.view',
                    'create' => 'feeding.create',
                    'update' => 'feeding.update',
                    'delete' => 'feeding.delete',
                ],
            ],
            'mortality' => [
                'label' => 'Mortality Records',
                'permissions' => [
                    'view' => 'mortality.view',
                    'create' => 'mortality.create',
                    'update' => 'mortality.update',
                ],
            ],
            'inventory' => [
                'label' => 'Inventory Management',
                'permissions' => [
                    'view' => 'inventory.view',
                    'create' => 'inventory.create',
                    'update' => 'inventory.update',
                    'delete' => 'inventory.delete',
                ],
            ],
            'sales' => [
                'label' => 'Farm Sales',
                'permissions' => [
                    'view' => 'sales.view',
                    'create' => 'sales.create',
                    'update' => 'sales.update',
                    'cancel' => 'sales.cancel',
                ],
            ],
            'customers' => [
                'label' => 'Customers',
                'permissions' => [
                    'view' => 'customers.view',
                    'create' => 'customers.create',
                    'update' => 'customers.update',
                    'delete' => 'customers.delete',
                ],
            ],
            'suppliers' => [
                'label' => 'Supplier Management',
                'permissions' => [
                    'view' => 'suppliers.view',
                    'create' => 'suppliers.create',
                    'update' => 'suppliers.update',
                    'delete' => 'suppliers.delete',
                ],
            ],
            'purchases' => [
                'label' => 'Purchase Management',
                'permissions' => [
                    'view' => 'purchases.view',
                    'create' => 'purchases.create',
                    'update' => 'purchases.update',
                    'receive' => 'purchases.receive',
                    'cancel' => 'purchases.cancel',
                ],
            ],
            'expenses' => [
                'label' => 'Farm Expenses',
                'permissions' => [
                    'view' => 'expenses.view',
                    'create' => 'expenses.create',
                    'update' => 'expenses.update',
                    'delete' => 'expenses.delete',
                ],
            ],
            'reports' => [
                'label' => 'Farm Reports',
                'permissions' => ['view' => 'reports.view'],
            ],
            'users' => [
                'label' => 'User Management',
                'permissions' => [
                    'view' => 'users.view',
                    'create' => 'users.create',
                    'update' => 'users.update',
                    'deactivate' => 'users.deactivate',
                ],
            ],
            'roles' => [
                'label' => 'Role Management',
                'permissions' => [
                    'manage' => 'roles.manage',
                    'permissions.manage' => 'permissions.manage',
                ],
            ],
            'audit-logs' => [
                'label' => 'Audit Trail',
                'permissions' => ['view' => 'audit-logs.view'],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        return collect(self::modules())
            ->flatMap(fn (array $module) => array_values($module['permissions']))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Permissions that must only ever be granted via the administrator role.
     *
     * @return list<string>
     */
    public static function protectedPermissions(): array
    {
        return [
            'users.create',
            'users.update',
            'users.deactivate',
            'roles.manage',
            'permissions.manage',
            'audit-logs.view',
        ];
    }

    /**
     * @return list<string>
     */
    public static function privilegedRoleNames(): array
    {
        return ['admin', 'super-admin'];
    }
}
