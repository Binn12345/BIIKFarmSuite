<?php

namespace App\Livewire\Farm;

use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\StockMovement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class FarmReportIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $from = '';

    public string $to = '';

    public string $module = '';

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    public function updatedFrom(): void
    {
        $this->validateDateRange();
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->validateDateRange();
        $this->resetPage();
    }

    public function exportCsv()
    {
        $this->authorize('reports.view');
        $this->validateDateRange();

        $records = FarmRecord::query()
            ->whereBetween('record_date', [$this->from, $this->to])
            ->when($this->module !== '', fn ($query) => $query->where('module', $this->module))
            ->orderBy('record_date')
            ->get(['record_date', 'module', 'reference', 'title', 'quantity', 'unit', 'amount', 'status']);

        return response()->streamDownload(function () use ($records): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Date', 'Module', 'Reference', 'Description', 'Quantity', 'Unit', 'Amount', 'Status']);
            foreach ($records as $record) {
                fputcsv($output, array_map(static function ($value): string {
                    $value = (string) ($value ?? '');

                    return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
                }, [
                    $record->record_date?->toDateString(),
                    $record->module,
                    $record->reference,
                    $record->title,
                    $record->quantity,
                    $record->unit,
                    $record->amount,
                    $record->status,
                ]));
            }
            fclose($output);
        }, 'farmsuite-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validateDateRange(): void
    {
        $this->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'module' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Support\FarmRecordCatalog::modules()))],
        ]);
    }

    public function render()
    {
        $this->authorize('reports.view');
        $this->validateDateRange();
        $range = fn ($query) => $query
            ->whereBetween('record_date', [$this->from, $this->to])
            ->whereNull('deleted_at');

        $sales = FarmRecord::query()->where('module', 'sales')->where('status', 'completed')->where($range);
        $purchases = FarmRecord::query()->where('module', 'purchases')->where('status', 'received')->where($range);
        $expenses = FarmRecord::query()->where('module', 'expenses')->where($range);

        $records = FarmRecord::query()
            ->when($this->module !== '', fn ($query) => $query->where('module', $this->module))
            ->whereBetween('record_date', [$this->from, $this->to])
            ->with('animal')
            ->latest('record_date')
            ->paginate(20);
        $lowStock = FarmRecord::query()
            ->where('module', 'inventory')
            ->get(['quantity', 'data'])
            ->filter(fn (FarmRecord $item) => (float) data_get($item->data, 'min_quantity', 0) > (float) $item->quantity)
            ->count();
        $stockMovements = StockMovement::query()
            ->whereBetween('created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
            ->sum('quantity');

        return view('livewire.farm.reports.index', [
            'records' => $records,
            'animalCounts' => Animal::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'pigletCount' => FarmRecord::query()->where('module', 'piglets')->count(),
            'breedingCount' => FarmRecord::query()->where('module', 'breeding')->whereBetween('record_date', [$this->from, $this->to])->count(),
            'farrowingOutcomes' => FarmRecord::query()->where('module', 'farrowing')->whereBetween('record_date', [$this->from, $this->to])->selectRaw('COALESCE(SUM(quantity),0) as total, COALESCE(SUM(JSON_EXTRACT(data, "$.born_alive")),0) as born_alive, COALESCE(SUM(JSON_EXTRACT(data, "$.stillborn")),0) as stillborn')->first(),
            'mortalityCount' => FarmRecord::query()->where('module', 'mortality')->whereBetween('record_date', [$this->from, $this->to])->count(),
            'healthCount' => FarmRecord::query()->where('module', 'health')->whereBetween('record_date', [$this->from, $this->to])->count(),
            'feedConsumed' => FarmRecord::query()->where('module', 'feeding')->where('category', 'consumption')->whereBetween('record_date', [$this->from, $this->to])->sum('quantity'),
            'stockMovements' => $stockMovements,
            'lowStock' => $lowStock,
            'salesRevenue' => $sales->sum('amount'),
            'purchasesTotal' => $purchases->sum('amount'),
            'expensesTotal' => $expenses->sum('amount'),
        ])->layout('layouts.app', ['title' => 'Farm Reports']);
    }
}
