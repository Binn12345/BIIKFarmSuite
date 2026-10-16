<div class="min-h-screen bg-cream-50">
    <header class="border-b border-forest-950/10 bg-white">
        <div class="site-container flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">Pens Management</span></span></a>
            <a href="{{ route('dashboard') }}" class="button button-outline !px-5 !py-2.5">Dashboard</a>
        </div>
    </header>
    <section class="site-container py-12 sm:py-16">
        <p class="eyebrow">Farm management</p>
        <div class="mt-3 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Pens Management</h1>
                <p class="mt-2 text-forest-800/75">Maintain pen capacity and location, and see active assigned animal counts.</p>
            </div>
            @can('pens.create')
                <button type="button" wire:click="createPen" class="button button-primary">Add pen</button>
            @endcan
        </div>

        @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
        @error('pen') <p role="alert" class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p> @enderror
        <div class="mt-7 rounded-2xl border border-forest-950/10 bg-white p-5">
            <label for="pen-search" class="form-label">Search pens</label>
            <input id="pen-search" type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Pen name or location">
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-forest-950/10 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[48rem] text-left text-sm">
                    <thead class="bg-cream-100 text-xs uppercase tracking-wider text-forest-800"><tr><th class="px-5 py-4">Pen</th><th class="px-5 py-4">Location</th><th class="px-5 py-4">Occupancy</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Actions</th></tr></thead>
                    <tbody class="divide-y divide-forest-950/10">
                        @forelse ($pens as $pen)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-forest-950">{{ $pen->name }}</td>
                                <td class="px-5 py-4">{{ $pen->location ?: '—' }}</td>
                                <td class="px-5 py-4">{{ $pen->active_animals_count }} / {{ $pen->capacity }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $pen->status === 'active' ? 'bg-green-50 text-green-800' : 'bg-cream-100 text-forest-800' }}">{{ ucfirst($pen->status) }}</span></td>
                                <td class="px-5 py-4"><div class="flex flex-wrap gap-3">
                                    <button type="button" wire:click="showPen({{ $pen->id }})" class="font-semibold text-forest-800 underline underline-offset-4">View</button>
                                    @can('pens.update')<button type="button" wire:click="editPen({{ $pen->id }})" class="font-semibold text-forest-800 underline underline-offset-4">Edit</button>@endcan
                                    @can('pens.delete')<button type="button" wire:click="deletePen({{ $pen->id }})" wire:confirm="Delete this pen? Pens with animal records cannot be deleted." class="font-semibold text-pig-500 underline underline-offset-4">Delete</button>@endcan
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-forest-800/70">No pens have been registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-forest-950/10 px-5 py-4">{{ $pens->links() }}</div>
        </div>

        @if ($selectedPen)
            <section class="mt-6 rounded-2xl border border-forest-950/10 bg-white p-6" aria-labelledby="pen-detail-heading">
                <div class="flex items-start justify-between gap-4"><div><p class="eyebrow">Pen record</p><h2 id="pen-detail-heading" class="mt-2 text-xl font-semibold text-forest-950">{{ $selectedPen->name }}</h2></div><button type="button" wire:click="closePanels" class="button button-outline !px-4 !py-2">Close</button></div>
                <dl class="mt-5 grid gap-4 sm:grid-cols-3"><div><dt class="form-label">Location</dt><dd>{{ $selectedPen->location ?: 'Not specified' }}</dd></div><div><dt class="form-label">Capacity</dt><dd>{{ $selectedPen->animals->where('status', 'active')->count() }} / {{ $selectedPen->capacity }}</dd></div><div><dt class="form-label">Status</dt><dd class="capitalize">{{ $selectedPen->status }}</dd></div><div class="sm:col-span-3"><dt class="form-label">Assigned animals</dt><dd>{{ $selectedPen->animals->pluck('tag_number')->implode(', ') ?: 'None' }}</dd></div><div class="sm:col-span-3"><dt class="form-label">Notes</dt><dd class="whitespace-pre-line">{{ $selectedPen->notes ?: 'No notes' }}</dd></div></dl>
            </section>
        @endif

        @if ($showForm)
            <section class="mt-6 rounded-2xl border border-forest-950/10 bg-white p-6" aria-labelledby="pen-form-heading">
                <div class="flex items-start justify-between gap-4"><div><p class="eyebrow">Pen record</p><h2 id="pen-form-heading" class="mt-2 text-xl font-semibold text-forest-950">{{ $editingPenId ? 'Edit pen' : 'Add pen' }}</h2></div><button type="button" wire:click="closePanels" class="button button-outline !px-4 !py-2">Close</button></div>
                <form wire:submit="savePen" class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><label for="pen-name" class="form-label">Pen name *</label><input id="pen-name" wire:model="name" class="form-input" required>@error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="pen-capacity" class="form-label">Capacity (animals) *</label><input id="pen-capacity" type="number" min="1" wire:model="capacity" class="form-input" required>@error('capacity') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="pen-location" class="form-label">Location</label><input id="pen-location" wire:model="location" class="form-input">@error('location') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="pen-status" class="form-label">Status *</label><select id="pen-status" wire:model="status" class="form-input" required><option value="active">Active</option><option value="maintenance">Maintenance</option></select>@error('status') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div class="sm:col-span-2"><label for="pen-notes" class="form-label">Notes</label><textarea id="pen-notes" wire:model="notes" rows="3" class="form-input"></textarea>@error('notes') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div class="flex gap-3 sm:col-span-2"><button type="submit" class="button button-primary">Save pen</button><button type="button" wire:click="closePanels" class="button button-outline">Cancel</button></div>
                </form>
            </section>
        @endif
    </section>
</div>
