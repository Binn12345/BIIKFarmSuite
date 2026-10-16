<?php

namespace App\Support;

use App\Models\User;
use App\Support\FarmRecordCatalog;

class DashboardModules
{
    /**
     * Return only implemented modules available to the user.
     *
     * @return array<string, list<array{title: string, description: string, route: string, permission: string, icon: string}>>
     */
    public static function for(User $user): array
    {
        $modules = [
            'Farm management' => [
                [
                    'title' => 'Animal Management',
                    'description' => 'Register animals, manage identification, and view animal details.',
                    'route' => 'modules.animals',
                    'permission' => 'animals.view',
                    'icon' => 'animal',
                ],
                [
                    'title' => 'Pens Management',
                    'description' => 'Manage pen capacity, location, status, and assigned animals.',
                    'route' => 'modules.pens',
                    'permission' => 'pens.view',
                    'icon' => 'pen',
                ],
            ],
            'Business management' => [],
            'Reports' => [[
                'title' => 'Farm Reports',
                'description' => 'View database-backed operational and financial summaries.',
                'route' => 'modules.reports',
                'permission' => 'reports.view',
                'icon' => 'list',
            ]],
            'Administration' => [],
        ];

        foreach (FarmRecordCatalog::modules() as $slug => $catalog) {
            $category = in_array($slug, ['customers', 'suppliers', 'purchases', 'sales', 'expenses'], true)
                ? 'Business management'
                : 'Farm management';
            $modules[$category][] = [
                'title' => $catalog['title'],
                'description' => $catalog['description'],
                'route' => 'modules.'.$slug,
                'permission' => $slug.'.view',
                'icon' => 'animal',
            ];
        }

        if ($user->hasRole('admin')) {
            $modules['Administration'] = [
                [
                    'title' => 'User Management',
                    'description' => 'Search accounts, manage roles, and control account access.',
                    'route' => 'admin.users',
                    'permission' => 'users.view',
                    'icon' => 'users',
                ],
                [
                    'title' => 'Roles and Permissions',
                    'description' => 'Configure roles and module permissions for users.',
                    'route' => 'admin.roles',
                    'permission' => 'roles.manage',
                    'icon' => 'shield',
                ],
                [
                    'title' => 'Permission Catalog',
                    'description' => 'Review the permissions supported by FarmSuite.',
                    'route' => 'admin.permissions',
                    'permission' => 'permissions.manage',
                    'icon' => 'list',
                ],
                [
                    'title' => 'Audit Trail',
                    'description' => 'Review authentication and access-control events.',
                    'route' => 'admin.audit-logs',
                    'permission' => 'audit-logs.view',
                    'icon' => 'clock',
                ],
            ];
        }

        foreach ($modules as $category => $items) {
            $modules[$category] = array_values(array_filter(
                $items,
                fn (array $module) => $user->can($module['permission']),
            ));
        }

        return array_filter($modules, fn (array $items) => $items !== []);
    }
}
