<section class="site-container py-7 sm:py-10">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ __('biik.admin_ui.portal') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">{{ __('biik.analytics.admin_welcome') }}</h1>
            <p class="mt-2 max-w-2xl text-forest-800/75">{{ __('biik.analytics.admin_intro') }}</p>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-3">
            <label for="analytics-period" class="text-sm font-semibold text-forest-800">{{ __('biik.analytics.period') }}</label>
            <select id="analytics-period" name="period" onchange="this.form.submit()" class="form-input !w-auto min-w-36">
                @foreach ([3, 6, 12] as $months)
                    <option value="{{ $months }}" @selected($analytics['period'] === $months)>{{ __('biik.analytics.month_'.$months) }}</option>
                @endforeach
            </select>
        </form>
    </header>

    <section class="mt-7" aria-label="{{ __('biik.farm_summary') }}">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($analytics['metrics'] as $metric)
                @php
                    $metricLabel = __('biik.analytics.metric_'.$metric['key']);
                @endphp
                <a href="{{ route($metric['route']) }}" class="analytics-card rounded-2xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5">
                    <h2 class="text-sm font-semibold">{{ $metricLabel }}</h2>
                    <p class="mt-2 break-words text-2xl font-semibold tracking-tight sm:text-3xl">{{ $metric['value'] }}</p>
                    <p class="mt-1 text-xs">{{ $metric['detail'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <div class="mt-5 grid min-w-0 gap-5 xl:grid-cols-2">
        <section class="analytics-panel min-w-0 rounded-2xl border p-4 shadow-sm sm:p-6" aria-labelledby="sales-trend-heading">
            <div>
                <h2 id="sales-trend-heading" class="text-lg font-semibold">{{ __('biik.analytics.sales_trend') }}</h2>
                <p class="mt-1 text-sm">{{ __('biik.analytics.sales_trend_description') }}</p>
            </div>
            @if (array_sum(array_column($analytics['sales'], 'value')) > 0)
                @php
                    $salesMax = max(array_column($analytics['sales'], 'value'));
                    $salesPoints = collect($analytics['sales'])->map(function (array $month, int $index) use ($analytics, $salesMax): string {
                        $x = count($analytics['sales']) === 1 ? 320 : 42 + ($index * (556 / (count($analytics['sales']) - 1)));
                        $y = 150 - (($month['value'] / $salesMax) * 120);

                        return number_format($x, 1, '.', '').','.number_format($y, 1, '.', '');
                    })->implode(' ');
                @endphp
                <div class="mt-5 w-full overflow-hidden">
                    <svg viewBox="0 0 640 200" class="h-auto w-full" role="img" aria-label="{{ __('biik.analytics.sales_trend') }}">
                        <path d="M42 150H598M42 90H598M42 30H598" class="chart-grid" fill="none" stroke-width="1" />
                        <polyline points="{{ $salesPoints }}" fill="none" stroke="var(--theme-accent)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($analytics['sales'] as $index => $month)
                            @php($pointX = count($analytics['sales']) === 1 ? 320 : 42 + ($index * (556 / (count($analytics['sales']) - 1))))
                            @php($pointY = 150 - (($month['value'] / $salesMax) * 120))
                            <circle cx="{{ $pointX }}" cy="{{ $pointY }}" r="4" fill="var(--theme-accent)" />
                            <text x="{{ $pointX }}" y="182" text-anchor="middle" class="chart-label">{{ $month['label'] }}</text>
                        @endforeach
                    </svg>
                </div>
                <ul class="sr-only">
                    @foreach ($analytics['sales'] as $month)
                        <li>{{ $month['label'] }}: {{ number_format($month['value'], 2) }}</li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 rounded-xl bg-cream-50 p-5 text-sm">{{ __('biik.analytics.no_sales') }}</p>
            @endif
        </section>

        <section class="analytics-panel min-w-0 rounded-2xl border p-4 shadow-sm sm:p-6" aria-labelledby="animal-category-heading">
            <div>
                <h2 id="animal-category-heading" class="text-lg font-semibold">{{ __('biik.analytics.animals_by_category') }}</h2>
                <p class="mt-1 text-sm">{{ __('biik.analytics.all_statuses') }}</p>
            </div>
            @if ($analytics['animalCategories'])
                @php($categoryMax = max(array_column($analytics['animalCategories'], 'value')))
                <ul class="mt-5 space-y-4" role="img" aria-label="{{ __('biik.analytics.animals_by_category') }}">
                    @foreach ($analytics['animalCategories'] as $category)
                        <li>
                            <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                <span class="truncate font-medium">{{ $category['label'] }}</span>
                                <span class="shrink-0 font-semibold">{{ $category['value'] }}</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-cream-100">
                                <div class="h-full rounded-full bg-forest-900" style="width: {{ max(3, ($category['value'] / $categoryMax) * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 rounded-xl bg-cream-50 p-5 text-sm">{{ __('biik.analytics.no_categories') }}</p>
            @endif
        </section>
    </div>

    <section class="analytics-panel mt-5 rounded-2xl border p-4 shadow-sm sm:p-6" aria-labelledby="admin-recent-heading">
        <h2 id="admin-recent-heading" class="text-lg font-semibold">{{ __('biik.analytics.recent_activity') }}</h2>
        @if ($analytics['recent']->isEmpty())
            <p class="mt-4 rounded-xl bg-cream-50 p-4 text-sm">{{ __('biik.analytics.no_recent') }}</p>
        @else
            <div class="mt-3 divide-y divide-forest-950/10">
                @foreach ($analytics['recent'] as $record)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $record->title ?: $record->reference ?: __('biik.record_label') }}</p>
                            <p class="text-sm text-forest-800/75">{{ __('biik.module_names.modules.'.$record->module) }} · {{ $record->record_date->translatedFormat('M j, Y') }}{{ $record->reference ? ' · '.$record->reference : '' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-cream-100 px-3 py-1 text-xs font-semibold">{{ __('biik.values.'.$record->status) }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</section>
