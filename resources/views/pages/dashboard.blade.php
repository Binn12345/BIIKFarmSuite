@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-cream-50">
        <header class="border-b border-forest-950/10 bg-white">
            <div class="site-container flex min-h-20 items-center justify-between gap-5">
                <a href="{{ route('dashboard') }}" class="brand-mark" aria-label="BIIK FarmSuite dashboard">
                    <svg viewBox="0 0 40 40" aria-hidden="true" class="h-10 w-10 shrink-0">
                        <rect width="40" height="40" rx="13" fill="#214a37"/>
                        <path d="M11 26.5 19.8 12l9.1 14.5H11Z" fill="#f6f4ec"/>
                        <path d="M20 12v14.5m-5.2 0c.9-3.3 3.1-5 5.2-5s4.3 1.7 5.2 5" fill="none" stroke="#dfe8d8" stroke-linecap="round" stroke-width="1.6"/>
                        <circle cx="20" cy="17" r="2.4" fill="#e9a9a2"/>
                    </svg>
                    <span class="leading-tight">
                        <span class="block text-base font-extrabold tracking-tight">BIIK</span>
                        <span class="block text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-forest-800">FarmSuite</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="button button-outline !px-5 !py-2.5">Log out</button>
                </form>
            </div>
        </header>
        <section class="site-container py-12 sm:py-16">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="eyebrow">Your workspace</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Welcome, {{ auth()->user()->name }}.</h1>
                    <p class="mt-3 max-w-2xl leading-7 text-forest-800/75">Open the areas available to your account. Access is based on your role and permissions.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full bg-forest-900/10 px-4 py-2 text-sm font-semibold text-forest-900">
                    {{ auth()->user()->hasRole('admin') ? 'Admin' : 'Basic User' }}
                </span>
            </div>

            @if (count($summary))
                <section class="mt-8" aria-label="Farm summary">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
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

            @forelse ($modules as $category => $categoryModules)
                <section class="mt-10" aria-labelledby="module-category-{{ \Illuminate\Support\Str::slug($category) }}">
                    <h2 id="module-category-{{ \Illuminate\Support\Str::slug($category) }}" class="text-xl font-semibold text-forest-950">{{ $category }}</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($categoryModules as $module)
                            <a href="{{ route($module['route']) }}" class="feature-card group block !min-h-0 rounded-2xl transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-forest-700 focus-visible:ring-offset-2">
                                <span class="feature-icon {{ $loop->even ? 'feature-icon-pink' : 'feature-icon-green' }}" aria-hidden="true">
                                    @switch($module['icon'])
                                        @case('users')
                                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m6-10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm6 0a3 3 0 1 0 0-6m1 10h.5a3.5 3.5 0 0 1 3.5 3.5V20" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @break
                                        @case('shield')
                                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 19 6v5c0 4.5-2.9 8-7 10-4.1-2-7-5.5-7-10V6l7-3Z" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @break
                                        @case('list')
                                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01" stroke-linecap="round"/></svg>
                                            @break
                                        @case('clock')
                                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @break
                                        @case('animal')
                                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 13c0-3 2.7-5 7-5s7 2 7 5v3a2 2 0 0 1-2 2h-1l-1-2h-6l-1 2H7a2 2 0 0 1-2-2v-3Z" stroke-linejoin="round"/><path d="M7 9 5 6m12 3 2-3M9 12h.01M15 12h.01M10 16h4" stroke-linecap="round"/></svg>
                                            @break
                                        @case('pen')
                                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20V5m16 15V5M4 9h16M4 15h16M8 9v6m4-6v6m4-6v6M2 20h20" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @break
                                    @endswitch
                                </span>
                                <h3 class="mt-4 text-lg font-semibold text-forest-950">{{ $module['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-forest-800/75">{{ $module['description'] }}</p>
                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-forest-800 group-hover:text-forest-950">
                                    Open module <span aria-hidden="true">→</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="mt-10 rounded-3xl border border-forest-950/10 bg-white p-7 shadow-sm sm:p-9">
                    <h2 class="text-xl font-semibold text-forest-950">No modules are available yet</h2>
                    <p class="mt-2 max-w-2xl leading-7 text-forest-800/75">No modules are currently available to your account. Contact an administrator if you need access.</p>
                </div>
            @endforelse

        </section>
    </div>
@endsection
