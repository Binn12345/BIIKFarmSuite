<div class="min-h-screen bg-cream-50">
    <header class="border-b border-forest-950/10 bg-white">
        <div class="site-container flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">{{ __('biik.modules.animals.title') }}</span></span></a>
            <a href="{{ route('dashboard') }}" class="button button-outline !px-5 !py-2.5">{{ __('biik.dashboard') }}</a>
        </div>
    </header>
    <section class="site-container py-12 sm:py-16">
        <p class="eyebrow">{{ __('biik.animal_page.farm_management') }}</p>
        <div class="mt-3 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">{{ __('biik.modules.animals.title') }}</h1>
                <p class="mt-2 text-forest-800/75">{{ __('biik.animal_page.register_description') }}</p>
            </div>
            @can('animals.create')
                <button type="button" wire:click="createAnimal" class="button button-primary">{{ __('biik.animal_page.register') }}</button>
            @endcan
        </div>

        @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
        <div class="mt-7 rounded-2xl border border-forest-950/10 bg-white p-5">
            <label for="animal-search" class="form-label">{{ __('biik.animal_page.search') }}</label>
            <input id="animal-search" type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="{{ __('biik.animal_page.search_hint') }}">
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-forest-950/10 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[56rem] text-left text-sm">
                    <thead class="bg-cream-100 text-xs uppercase tracking-wider text-forest-800"><tr><th class="px-5 py-4">{{ __('biik.animal_page.tag_number') }}</th><th class="px-5 py-4">{{ __('biik.animal_page.name_breed') }}</th><th class="px-5 py-4">{{ __('biik.animal_page.sex') }}</th><th class="px-5 py-4">{{ __('biik.animal_page.birth_date') }}</th><th class="px-5 py-4">{{ __('biik.animal_page.pen') }}</th><th class="px-5 py-4">{{ __('biik.animal_page.status') }}</th><th class="px-5 py-4">{{ __('biik.animal_page.actions') }}</th></tr></thead>
                    <tbody class="divide-y divide-forest-950/10">
                        @forelse ($animals as $animal)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-forest-950">{{ $animal->tag_number }}</td>
                                <td class="px-5 py-4">{{ $animal->name ?: '—' }}<span class="block text-xs text-forest-800/65">{{ $animal->breed ?: __('biik.animal_page.breed_unspecified') }}</span></td>
                                <td class="px-5 py-4">{{ __('biik.animal_sexes.'.$animal->sex) }}</td>
                                <td class="px-5 py-4">{{ $animal->birth_date?->translatedFormat('M j, Y') ?? '—' }}</td>
                                <td class="px-5 py-4">{{ $animal->pen?->name ?? __('biik.animal_page.unassigned') }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $animal->status === 'active' ? 'bg-green-50 text-green-800' : 'bg-cream-100 text-forest-800' }}">{{ __('biik.values.'.$animal->status) }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-3">
                                        <button type="button" wire:click="showAnimal({{ $animal->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.animal_page.view') }}</button>
                                        @can('animals.update')
                                            @if (! in_array($animal->status, ['archived', 'sold', 'deceased'], true))
                                                <button type="button" wire:click="editAnimal({{ $animal->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.animal_page.edit') }}</button>
                                            @endif
                                        @endcan
                                        @can('animals.delete')
                                            @if ($animal->status === 'active')
                                                <button type="button" wire:click="archiveAnimal({{ $animal->id }})" wire:confirm="{{ __('biik.animal_page.archive_confirm') }}" class="font-semibold text-pig-500 underline underline-offset-4">{{ __('biik.animal_page.archive') }}</button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-forest-800/70">{{ __('biik.animal_page.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-forest-950/10 px-5 py-4">{{ $animals->links() }}</div>
        </div>

        @if ($selectedAnimal)
            <section class="mt-6 rounded-2xl border border-forest-950/10 bg-white p-6" aria-labelledby="animal-detail-heading">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="eyebrow">{{ __('biik.animal_page.record') }}</p><h2 id="animal-detail-heading" class="mt-2 text-xl font-semibold text-forest-950">{{ $selectedAnimal->tag_number }}{{ $selectedAnimal->name ? ' · '.$selectedAnimal->name : '' }}</h2></div>
                    <button type="button" wire:click="closePanels" class="button button-outline !px-4 !py-2">{{ __('biik.animal_page.close') }}</button>
                </div>
                <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div><dt class="form-label">{{ __('biik.animal_page.breed_label') }}</dt><dd>{{ $selectedAnimal->breed ?: __('biik.animal_page.not_specified') }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.category') }}</dt><dd>{{ $selectedAnimal->category ?: __('biik.animal_page.not_specified') }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.sex') }}</dt><dd>{{ __('biik.animal_sexes.'.$selectedAnimal->sex) }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.date_of_birth') }}</dt><dd>{{ $selectedAnimal->birth_date?->translatedFormat('M j, Y') ?? __('biik.animal_page.date_not_recorded') }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.acquisition_source') }}</dt><dd>{{ $selectedAnimal->acquisition_source ?: __('biik.animal_page.date_not_recorded') }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.acquisition_date') }}</dt><dd>{{ $selectedAnimal->acquired_at?->translatedFormat('M j, Y') ?? __('biik.animal_page.date_not_recorded') }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.purchase_cost') }}</dt><dd>{{ $selectedAnimal->purchase_cost !== null ? number_format((float) $selectedAnimal->purchase_cost, 2) : __('biik.animal_page.date_not_recorded') }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.pen') }}</dt><dd>{{ $selectedAnimal->pen?->name ?? __('biik.animal_page.unassigned') }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.status') }}</dt><dd>{{ __('biik.values.'.$selectedAnimal->status) }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.animal_page.created_by') }}</dt><dd>{{ $selectedAnimal->creator?->name ?? __('biik.animal_page.unknown') }}</dd></div>
                    <div class="sm:col-span-2 lg:col-span-3"><dt class="form-label">{{ __('biik.animal_page.notes') }}</dt><dd class="whitespace-pre-line">{{ $selectedAnimal->notes ?: __('biik.animal_page.date_not_recorded') }}</dd></div>
                </dl>
            </section>
        @endif

        @if ($showForm)
            <section class="mt-6 rounded-2xl border border-forest-950/10 bg-white p-6" aria-labelledby="animal-form-heading">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="eyebrow">{{ __('biik.animal_page.record') }}</p><h2 id="animal-form-heading" class="mt-2 text-xl font-semibold text-forest-950">{{ $editingAnimalId ? __('biik.animal_page.edit_animal') : __('biik.animal_page.register') }}</h2></div>
                    <button type="button" wire:click="closePanels" class="button button-outline !px-4 !py-2">{{ __('biik.animal_page.close') }}</button>
                </div>
                <form wire:submit="saveAnimal" class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><label for="animal-tag" class="form-label">{{ __('biik.animal_page.tag_required') }}</label><input id="animal-tag" wire:model="tag_number" class="form-input" required>@error('tag_number') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-name" class="form-label">{{ __('biik.animal_page.name_label') }}</label><input id="animal-name" wire:model="name" class="form-input">@error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-breed" class="form-label">{{ __('biik.animal_page.breed_label') }}</label><select id="animal-breed" wire:model="breed" class="form-input"><option value="">{{ __('biik.animal_page.select_breed') }}</option>@if ($breed && ! $breeds->contains($breed))<option value="{{ $breed }}">{{ $breed }} ({{ __('biik.animal_page.existing') }})</option>@endif @foreach ($breeds as $breedOption)<option value="{{ $breedOption }}">{{ $breedOption }}</option>@endforeach</select>@error('breed') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-category" class="form-label">{{ __('biik.animal_page.category') }}</label><select id="animal-category" wire:model="category" class="form-input"><option value="">{{ __('biik.animal_page.select_category') }}</option>@if ($category && ! $categories->contains($category))<option value="{{ $category }}">{{ $category }} ({{ __('biik.animal_page.existing') }})</option>@endif @foreach ($categories as $categoryOption)<option value="{{ $categoryOption }}">{{ $categoryOption }}</option>@endforeach</select>@error('category') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-sex" class="form-label">{{ __('biik.animal_page.sex_required') }}</label><select id="animal-sex" wire:model="sex" class="form-input" required><option value="">{{ __('biik.animal_page.select_sex') }}</option><option value="female">{{ __('biik.animal_page.female') }}</option><option value="male">{{ __('biik.animal_page.male') }}</option></select>@error('sex') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-birth-date" class="form-label">{{ __('biik.animal_page.date_of_birth') }}</label><input id="animal-birth-date" type="date" wire:model="birth_date" max="{{ now()->toDateString() }}" class="form-input">@error('birth_date') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-acquisition-source" class="form-label">{{ __('biik.animal_page.acquisition_source') }}</label><input id="animal-acquisition-source" wire:model="acquisition_source" class="form-input">@error('acquisition_source') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-acquired-at" class="form-label">{{ __('biik.animal_page.acquired_date') }}</label><input id="animal-acquired-at" type="date" wire:model="acquired_at" max="{{ now()->toDateString() }}" class="form-input">@error('acquired_at') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-purchase-cost" class="form-label">{{ __('biik.animal_page.purchase_cost') }}</label><input id="animal-purchase-cost" type="number" min="0" step="0.01" wire:model="purchase_cost" class="form-input">@error('purchase_cost') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-status" class="form-label">{{ __('biik.animal_page.status_label') }}</label><select id="animal-status" wire:model="status" class="form-input"><option value="active">{{ __('biik.animal_page.active') }}</option><option value="inactive">{{ __('biik.animal_page.inactive') }}</option></select>@error('status') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div><label for="animal-pen" class="form-label">{{ __('biik.animal_page.pen') }}</label><select id="animal-pen" wire:model="pen_id" class="form-input"><option value="">{{ __('biik.animal_page.unassigned') }}</option>@foreach ($pens as $pen)<option value="{{ $pen->id }}">{{ $pen->name }} ({{ $pen->active_animals_count }}/{{ $pen->capacity }})</option>@endforeach</select>@error('pen_id') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div class="sm:col-span-2"><label for="animal-notes" class="form-label">{{ __('biik.animal_page.notes') }}</label><textarea id="animal-notes" wire:model="notes" rows="3" class="form-input"></textarea>@error('notes') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
                    <div class="flex gap-3 sm:col-span-2"><button type="submit" class="button button-primary">{{ __('biik.animal_page.save') }}</button><button type="button" wire:click="closePanels" class="button button-outline">{{ __('biik.animal_page.cancel') }}</button></div>
                </form>
            </section>
        @endif
    </section>
</div>
