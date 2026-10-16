<div class="min-h-screen bg-cream-50">
    <section class="site-container py-8 sm:py-10">
        <p class="eyebrow">{{ __('biik.farmsuite_workspace') }}</p>
        <div class="mt-3 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div><h1 class="text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">{{ $catalog['title'] }}</h1><p class="mt-2 text-forest-800/75">{{ $catalog['description'] }}</p></div>
            @can($module.'.create')<button type="button" wire:click="createRecord" class="button button-primary">{{ __('biik.add_record') }}</button>@endcan
        </div>
        @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
        @error('adjustment') <p role="alert" class="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p> @enderror
        <div class="mt-7 rounded-2xl border border-forest-950/10 bg-white p-5"><label for="record-search" class="form-label">{{ __('biik.search_records') }} {{ strtolower($catalog['title']) }}</label><input id="record-search" type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="{{ __('biik.search_record_placeholder') }}"></div>
        <div class="mt-5 overflow-hidden rounded-2xl border border-forest-950/10 bg-white">
            <div class="overflow-x-auto"><table class="w-full min-w-[60rem] text-left text-sm">
                <thead class="bg-cream-100 text-xs uppercase tracking-wider text-forest-800"><tr><th class="px-5 py-4">{{ __('biik.fields.reference') }}</th><th class="px-5 py-4">{{ __('biik.description') }}</th><th class="px-5 py-4">{{ __('biik.date') }}</th><th class="px-5 py-4">{{ __('biik.fields.quantity') }}</th><th class="px-5 py-4">{{ __('biik.fields.amount') }}</th><th class="px-5 py-4">{{ __('biik.status') }}</th><th class="px-5 py-4">{{ __('biik.actions') }}</th></tr></thead>
                <tbody class="divide-y divide-forest-950/10">
                    @forelse ($records as $record)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-forest-950">{{ $record->reference ?: '—' }}</td>
                            <td class="px-5 py-4">{{ $record->title ?: $record->category ?: __('biik.record_label') }}@if ($record->animal)<span class="block text-xs text-forest-800/65">{{ __('biik.animal_label') }}: {{ $record->animal->tag_number }}</span>@endif</td>
                            <td class="px-5 py-4">{{ $record->record_date?->translatedFormat('M j, Y') ?? '—' }}</td>
                            <td class="px-5 py-4">{{ $record->quantity !== null ? number_format((float) $record->quantity, 3).' '.($record->unit ?? '') : '—' }}</td>
                            <td class="px-5 py-4">{{ $record->amount !== null ? number_format((float) $record->amount, 2) : '—' }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-cream-100 px-2.5 py-1 text-xs font-semibold text-forest-800">{{ __('biik.values.'.$record->status) }}</span></td>
                            <td class="px-5 py-4"><div class="flex flex-wrap gap-3">
                                <button type="button" wire:click="showRecord({{ $record->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.view') }}</button>
                                @can($module.'.update')
                                    @if (! in_array($record->status, ['completed', 'cancelled', 'received', 'archived'], true))
                                        <button type="button" wire:click="editRecord({{ $record->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.edit') }}</button>
                                    @endif
                                @endcan
                                @if ($module === 'sales' && $record->status === 'draft')
                                    @can('sales.update')<button type="button" wire:click="completeSale({{ $record->id }})" wire:confirm="{{ __('biik.confirm_sale') }}" class="font-semibold text-green-800 underline underline-offset-4">{{ __('biik.complete') }}</button>@endcan
                                @endif
                                @if ($module === 'sales' && $record->status === 'completed')
                                    @can('sales.cancel')<button type="button" wire:click="cancelSale({{ $record->id }})" wire:confirm="{{ __('biik.confirm_cancel_sale') }}" class="font-semibold text-pig-500 underline underline-offset-4">{{ __('biik.cancel_sale') }}</button>@endcan
                                @endif
                                @if ($module === 'purchases' && $record->status === 'ordered')
                                    @can('purchases.receive')<button type="button" wire:click="receivePurchase({{ $record->id }})" wire:confirm="{{ __('biik.confirm_receive_purchase') }}" class="font-semibold text-green-800 underline underline-offset-4">{{ __('biik.receive') }}</button>@endcan
                                    @can('purchases.cancel')<button type="button" wire:click="cancelPurchase({{ $record->id }})" wire:confirm="{{ __('biik.confirm_cancel_purchase') }}" class="font-semibold text-pig-500 underline underline-offset-4">{{ __('biik.cancel') }}</button>@endcan
                                @endif
                                @if ($module === 'inventory')
                                    @can('inventory.update')
                                        <label class="sr-only" for="adjustment-{{ $record->id }}">{{ __('biik.stock_adjustment') }}</label><input id="adjustment-{{ $record->id }}" type="number" step="0.001" wire:model="stockAdjustment" class="form-input !w-28 !py-1" placeholder="+/- qty"><button type="button" wire:click="adjustInventory({{ $record->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.adjust') }}</button>
                                    @endcan
                                @endif
                                @if ($module === 'feeding' && $record->category === 'stock')
                                    @can('feeding.update')
                                        <label class="sr-only" for="consume-{{ $record->id }}">{{ __('biik.feed_quantity_used') }}</label><input id="consume-{{ $record->id }}" type="number" min="0.001" step="0.001" wire:model="stockAdjustment" class="form-input !w-28 !py-1" placeholder="{{ __('biik.quantity_used') }}"><button type="button" wire:click="consumeFeed({{ $record->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.record_use') }}</button>
                                    @endcan
                                @endif
                                @can($module.'.delete')
                                    @if (! in_array($record->status, ['completed', 'received', 'cancelled', 'archived'], true))
                                        <button type="button" wire:click="archiveRecord({{ $record->id }})" wire:confirm="{{ __('biik.confirm_archive') }}" class="font-semibold text-pig-500 underline underline-offset-4">{{ __('biik.archive') }}</button>
                                    @endif
                                @endcan
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-forest-800/70">{{ __('biik.no_matching_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="border-t border-forest-950/10 px-5 py-4">{{ $records->links() }}</div>
        </div>

        @if ($selectedRecord)
            <section class="mt-6 rounded-2xl border border-forest-950/10 bg-white p-6" aria-labelledby="record-details-title">
                <div class="flex items-start justify-between gap-4"><div><p class="eyebrow">{{ __('biik.record_details') }}</p><h2 id="record-details-title" class="mt-2 text-xl font-semibold text-forest-950">{{ $selectedRecord->title ?: $selectedRecord->reference ?: $catalog['title'] }}</h2></div><button type="button" wire:click="closePanels" class="button button-outline !px-4 !py-2">{{ __('biik.close') }}</button></div>
                <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div><dt class="form-label">{{ __('biik.fields.reference') }}</dt><dd>{{ $selectedRecord->reference ?: '—' }}</dd></div><div><dt class="form-label">{{ __('biik.date') }}</dt><dd>{{ $selectedRecord->record_date?->translatedFormat('M j, Y') ?? '—' }}</dd></div><div><dt class="form-label">{{ __('biik.status') }}</dt><dd>{{ ucfirst($selectedRecord->status) }}</dd></div>
                    @foreach ($catalog['fields'] as $key => $field)
                        @php($value = in_array($key, ['reference','title','record_date','status','quantity','weight','unit_price','amount','unit','category','related_animal_id','related_record_id','notes'], true) ? $selectedRecord->{$key} : data_get($selectedRecord->data, $key))
                        @if ($value !== null && $value !== '')
                            <div><dt class="form-label">{{ $field['label'] }}</dt><dd>{{ $value }}</dd></div>
                        @endif
                    @endforeach
                    @if ($selectedRecord->animal)<div><dt class="form-label">{{ __('biik.animals') }}</dt><dd>{{ $selectedRecord->animal->tag_number }}</dd></div>@endif
                    @if ($selectedRecord->relatedRecord)<div><dt class="form-label">{{ __('biik.related_record') }}</dt><dd>{{ $selectedRecord->relatedRecord->title ?: $selectedRecord->relatedRecord->reference }}</dd></div>@endif
                    <div><dt class="form-label">{{ __('biik.created_by') }}</dt><dd>{{ $selectedRecord->creator?->name ?? __('biik.unknown') }}</dd></div>
                </dl>
                @if ($selectedRecord->stockMovements->isNotEmpty())
                    <h3 class="mt-6 font-semibold text-forest-950">{{ __('biik.stock_movement_history') }}</h3>
                    <div class="mt-3 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-cream-100"><tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Type</th><th class="px-3 py-2">Quantity</th><th class="px-3 py-2">Balance</th><th class="px-3 py-2">Reference</th></tr></thead><tbody class="divide-y divide-forest-950/10">@foreach ($selectedRecord->stockMovements as $movement)<tr><td class="px-3 py-2">{{ $movement->created_at->format('M j, Y H:i') }}</td><td class="px-3 py-2">{{ str_replace('_', ' ', ucfirst($movement->movement_type)) }}</td><td class="px-3 py-2">{{ number_format((float) $movement->quantity, 3) }}</td><td class="px-3 py-2">{{ number_format((float) $movement->balance_after, 3) }}</td><td class="px-3 py-2">{{ $movement->reference ?: '—' }}</td></tr>@endforeach</tbody></table></div>
                @endif
                @if ($module === 'sales')
                    <h3 class="mt-6 font-semibold text-forest-950">{{ __('biik.payments') }}</h3>
                    <p class="mt-1 text-sm">Paid {{ number_format((float) $selectedRecord->payments->sum('amount'), 2) }} of {{ number_format((float) $selectedRecord->amount, 2) }}</p>
                    @if ($selectedRecord->payments->isNotEmpty())
                        <ul class="mt-2 space-y-1 text-sm">@foreach ($selectedRecord->payments as $payment)<li>{{ $payment->created_at->format('M j, Y') }} · {{ number_format((float) $payment->amount, 2) }} · {{ $payment->method }} {{ $payment->reference ? '· '.$payment->reference : '' }}</li>@endforeach</ul>
                    @endif
                    @can('sales.update')
                        @if ($selectedRecord->status === 'completed' && (float) $selectedRecord->payments->sum('amount') < (float) $selectedRecord->amount)
                            <form wire:submit="recordPayment({{ $selectedRecord->id }})" class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div><label for="payment-amount" class="form-label">Payment amount</label><input id="payment-amount" type="number" min="0.01" step="0.01" wire:model="paymentAmount" class="form-input">@error('paymentAmount')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
                                <div><label for="payment-method" class="form-label">Method</label><input id="payment-method" wire:model="paymentMethod" class="form-input">@error('paymentMethod')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
                                <div><label for="payment-reference" class="form-label">Reference</label><input id="payment-reference" wire:model="paymentReference" class="form-input">@error('paymentReference')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
                                <button class="button button-primary sm:col-span-3">Record payment</button>
                            </form>
                        @endif
                    @endcan
                @endif
                @if (in_array($module, ['customers', 'suppliers'], true) && $relatedTransactions->isNotEmpty())
                    <h3 class="mt-6 font-semibold text-forest-950">{{ $module === 'customers' ? __('biik.sales_history') : __('biik.purchase_history') }}</h3>
                    <ul class="mt-2 space-y-2 text-sm">@foreach ($relatedTransactions as $transaction)<li>{{ $transaction->record_date?->format('M j, Y') }} · {{ $transaction->reference ?: $transaction->title }} · {{ ucfirst($transaction->status) }} · {{ number_format((float) $transaction->amount, 2) }}</li>@endforeach</ul>
                @endif
            </section>
        @endif

        @if ($showForm)
            <section class="mt-6 rounded-2xl border border-forest-950/10 bg-white p-6" aria-labelledby="record-form-title">
                <div class="flex items-start justify-between gap-4"><div><p class="eyebrow">{{ __('biik.record_details') }}</p><h2 id="record-form-title" class="mt-2 text-xl font-semibold text-forest-950">{{ $editingRecordId ? __('biik.edit_record') : __('biik.add_record') }}</h2></div><button type="button" wire:click="closePanels" class="button button-outline !px-4 !py-2">{{ __('biik.close') }}</button></div>
                <form wire:submit="saveRecord" class="mt-5 grid gap-5 sm:grid-cols-2">
                    @foreach ($catalog['fields'] as $key => $field)
                        <div class="{{ $field['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                            <label for="record-{{ $key }}" class="form-label">{{ $field['label'] }}{{ ! empty($field['required']) ? ' *' : '' }}</label>
                            @if ($field['type'] === 'textarea')
                                <textarea id="record-{{ $key }}" wire:model="form.{{ $key }}" rows="3" class="form-input"></textarea>
                            @elseif ($field['type'] === 'select')
                                <select id="record-{{ $key }}" wire:model="form.{{ $key }}" class="form-input"><option value="">{{ __('biik.select') }}</option>@foreach ($field['options'] as $option => $label)<option value="{{ $option }}">{{ $label }}</option>@endforeach</select>
                            @elseif (in_array($field['type'], ['animal', 'animal-reference'], true))
                                <select id="record-{{ $key }}" wire:model="form.{{ $key }}" class="form-input"><option value="">{{ __('biik.select_animal') }}</option>@foreach ($animals as $animal)<option value="{{ $animal->id }}">{{ $animal->tag_number }}{{ $animal->name ? ' · '.$animal->name : '' }}</option>@endforeach</select>
                            @elseif ($field['type'] === 'record')
                                <select id="record-{{ $key }}" wire:model="form.{{ $key }}" class="form-input"><option value="">{{ __('biik.select_related', ['record' => __('biik.modules.'.$field['relation'].'.title')]) }}</option>@foreach (($relatedOptions[$key] ?? []) as $option)<option value="{{ $option->id }}">{{ $option->reference ?: $option->title ?: ('Record '.$option->id) }}</option>@endforeach</select>
                            @else
                                <input id="record-{{ $key }}" type="{{ match ($field['type']) { 'date' => 'date', 'email' => 'email', 'decimal', 'money', 'integer' => 'number', default => 'text' } }}" @if (in_array($field['type'], ['decimal','money'], true)) step="0.001" @endif @if ($field['type'] === 'integer') step="1" @endif @if ($editingRecordId && $key === 'quantity' && in_array($module, ['inventory', 'feeding'], true)) readonly @endif wire:model="form.{{ $key }}" class="form-input">
                            @endif
                            @error('form.'.$key)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    <div class="flex gap-3 sm:col-span-2"><button type="submit" class="button button-primary">{{ __('biik.save_record') }}</button><button type="button" wire:click="closePanels" class="button button-outline">{{ __('biik.cancel') }}</button></div>
                </form>
            </section>
        @endif
    </section>
</div>
