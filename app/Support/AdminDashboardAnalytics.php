<?php

namespace App\Support;

use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardAnalytics
{
    /**
     * @return array{period: int, metrics: list<array{key: string, value: string, detail: string, route: ?string}>, sales: list<array{label: string, value: float}>, animalCategories: list<array{label: string, value: int}>, recent: Collection}
     */
    public static function for(User $user, int $period = 6): array
    {
        $period = in_array($period, [3, 6, 12], true) ? $period : 6;
        $start = now()->startOfMonth()->subMonths($period - 1);
        $end = now()->toDateString();
        $range = [$start->toDateString(), $end];
        $can = fn (string $permission): bool => $user->can($permission);
        $metrics = [];

        if ($can('animals.view')) {
            $metrics[] = ['key' => 'total_animals', 'value' => (string) Animal::query()->count(), 'detail' => __('biik.analytics.all_statuses'), 'route' => 'modules.animals'];
            $metrics[] = ['key' => 'active_animals', 'value' => (string) Animal::query()->where('status', 'active')->count(), 'detail' => __('biik.analytics.current_stock'), 'route' => 'modules.animals'];
            $metrics[] = ['key' => 'animals_in_pens', 'value' => (string) Animal::query()->whereNotNull('pen_id')->where('status', 'active')->count(), 'detail' => __('biik.analytics.active_assignments'), 'route' => 'modules.pens'];
        }
        if ($can('piglets.view')) {
            $metrics[] = ['key' => 'registered_piglets', 'value' => (string) FarmRecord::query()->where('module', 'piglets')->count(), 'detail' => __('biik.analytics.all_records'), 'route' => 'modules.piglets'];
        }
        if ($can('customers.view')) {
            $metrics[] = ['key' => 'customers', 'value' => (string) FarmRecord::query()->where('module', 'customers')->where('status', 'active')->count(), 'detail' => __('biik.analytics.active_accounts'), 'route' => 'modules.customers'];
        }
        if ($can('inventory.view')) {
            $reorderExpression = DB::connection()->getDriverName() === 'sqlite'
                ? "CAST(COALESCE(json_extract(data, '$.min_quantity'), json_extract(data, '$.reorder_level'), 0) AS REAL)"
                : "CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.min_quantity')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.reorder_level')), 0) AS DECIMAL(14,3))";
            $lowStock = FarmRecord::query()->where('module', 'inventory')
                ->whereRaw("$reorderExpression > quantity")
                ->count();
            $metrics[] = ['key' => 'low_stock', 'value' => (string) $lowStock, 'detail' => __('biik.analytics.below_reorder'), 'route' => 'modules.inventory'];
        }
        if ($can('sales.view')) {
            $metrics[] = ['key' => 'sales_revenue', 'value' => number_format((float) FarmRecord::query()->where('module', 'sales')->where('status', 'completed')->whereBetween('record_date', $range)->sum('amount'), 2), 'detail' => __('biik.analytics.revenue_not_profit'), 'route' => 'modules.sales'];
        }
        if ($can('purchases.view')) {
            $metrics[] = ['key' => 'purchase_total', 'value' => number_format((float) FarmRecord::query()->where('module', 'purchases')->where('status', 'received')->whereBetween('record_date', $range)->sum('amount'), 2), 'detail' => __('biik.analytics.received_purchases'), 'route' => 'modules.purchases'];
        }
        if ($can('expenses.view')) {
            $metrics[] = ['key' => 'expense_total', 'value' => number_format((float) FarmRecord::query()->where('module', 'expenses')->whereNotIn('status', ['cancelled', 'archived'])->whereBetween('record_date', $range)->sum('amount'), 2), 'detail' => __('biik.analytics.recorded_expenses'), 'route' => 'modules.expenses'];
        }
        if ($can('mortality.view')) {
            $metrics[] = ['key' => 'recent_mortality', 'value' => (string) FarmRecord::query()->where('module', 'mortality')->whereBetween('record_date', $range)->count(), 'detail' => __('biik.analytics.period_mortality'), 'route' => 'modules.mortality'];
        }

        $monthlySales = collect();
        if ($can('sales.view')) {
            $groupExpression = DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('%Y-%m', record_date)"
                : "DATE_FORMAT(record_date, '%Y-%m')";
            $monthlySales = FarmRecord::query()->where('module', 'sales')->where('status', 'completed')->whereBetween('record_date', $range)
                ->selectRaw("$groupExpression as period, SUM(amount) as total")
                ->groupBy('period')
                ->pluck('total', 'period');
        }
        $sales = collect(range(0, $period - 1))->map(function (int $offset) use ($start, $monthlySales): array {
            $month = $start->copy()->addMonths($offset);

            return ['label' => $month->translatedFormat('M'), 'value' => (float) $monthlySales->get($month->format('Y-m'), 0)];
        })->all();

        $animalCategories = $can('animals.view')
            ? Animal::query()->select('category', DB::raw('COUNT(*) as total'))
                ->groupBy('category')->orderByDesc('total')->limit(6)->get()
                ->map(fn ($row) => ['label' => $row->category ?: __('biik.analytics.uncategorized'), 'value' => (int) $row->total])->all()
            : [];

        $allowedModules = collect(FarmRecordCatalog::modules())
            ->keys()
            ->filter(fn (string $module) => $can($module.'.view'))
            ->values();
        $recent = $allowedModules->isEmpty()
            ? collect()
            : FarmRecord::query()->whereIn('module', $allowedModules)->whereNotNull('record_date')
                ->latest('record_date')->limit(6)->get(['id', 'module', 'title', 'reference', 'record_date', 'status']);

        return compact('period', 'metrics', 'sales', 'animalCategories', 'recent');
    }
}
