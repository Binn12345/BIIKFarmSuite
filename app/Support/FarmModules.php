<?php

namespace App\Support;

class FarmModules
{
    /**
     * @return array<string, array{label: string, permission: string}>
     */
    public static function all(): array
    {
        return [
            'animals' => ['label' => 'Animal Management', 'permission' => 'animals.view'],
            'breeding' => ['label' => 'Breeding and Farrowing', 'permission' => 'breeding.view'],
            'piglets' => ['label' => 'Piglet Records', 'permission' => 'piglets.view'],
            'sales' => ['label' => 'Farm Sales', 'permission' => 'sales.view'],
            'expenses' => ['label' => 'Farm Expenses', 'permission' => 'expenses.view'],
            'reports' => ['label' => 'Farm Reports', 'permission' => 'reports.view'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        return array_values(array_column(self::all(), 'permission'));
    }
}
