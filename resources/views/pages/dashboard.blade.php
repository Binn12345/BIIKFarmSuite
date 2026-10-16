@extends('layouts.app')

@section('content')
    @if ($isAdmin)
        @include('pages.admin.analytics-dashboard')
    @else
        <section class="site-container py-8 sm:py-12">
            <p class="eyebrow">{{ __('biik.your_workspace') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">
                {{ __('biik.welcome', ['name' => auth()->user()->name]) }}
            </h1>
            <p class="mt-3 max-w-2xl leading-7 text-forest-800/75">{{ __('biik.dashboard_intro') }}</p>

            @if (count($summary))
                <section class="mt-7" aria-label="{{ __('biik.farm_summary') }}">
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($summary as $item)
                            <article class="rounded-2xl border border-forest-950/10 bg-white p-5 shadow-sm">
                                <h2 class="text-sm font-semibold text-forest-800">{{ $item['label'] }}</h2>
                                <p class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">{{ $item['value'] }}</p>
                                <p class="mt-1 text-xs text-forest-800/70">{{ $item['detail'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="mt-8 rounded-2xl border border-forest-950/10 bg-white p-5 sm:p-6" aria-labelledby="recent-farm-records">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="recent-farm-records" class="text-lg font-semibold text-forest-950">{{ __('biik.analytics.recent_activity') }}</h2>
                    <p class="text-sm text-forest-800/70">{{ __('biik.dashboard_intro') }}</p>
                </div>
                @if ($recentRecords->isEmpty())
                    <p class="mt-4 rounded-xl bg-cream-50 p-4 text-sm text-forest-800/75">{{ __('biik.analytics.no_recent') }}</p>
                @else
                    <ul class="mt-3 divide-y divide-forest-950/10">
                        @foreach ($recentRecords as $record)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-forest-950">{{ $record->title ?: $record->reference ?: __('biik.record_label') }}</p>
                                    <p class="text-sm text-forest-800/70">{{ __('biik.module_names.modules.'.$record->module) }} · {{ $record->record_date->translatedFormat('M j, Y') }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-cream-100 px-3 py-1 text-xs font-semibold text-forest-800">{{ __('biik.values.'.$record->status) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </section>
    @endif
@endsection
