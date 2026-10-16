<div class="min-h-screen bg-cream-50">
    <header class="border-b border-forest-950/10 bg-white">
        <div class="site-container flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">Animal Management</span></span></a>
            <a href="{{ route('dashboard') }}" class="button button-outline !px-5 !py-2.5">Dashboard</a>
        </div>
    </header>
    <section class="site-container py-12 sm:py-16">
        <p class="eyebrow">Farm management</p>
        <div class="mt-3 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Animal Management</h1>
                <p class="mt-2 text-forest-800/75">Register animals, track identification, and manage pen assignments.</p>
            </div>
            @can('animals.create')
                <button type="button" wire:click="createAnimal" class="button button-primary">Register animal</button>
            @endcan
        </div>

        @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
        <div class="mt-7 rounded-2xl border border-forest-950/10 bg-white p-5">
            <label for="animal-search" class="form-label">Search animals</label>
            <input id="animal-search" type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Tag number, name, or breed">
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-forest-950/10 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[56rem] text-left text-sm">
                    <thead class="bg-cream-100 text-xs uppercase tracking-wider text-forest-800"><tr><th class="px-5 py-4">Tag number</th><th class="px-5 py-4">Name / breed</th><th class="px-5 py-4">Sex</th><th class="px-5 py-4">Birth date</th><th class="px-5 py-4">Pen</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Actions</th></tr></thead>
                    <tbody class="divide-y divide-forest-950/10">
                        @forelse ($animals as $animal)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-forest-950">{{ $animal->tag_number }}</td>
                                <td class="px-5 py-4">{{ $animal->name ?: '—' }}<span class="block text-xs text-forest-800/65">{{ $animal->breed ?: 'Breed not specified' }}</span></td>
                                <td class="px-5 py-4 capitalize">{{ $animal->sex }}</td>
                                <td class="px-5 py-4">{{ $animal->birth_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-5 py-4">{{ $animal->pen?->name ?? 'Unassigned' }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $animal->status === 'active' ? 'bg-green-50 text-green-800' : 'bg-cream-100 text-forest-800' }}">{{ ucfirst($animal->status) }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-3">
                                        <button type="button" wire:click="showAnimal({{ $animal->id }})" class="font-semibold text-forest-800 underline underline-offset-4">View</button>
                                        @can('animals.update')
                                            @if (! in_array($animal->status, ['archived', 'sold', 'deceased'], true))
                                                <button type="button" wire:click="editAnimal({{ $animal->id }})" class="font-semibold text-forest-800 underline underline-offset-4">Edit</button>
                                            @endif
                                        @endcan
                                        @can('animals.delete')
                                            @if ($animal->status === 'active')
                                                <button type="button" wire:click="archiveAnimal({{ $animal->id }})" wire:confirm="Archive this animal? The record will be retained." class="font-semibold text-pig-500 underline underline-offset-4">Archive</button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-forest-800/70">No animal records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-forest-950/10 px-5 py-4">{{ $animals->links() }}</div>
        </div>

        @if ($selectedAnimal)
            <section class="mt-6 rounded-2xl border border-forest-950/10 bg-white p-6" aria-labelledby="animal-detail-heading">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="eyebrow">Animal record</p><h2 id="animal-detail-heading" class="mt-2 text-xl font-semibold text-forest-950">{{ $selectedAnimal->tag_number }}{{ $selectedAnimal->name ? ' · '.$selectedAnimal->name : '' }}</h2></div>
                    <button type="button" wire:click="closePanels" class="button button-outline !px-4 !py-2">Close</button>
                </div>
                <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div><dt class="form-label">Breed</dt><dd>{{ $selectedAnimal->breed ?: 'Not specified' }}</dd></div>
                    <div><dt class="form-label">Category</dt><dd>{{ $selectedAnimal->category ?: 'Not specified' }}</dd></div>
                    <div><dt class="form-label">Sex</dt><dd class="capitalize">{{ $selectedAnimal->sex }}</dd></div>
                    <div><dt class="form-label">Date of birth</dt><dd>{{ $selectedAnimal->birth_date?->format('M j, Y') ?? 'Not recorded' }}</dd></div>
                    <div><dt class="form-label">Acquisition source</dt><dd>{{ $selectedAnimal->acquisition_source ?: 'Not recorded' }}</dd></div>
                    <div><dt class="form-label">Acquisition date</dt><dd>{{ $selectedAnimal->acquired_at?->format('M j, Y') ?? 'Not recorded' }}</dd></div>
                    <div><dt class="form-label">Purchase cost</dt><dd>{{ $selectedAnimal->purchase_cost !== null ? number_format((float) $selectedAnimal->purchase_cost, 2) : 'Not recorded' }}</dd></div>
                    <div><dt class="form-label">Pen</dt><dd>{{ $selectedAnimal->pen?->name ?? 'Unassigned' }}</dd></div>
                    <div><dt class="form-label">Status</dt><dd class="capitalize">{{ $selectedAnimal->status }}</dd></div>
                    <div><dt class="form-label">Created by</dt><dd>{{ $selectedAnimal->creator?->name ?? 'Unknown' }}</dd></div>
                    <div class="sm:col-span-2 lg:col-span-3"><dt class="form-label">Notes</dt><dd class="whitespace-pre-line">{{ $selectedAnimal->notes ?: 'No notes' }}</dd></div>
                </dl>
            </section>
        @endif

        @if ($showForm)
            <section class="mt-6 rounded-2xl border border-forest-950/10 bg-white p-6" aria-labelledby="animal-form-heading">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="eyebrow">Animal record</p><h2 id="animal-form-heading" class="mt-2 text-xl font-semibold text-forest-950">{{ $editingAnimalId ? 'Edit animal' : 'Register animal' }}</h2></div>
                    <button type="button" wire:click="closePanels" class="button button-outline !px-4 !py-2">Close</button>
                </div>
                <form wire:submit="saveAnimal" class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><label for="animal-tag" class="form-label">Tag number *</label><input id="animal-tag" wire:model="tag_number" class="form-input" required>@error('tag_number') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-name" class="form-label">Name</label><input id="animal-name" wire:model="name" class="form-input">@error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-breed" class="form-label">Breed</label><input id="animal-breed" wire:model="breed" class="form-input">@error('breed') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-category" class="form-label">Category</label><input id="animal-category" wire:model="category" class="form-input">@error('category') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-sex" class="form-label">Sex *</label><select id="animal-sex" wire:model="sex" class="form-input" required><option value="">Select sex</option><option value="female">Female</option><option value="male">Male</option></select>@error('sex') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-birth-date" class="form-label">Date of birth</label><input id="animal-birth-date" type="date" wire:model="birth_date" max="{{ now()->toDateString() }}" class="form-input">@error('birth_date') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-acquisition-source" class="form-label">Acquisition source</label><input id="animal-acquisition-source" wire:model="acquisition_source" class="form-input">@error('acquisition_source') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-acquired-at" class="form-label">Acquisition date</label><input id="animal-acquired-at" type="date" wire:model="acquired_at" max="{{ now()->toDateString() }}" class="form-input">@error('acquired_at') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-purchase-cost" class="form-label">Purchase cost</label><input id="animal-purchase-cost" type="number" min="0" step="0.01" wire:model="purchase_cost" class="form-input">@error('purchase_cost') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-status" class="form-label">Status</label><select id="animal-status" wire:model="status" class="form-input"><option value="active">Active</option><option value="inactive">Inactive</option></select>@error('status') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-pen" class="form-label">Pen</label><select id="animal-pen" wire:model="pen_id" class="form-input"><option value="">Unassigned</option>@foreach ($pens as $pen)<option value="{{ $pen->id }}">{{ $pen->name }} ({{ $pen->active_animals_count }}/{{ $pen->capacity }})</option>@endforeach</select>@error('pen_id') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div class="sm:col-span-2"><label for="animal-notes" class="form-label">Notes</label><textarea id="animal-notes" wire:model="notes" rows="3" class="form-input"></textarea>@error('notes') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div class="flex gap-3 sm:col-span-2"><button type="submit" class="button button-primary">Save animal</button><button type="button" wire:click="closePanels" class="button button-outline">Cancel</button></div>
                </form>
            </section>
        @endif
    </section>
</div>
