<?php

namespace App\Support;

use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
                'label' => __('biik.dashboard_summaries.active_animals'),
                'value' => (string) Animal::query()->where('status', 'active')->count(),
                'detail' => __('biik.dashboard_summaries.current_inventory'),
            ];
        }

        if ($user->can('piglets.view')) {
            $summary[] = [
                'label' => __('biik.dashboard_summaries.piglets'),
                'value' => (string) FarmRecord::query()->where('module', 'piglets')->count(),
                'detail' => __('biik.dashboard_summaries.registered_piglets'),
            ];
        }

        if ($user->can('farrowing.view')) {
            $expectedDateExpression = DB::connection()->getDriverName() === 'sqlite'
                ? "json_extract(data, '$.expected_date')"
                : "JSON_UNQUOTE(JSON_EXTRACT(data, '$.expected_date'))";
            $upcoming = FarmRecord::query()
                ->where('module', 'breeding')
                ->where('status', 'confirmed')
                ->whereRaw("$expectedDateExpression BETWEEN ? AND ?", [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->count();
            $summary[] = [
                'label' => __('biik.dashboard_summaries.upcoming_farrowing'),
                'value' => (string) $upcoming,
                'detail' => __('biik.dashboard_summaries.next_30_days'),
            ];
        }

        if ($user->can('inventory.view')) {
            $reorderExpression = DB::connection()->getDriverName() === 'sqlite'
                ? "CAST(COALESCE(json_extract(data, '$.min_quantity'), json_extract(data, '$.reorder_level'), 0) AS REAL)"
                : "CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.min_quantity')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.reorder_level')), 0) AS DECIMAL(14,3))";
            $lowStock = FarmRecord::query()->where('module', 'inventory')
                ->whereRaw("$reorderExpression > quantity")
                ->count();
            $summary[] = [
                'label' => __('biik.dashboard_summaries.low_stock'),
                'value' => (string) $lowStock,
                'detail' => __('biik.analytics.below_reorder'),
            ];
        }

        if ($user->can('sales.view')) {
            $summary[] = [
                'label' => __('biik.dashboard_summaries.sales_revenue'),
                'value' => number_format((float) FarmRecord::query()
                    ->where('module', 'sales')
                    ->where('status', 'completed')
                    ->whereBetween('record_date', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                    ->sum('amount'), 2),
                'detail' => __('biik.analytics.revenue_not_profit'),
            ];
        }

        return $summary;
    }
}
