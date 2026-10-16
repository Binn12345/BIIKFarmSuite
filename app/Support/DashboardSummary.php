<?php

namespace App\Support;

use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\User;

class DashboardSummary
{
    /**
     * @return list<array{label: string, value: string, detail: string}>
     */
    public static function for(User $user): array
    {
        $summary = [];

        if ($user->can('animals.view')) {
            $summary[] = [
                'label' => 'Active animals',
                'value' => (string) Animal::query()->where('status', 'active')->count(),
                'detail' => 'Current animal inventory',
            ];
        }

        if ($user->can('piglets.view')) {
            $summary[] = [
                'label' => 'Piglet records',
                'value' => (string) FarmRecord::query()->where('module', 'piglets')->count(),
                'detail' => 'Registered piglets',
            ];
        }

        if ($user->can('farrowing.view')) {
            $upcoming = FarmRecord::query()
                ->where('module', 'breeding')
                ->where('status', 'confirmed')
                ->get(['data'])
                ->filter(fn (FarmRecord $record) => ($date = data_get($record->data, 'expected_date'))
                    && $date >= now()->toDateString()
                    && $date <= now()->addDays(30)->toDateString())
                ->count();
            $summary[] = [
                'label' => 'Upcoming farrowing',
                'value' => (string) $upcoming,
                'detail' => 'Confirmed records in the next 30 days',
            ];
        }

        if ($user->can('inventory.view')) {
            $lowStock = FarmRecord::query()
                ->where('module', 'inventory')
                ->get(['quantity', 'data'])
                ->filter(fn (FarmRecord $item) => (float) data_get($item->data, 'min_quantity', 0) > (float) $item->quantity)
                ->count();
            $summary[] = [
                'label' => 'Low-stock items',
                'value' => (string) $lowStock,
                'detail' => 'Below configured reorder levels',
            ];
        }

        if ($user->can('sales.view')) {
            $summary[] = [
                'label' => 'Sales revenue this month',
                'value' => number_format((float) FarmRecord::query()
                    ->where('module', 'sales')
                    ->where('status', 'completed')
                    ->whereBetween('record_date', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                    ->sum('amount'), 2),
                'detail' => 'Revenue, not profit',
            ];
        }

        return $summary;
    }
}
