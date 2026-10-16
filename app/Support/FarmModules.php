<?php

namespace App\Support;

class FarmModules
{
    /**
     * @return array<string, array{label: string, permission: string}>
     */
    public static function all(): array
    {
        return collect(PermissionCatalog::modules())
            ->filter(fn (array $module, string $key) => ! in_array($key, ['dashboard', 'users', 'roles', 'audit-logs'], true))
            ->map(fn (array $module) => [
                'label' => $module['label'],
                'permission' => $module['permissions']['view'] ?? $module['permissions']['create'],
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        return PermissionCatalog::permissions();
    }
}
