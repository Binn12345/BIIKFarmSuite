<nav aria-label="{{ __('biik.farm_navigation') }}" class="space-y-2">
    <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" @class([
        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
        'bg-forest-900 text-white' => request()->routeIs('dashboard'),
        'text-forest-800 hover:bg-cream-100' => ! request()->routeIs('dashboard'),
    ])>
        <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/15" aria-hidden="true">⌂</span>
        {{ __('biik.dashboard_home') }}
    </a>
    @foreach ($navigationModules as $category => $items)
        <details class="group rounded-xl" @if (collect($items)->contains(fn ($item) => request()->routeIs($item['route']))) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl px-3 py-3 text-xs font-bold uppercase tracking-wider text-forest-700 hover:bg-cream-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-forest-700">
                <span>{{ __('biik.groups.'.$category, [], app()->getLocale()) !== 'biik.groups.'.$category ? __('biik.groups.'.$category) : $category }}</span>
                <span class="transition group-open:rotate-180" aria-hidden="true">⌄</span>
            </summary>
            <div class="ml-3 space-y-1 border-l border-forest-950/10 pl-3">
                @foreach ($items as $item)
                    <a href="{{ route($item['route']) }}" @click="sidebarOpen = false" @class([
                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition',
                        'bg-cream-100 font-semibold text-forest-950' => request()->routeIs($item['route']),
                        'text-forest-800 hover:bg-cream-50' => ! request()->routeIs($item['route']),
                    ])>
                        <span class="h-1.5 w-1.5 rounded-full bg-pig-500" aria-hidden="true"></span>
                        {{ __('biik.module_names.'.$item['route'], [], app()->getLocale()) !== 'biik.module_names.'.$item['route'] ? __('biik.module_names.'.$item['route']) : $item['title'] }}
                    </a>
                @endforeach
            </div>
        </details>
    @endforeach
</nav>
