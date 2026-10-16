<?php

namespace App\Livewire\Farm;

use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Services\AuditLogger;
use App\Support\FarmRecordCatalog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class FarmRecordIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Locked]
    public string $module = '';

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingRecordId = null;

    public ?int $selectedRecordId = null;

    public array $form = [];

    public string $stockAdjustment = '';

    public string $paymentAmount = '';

    public string $paymentMethod = '';

    public string $paymentReference = '';

    public function mount(string $module): void
    {
        $this->module = $module;
        $this->resetForm();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createRecord(): void
    {
        $this->authorize($this->module.'.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function editRecord(int $recordId): void
    {
        $this->authorize($this->module.'.update');
        $record = $this->record($recordId);
        abort_if(in_array($record->status, ['completed', 'cancelled', 'received', 'archived'], true), 409, __('biik.record_messages.historical_locked'));

        foreach (FarmRecordCatalog::for($this->module)['fields'] as $key => $field) {
            $this->form[$key] = match ($key) {
                'reference', 'title', 'record_date', 'status', 'quantity', 'weight', 'unit_price', 'amount', 'unit', 'category', 'related_animal_id', 'related_record_id', 'notes' => $record->{$key},
                default => data_get($record->data, $key),
            };

            if ($key === 'record_date') {
                $this->form[$key] = $record->record_date?->format('Y-m-d') ?? '';
            }
            if (in_array($key, ['related_animal_id', 'related_record_id'], true)) {
                $this->form[$key] = $record->{$key} === null ? '' : (string) $record->{$key};
            }
            if ($this->form[$key] === null) {
                $this->form[$key] = '';
            }
        }

        $this->editingRecordId = $record->id;
        $this->selectedRecordId = null;
        $this->showForm = true;
    }

    public function showRecord(int $recordId): void
    {
        $this->authorize($this->module.'.view');
        $this->selectedRecordId = $this->record($recordId)->id;
        $this->showForm = false;
    }

    public function saveRecord(): void
    {
        $this->authorize($this->module.'.'.($this->editingRecordId ? 'update' : 'create'));
        $catalog = FarmRecordCatalog::for($this->module);
        $existing = $this->editingRecordId ? $this->record($this->editingRecordId) : null;
        abort_if($existing && in_array($existing->status, ['completed', 'cancelled', 'received', 'archived'], true), 409, __('biik.record_messages.historical_locked'));

        $rules = [];
        foreach ($catalog['fields'] as $key => $field) {
            $prefix = ! empty($field['required']) ? 'required' : 'nullable';
            $rules['form.'.$key] = match ($field['type']) {
                'date' => [$prefix, 'date', ...($key === 'record_date' ? ['before_or_equal:today'] : [])],
                'decimal', 'money' => [$prefix, 'numeric', 'min:0', 'max:999999999'],
                'integer' => [$prefix, 'integer', 'min:0', 'max:1000000'],
                'email' => [$prefix, 'email', 'max:255'],
                'animal', 'animal-reference' => [$prefix, Rule::exists('animals', 'id')],
                'record' => [$prefix, Rule::exists('farm_records', 'id')],
                'select' => [$prefix, Rule::in(array_keys($field['options'] ?? []))],
                default => [$prefix, 'string', 'max:'.($field['type'] === 'textarea' ? '5000' : '255')],
            };
            if ($key === 'reference') {
                $rules['form.'.$key][] = Rule::unique('farm_records', 'reference')
                    ->where(fn ($query) => $query->where('module', $this->module))
                    ->ignore($existing?->id);
            }
        }
        $validated = $this->validate($rules);
        $values = $validated['form'] ?? [];

        foreach ($catalog['fields'] as $key => $field) {
            if (($field['type'] ?? '') === 'record' && ! empty($values[$key])) {
                $related = FarmRecord::query()->findOrFail($values[$key]);
                if ($related->module !== $field['relation']) {
                    throw ValidationException::withMessages([
                        'form.'.$key => __('biik.record_messages.related_invalid'),
                    ]);
                }
                if (in_array($this->module, ['sales', 'purchases'], true) && $related->status !== 'active') {
                    throw ValidationException::withMessages([
                        'form.'.$key => __('biik.record_messages.party_inactive'),
                    ]);
                }
                if ($field['relation'] === 'inventory' && $related->status !== 'active') {
                    throw ValidationException::withMessages([
                        'form.'.$key => __('biik.record_messages.inventory_inactive'),
                    ]);
                }
            }
            if (($field['type'] ?? '') === 'animal-reference' && ! empty($values[$key])) {
                if (! Animal::query()->whereKey($values[$key])->where('sex', 'male')->where('status', 'active')->exists()) {
                    throw ValidationException::withMessages([
                        'form.'.$key => __('biik.record_messages.boar_invalid'),
                    ]);
                }
            }
            if (($field['type'] ?? '') === 'animal' && in_array($this->module, ['piglets', 'breeding', 'farrowing'], true) && ! empty($values[$key])) {
                if (! Animal::query()->whereKey($values[$key])->where('sex', 'female')->where('status', 'active')->exists()) {
                    throw ValidationException::withMessages([
                        'form.'.$key => __('biik.record_messages.sow_invalid'),
                    ]);
                }
            }
        }
        if ($this->module === 'farrowing'
            && (int) $values['born_alive'] + (int) $values['stillborn'] !== (int) $values['quantity']) {
            throw ValidationException::withMessages([
                'form.quantity' => __('biik.record_messages.farrowing_total'),
            ]);
        }
        if ($this->module === 'sales'
            && empty($values['related_animal_id'])
            && empty($values['inventory_item_id'])) {
            throw ValidationException::withMessages([
                'form.related_animal_id' => __('biik.record_messages.sale_target_required'),
            ]);
        }
        if ($this->module === 'breeding' && ! empty($values['expected_date'])
            && $values['expected_date'] < $values['record_date']) {
            throw ValidationException::withMessages([
                'form.expected_date' => __('biik.record_messages.expected_date_invalid'),
            ]);
        }
        if ($this->module === 'farrowing' && ! empty($values['related_record_id'])) {
            $breeding = FarmRecord::query()->findOrFail($values['related_record_id']);
            if ($breeding->related_animal_id !== (int) ($values['related_animal_id'] ?? 0)) {
                throw ValidationException::withMessages([
                    'form.related_record_id' => __('biik.record_messages.breeding_sow_mismatch'),
                ]);
            }
        }
        if ($this->module === 'piglets' && ! empty($values['related_record_id'])) {
            $farrowing = FarmRecord::query()->findOrFail($values['related_record_id']);
            if ($farrowing->related_animal_id !== (int) ($values['related_animal_id'] ?? 0)) {
                throw ValidationException::withMessages([
                    'form.related_record_id' => __('biik.record_messages.farrowing_sow_mismatch'),
                ]);
            }
        }
        if ($this->module === 'mortality') {
            abort_if($existing, 409, __('biik.record_messages.mortality_locked'));
            $alreadyRecorded = FarmRecord::query()
                ->where('module', 'mortality')
                ->where('related_animal_id', $values['related_animal_id'] ?? null)
                ->whereNull('deleted_at')
                ->exists();
            if ($alreadyRecorded) {
                throw ValidationException::withMessages([
                    'form.related_animal_id' => __('biik.record_messages.mortality_duplicate'),
                ]);
            }
        }
        if ($this->module === 'sales' && ! empty($values['related_animal_id'])) {
            if (! Animal::query()->whereKey($values['related_animal_id'])->where('status', 'active')->exists()) {
                throw ValidationException::withMessages([
                    'form.related_animal_id' => __('biik.record_messages.sale_animal_inactive'),
                ]);
            }
        }

        DB::transaction(function () use ($existing, $values): void {
            $record = $existing ? FarmRecord::query()->lockForUpdate()->findOrFail($existing->id) : new FarmRecord;
            $creating = ! $existing;
            if (! $creating && in_array($this->module, ['inventory', 'feeding'], true) && array_key_exists('quantity', $values)) {
                $values['quantity'] = $record->quantity;
            }
            $data = [];
            foreach ($values as $key => $value) {
                if (! in_array($key, [
                    'reference', 'title', 'record_date', 'status', 'quantity', 'weight', 'unit_price', 'amount', 'unit', 'category', 'related_animal_id', 'related_record_id', 'notes',
                ], true)) {
                    $data[$key] = $value === '' ? null : $value;
                }
            }

            $record->module = $this->module;
            foreach (['reference', 'title', 'record_date', 'quantity', 'weight', 'unit_price', 'unit', 'category', 'notes'] as $column) {
                if (array_key_exists($column, $values)) {
                    $record->{$column} = $values[$column] === '' ? null : $values[$column];
                }
            }
            foreach (['related_animal_id', 'related_record_id'] as $column) {
                if (array_key_exists($column, $values)) {
                    $record->{$column} = $values[$column] === '' ? null : (int) $values[$column];
                }
            }
            if (array_key_exists('status', $values)) {
                $record->status = $values['status'] ?: 'active';
            } elseif ($creating) {
                $record->status = $this->module === 'sales' ? 'draft' : ($this->module === 'purchases' ? 'ordered' : 'active');
            }
            if (isset($values['unit_price'], $values['quantity']) && in_array($this->module, ['sales', 'purchases'], true)) {
                $record->amount = round((float) $values['unit_price'] * (float) $values['quantity'], 2);
            } elseif (isset($values['amount'])) {
                $record->amount = $values['amount'] === '' ? null : $values['amount'];
            }
            $record->data = $data ?: null;
            $record->updated_by = auth()->id();
            if ($creating) {
                $record->created_by = auth()->id();
            }

            if ($this->module === 'mortality') {
                $animal = Animal::query()->lockForUpdate()->findOrFail($record->related_animal_id);
                abort_unless($animal->status === 'active', 409, __('biik.record_messages.mortality_animal_inactive'));
                abort_if(
                    FarmRecord::query()->where('module', 'mortality')->where('related_animal_id', $animal->id)->exists(),
                    409,
                    'A mortality event is already recorded for this animal.',
                );
                $animal->forceFill(['status' => 'deceased', 'updated_by' => auth()->id()])->save();
            }
            $record->save();
            if ($creating && in_array($this->module, ['inventory', 'feeding'], true) && (float) $record->quantity > 0) {
                StockMovement::create([
                    'item_record_id' => $record->id,
                    'movement_type' => 'opening_balance',
                    'quantity' => $record->quantity,
                    'balance_after' => $record->quantity,
                    'reference' => $record->reference,
                    'actor_id' => auth()->id(),
                ]);
            }
            app(AuditLogger::class)->record(
                $creating ? $this->module.'.created' : $this->module.'.updated',
                $record,
                ['reference' => $record->reference, 'status' => $record->status],
            );
        });

        $this->selectedRecordId = null;
        $this->resetForm();
        session()->flash('status', __('biik.record_saved', ['module' => $catalog['title']]));
    }

    public function archiveRecord(int $recordId): void
    {
        $permission = $this->module === 'sales' ? 'sales.cancel' : $this->module.'.delete';
        $this->authorize($permission);
        $record = $this->record($recordId);
        abort_if(in_array($record->status, ['completed', 'received', 'cancelled', 'archived'], true), 409, __('biik.record_messages.record_finalized'));

        DB::transaction(function () use ($record): void {
            $record->forceFill(['status' => 'archived', 'updated_by' => auth()->id()])->save();
            app(AuditLogger::class)->record($this->module.'.archived', $record, ['reference' => $record->reference]);
        });
        session()->flash('status', __('biik.record_archived'));
    }

    public function completeSale(int $recordId): void
    {
        $this->authorize('sales.update');
        DB::transaction(function () use ($recordId): void {
            $sale = $this->record($recordId, true);
            abort_unless($sale->status === 'draft', 409, __('biik.record_messages.sale_draft_only'));
            if ($sale->related_animal_id) {
                $animal = Animal::query()->lockForUpdate()->findOrFail($sale->related_animal_id);
                abort_unless($animal->status === 'active', 409, __('biik.record_messages.animal_unavailable'));
                $previousSale = FarmRecord::query()
                    ->where('module', 'sales')
                    ->where('related_animal_id', $animal->id)
                    ->where('status', 'completed')
                    ->exists();
                abort_if($previousSale, 409, __('biik.record_messages.animal_already_sold'));
                $animal->forceFill(['status' => 'sold', 'updated_by' => auth()->id()])->save();
            }
            $inventoryId = data_get($sale->data, 'inventory_item_id');
            if ($inventoryId) {
                $inventory = FarmRecord::query()->where('module', 'inventory')->lockForUpdate()->findOrFail($inventoryId);
                abort_unless($inventory->status === 'active', 409, __('biik.record_messages.inventory_inactive'));
                abort_if((float) $inventory->quantity < (float) $sale->quantity, 409, __('biik.record_messages.inventory_insufficient'));
                $inventory->quantity = (float) $inventory->quantity - (float) $sale->quantity;
                $inventory->updated_by = auth()->id();
                $inventory->save();
                StockMovement::create([
                    'item_record_id' => $inventory->id,
                    'movement_type' => 'sale',
                    'quantity' => -abs((float) $sale->quantity),
                    'balance_after' => $inventory->quantity,
                    'reference' => $sale->reference,
                    'source_record_id' => $sale->id,
                    'actor_id' => auth()->id(),
                ]);
            }
            $sale->forceFill(['status' => 'completed', 'updated_by' => auth()->id()])->save();
            app(AuditLogger::class)->record('sales.completed', $sale, [
                'reference' => $sale->reference,
                'amount' => $sale->amount,
            ]);
        });
        session()->flash('status', __('biik.record_messages.sale_completed'));
    }

    public function cancelSale(int $recordId): void
    {
        $this->authorize('sales.cancel');
        DB::transaction(function () use ($recordId): void {
            $sale = $this->record($recordId, true);
            abort_unless($sale->status === 'completed', 409, __('biik.record_messages.sale_completed_only'));
            if ((float) $sale->payments()->sum('amount') > 0) {
                throw ValidationException::withMessages([
                    'sale' => __('biik.record_messages.payments_reconcile'),
                ]);
            }
            if ($sale->related_animal_id) {
                $animal = Animal::query()->lockForUpdate()->findOrFail($sale->related_animal_id);
                abort_unless($animal->status === 'sold', 409, __('biik.record_messages.animal_status_changed'));
                $animal->forceFill(['status' => 'active', 'updated_by' => auth()->id()])->save();
            }
            $inventoryId = data_get($sale->data, 'inventory_item_id');
            if ($inventoryId) {
                $inventory = FarmRecord::query()->where('module', 'inventory')->lockForUpdate()->findOrFail($inventoryId);
                $inventory->quantity = (float) $inventory->quantity + (float) $sale->quantity;
                $inventory->updated_by = auth()->id();
                $inventory->save();
                StockMovement::create([
                    'item_record_id' => $inventory->id,
                    'movement_type' => 'sale_cancelled',
                    'quantity' => abs((float) $sale->quantity),
                    'balance_after' => $inventory->quantity,
                    'reference' => $sale->reference,
                    'source_record_id' => $sale->id,
                    'actor_id' => auth()->id(),
                ]);
            }
            $sale->forceFill(['status' => 'cancelled', 'updated_by' => auth()->id()])->save();
            app(AuditLogger::class)->record('sales.cancelled', $sale, ['reference' => $sale->reference]);
        });
        session()->flash('status', __('biik.record_messages.sale_cancelled'));
    }

    public function receivePurchase(int $recordId): void
    {
        $this->authorize('purchases.receive');
        DB::transaction(function () use ($recordId): void {
            $purchase = $this->record($recordId, true);
            abort_unless($purchase->status === 'ordered', 409, __('biik.record_messages.purchase_already_received'));
            $inventoryId = data_get($purchase->data, 'inventory_record_id');
            abort_if(! $inventoryId, 422, __('biik.record_messages.inventory_required'));
            $inventory = FarmRecord::query()->where('module', 'inventory')->lockForUpdate()->findOrFail($inventoryId);
            $inventory->quantity = (float) $inventory->quantity + (float) $purchase->quantity;
            $inventory->updated_by = auth()->id();
            $inventory->save();
            StockMovement::create([
                'item_record_id' => $inventory->id,
                'movement_type' => 'purchase_received',
                'quantity' => abs((float) $purchase->quantity),
                'balance_after' => $inventory->quantity,
                'reference' => $purchase->reference,
                'source_record_id' => $purchase->id,
                'actor_id' => auth()->id(),
            ]);
            $purchase->forceFill(['status' => 'received', 'updated_by' => auth()->id()])->save();
            app(AuditLogger::class)->record('purchases.received', $purchase, [
                'inventory_item' => $inventory->reference,
                'quantity_added' => $purchase->quantity,
            ]);
        });
        session()->flash('status', __('biik.record_messages.purchase_received'));
    }

    public function cancelPurchase(int $recordId): void
    {
        $this->authorize('purchases.cancel');
        $purchase = $this->record($recordId);
        abort_unless($purchase->status === 'ordered', 409, __('biik.record_messages.purchase_ordered_only'));
        $purchase->forceFill(['status' => 'cancelled', 'updated_by' => auth()->id()])->save();
        app(AuditLogger::class)->record('purchases.cancelled', $purchase, ['reference' => $purchase->reference]);
        session()->flash('status', __('biik.record_messages.purchase_cancelled'));
    }

    public function recordPayment(int $recordId): void
    {
        $this->authorize('sales.update');
        $validated = validator([
            'paymentAmount' => $this->paymentAmount,
            'paymentMethod' => $this->paymentMethod,
            'paymentReference' => $this->paymentReference,
        ], [
            'paymentAmount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'paymentMethod' => ['required', 'string', 'max:50'],
            'paymentReference' => ['nullable', 'string', 'max:100'],
        ])->validate();

        $paymentRejected = false;
        DB::transaction(function () use ($recordId, $validated, &$paymentRejected): void {
            $sale = $this->record($recordId, true);
            abort_unless($sale->status === 'completed', 409, __('biik.record_messages.payment_completed_only'));
            $alreadyPaid = (float) $sale->payments()->sum('amount');
            if (round($alreadyPaid + (float) $validated['paymentAmount'], 2) > (float) $sale->amount) {
                $this->addError('paymentAmount', 'Payment exceeds the outstanding sale balance.');
                $paymentRejected = true;

                return;
            }
            $payment = SalePayment::create([
                'sale_id' => $sale->id,
                'amount' => $validated['paymentAmount'],
                'method' => $validated['paymentMethod'],
                'reference' => $validated['paymentReference'] ?: null,
                'recorded_by' => auth()->id(),
            ]);
            app(AuditLogger::class)->record('sales.payment.recorded', $payment, [
                'sale_reference' => $sale->reference,
                'amount' => $payment->amount,
                'method' => $payment->method,
            ]);
        });

        if ($paymentRejected) {
            return;
        }

        $this->reset('paymentAmount', 'paymentMethod', 'paymentReference');
        session()->flash('status', __('biik.record_messages.payment_recorded'));
    }

    public function adjustInventory(int $recordId): void
    {
        $this->authorize('inventory.update');
        $validated = validator(['adjustment' => $this->stockAdjustment], [
            'adjustment' => ['required', 'numeric', 'not_in:0', 'between:-999999999,999999999'],
        ])->validate();
        DB::transaction(function () use ($recordId, $validated): void {
            $item = $this->record($recordId, true);
            $newQuantity = (float) $item->quantity + (float) $validated['adjustment'];
            abort_if($newQuantity < 0, 422, __('biik.record_messages.stock_negative'));
            $item->forceFill(['quantity' => $newQuantity, 'updated_by' => auth()->id()])->save();
            StockMovement::create([
                'item_record_id' => $item->id,
                'movement_type' => 'adjustment',
                'quantity' => (float) $validated['adjustment'],
                'balance_after' => $newQuantity,
                'reference' => $item->reference,
                'actor_id' => auth()->id(),
            ]);
            app(AuditLogger::class)->record('inventory.adjusted', $item, [
                'adjustment' => $validated['adjustment'],
                'quantity_after' => $newQuantity,
            ]);
        });
        $this->reset('stockAdjustment');
        session()->flash('status', __('biik.record_messages.inventory_adjusted'));
    }

    public function consumeFeed(int $recordId): void
    {
        $this->authorize('feeding.update');
        $validated = validator(['adjustment' => $this->stockAdjustment], [
            'adjustment' => ['required', 'numeric', 'gt:0', 'max:999999999'],
        ])->validate();
        DB::transaction(function () use ($recordId, $validated): void {
            $feed = FarmRecord::query()->where('module', 'feeding')->lockForUpdate()->findOrFail($recordId);
            abort_unless($feed->category === 'stock', 409, __('biik.record_messages.feed_stock_required'));
            $newQuantity = (float) $feed->quantity - (float) $validated['adjustment'];
            abort_if($newQuantity < 0, 422, __('biik.record_messages.feed_insufficient'));
            $feed->forceFill(['quantity' => $newQuantity, 'updated_by' => auth()->id()])->save();
            $movement = FarmRecord::create([
                'module' => 'feeding',
                'title' => $feed->title.' consumption',
                'record_date' => now()->toDateString(),
                'status' => 'completed',
                'quantity' => $validated['adjustment'],
                'unit' => $feed->unit,
                'category' => 'consumption',
                'related_record_id' => $feed->id,
                'notes' => 'Feed stock consumption',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            StockMovement::create([
                'item_record_id' => $feed->id,
                'movement_type' => 'feed_consumption',
                'quantity' => -abs((float) $validated['adjustment']),
                'balance_after' => $newQuantity,
                'reference' => $feed->reference,
                'source_record_id' => $movement->id,
                'actor_id' => auth()->id(),
            ]);
            app(AuditLogger::class)->record('feeding.consumed', $feed, [
                'quantity_used' => $validated['adjustment'],
                'quantity_after' => $newQuantity,
            ]);
        });
        $this->reset('stockAdjustment');
        session()->flash('status', __('biik.record_messages.feed_consumption_recorded'));
    }

    public function closePanels(): void
    {
        $this->resetForm();
        $this->selectedRecordId = null;
    }

    private function record(int $recordId, bool $lock = false): FarmRecord
    {
        $query = FarmRecord::query()->where('module', $this->module);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($recordId);
    }

    private function resetForm(): void
    {
        $this->form = [];
        foreach (FarmRecordCatalog::for($this->module)['fields'] as $key => $field) {
            $this->form[$key] = '';
        }
        $this->reset('editingRecordId');
        $this->resetValidation();
        $this->showForm = false;
    }

    public function render()
    {
        $this->authorize($this->module.'.view');
        $catalog = FarmRecordCatalog::for($this->module);

        return view('livewire.farm.records.index', [
            'catalog' => $catalog,
            'records' => FarmRecord::query()
                ->where('module', $this->module)
                ->with(['animal', 'relatedRecord', 'creator'])
                ->when($this->search !== '', function ($query): void {
                    $term = '%'.$this->search.'%';
                    $query->where(function ($query) use ($term): void {
                        $query->where('reference', 'like', $term)
                            ->orWhere('title', 'like', $term)
                            ->orWhere('category', 'like', $term)
                            ->orWhere('notes', 'like', $term);
                    });
                })
                ->latest('record_date')
                ->latest('id')
                ->paginate(15),
            'selectedRecord' => $this->selectedRecordId
                ? $this->record($this->selectedRecordId)->load(['stockMovements.sourceRecord', 'payments', 'relatedRecord'])
                : null,
            'relatedTransactions' => $this->selectedRecordId
                ? FarmRecord::query()
                    ->whereIn('module', $this->module === 'customers' ? ['sales'] : ['purchases'])
                    ->where('related_record_id', $this->selectedRecordId)
                    ->latest('record_date')
                    ->get()
                : collect(),
            'animals' => Animal::query()
                ->whereIn('status', $this->module === 'sales' ? ['active'] : ['active', 'sold'])
                ->orderBy('tag_number')
                ->get(),
            'relatedOptions' => $this->relatedOptions($catalog['fields']),
        ])->layout('layouts.app', ['title' => $catalog['title']]);
    }

    private function relatedOptions(array $fields): array
    {
        $options = [];
        foreach ($fields as $key => $field) {
            if (($field['type'] ?? '') === 'record') {
                $query = FarmRecord::query()->where('module', $field['relation']);
                if (in_array($field['relation'], ['customers', 'suppliers', 'inventory'], true)) {
                    $query->where('status', 'active');
                }
                $options[$key] = $query->orderBy('title')->orderBy('reference')->get();
            }
        }

        if (isset($fields['inventory_record_id'])) {
            $options['inventory_record_id'] = FarmRecord::query()
                ->where('module', 'inventory')
                ->where('status', 'active')
                ->orderBy('title')
                ->get();
        }
        foreach ($fields as $key => $field) {
            if (($field['type'] ?? '') === 'record' && ($field['relation'] ?? null) === 'inventory') {
                $options[$key] = FarmRecord::query()->where('module', 'inventory')->orderBy('title')->get();
            }
        }

        return $options;
    }
}
