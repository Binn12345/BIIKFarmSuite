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
        <section class="site-container py-16 sm:py-24">
            <p class="eyebrow">Your workspace</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-forest-950">Welcome, {{ auth()->user()->name }}.</h1>
            <div class="mt-8 max-w-2xl rounded-3xl border border-forest-950/10 bg-white p-7 shadow-sm sm:p-9">
                <span class="feature-icon feature-icon-green">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path d="M4 19V8l8-4 8 4v11H4Z" stroke-linejoin="round"/><path d="M8 19v-6h8v6M4 8l8 5 8-5" stroke-linejoin="round"/>
                    </svg>
                </span>
                <h2 class="mt-5 text-xl font-semibold text-forest-950">Your farm workspace is ready.</h2>
                <p class="mt-2 leading-7 text-forest-800/75">Choose an available workspace below. You will only see modules enabled for your account.</p>
            </div>
            @php($availableModules = collect($modules)->filter(fn ($module) => auth()->user()->can($module['permission'])))
            @if ($availableModules->isNotEmpty())
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($modules as $slug => $module)
                        @can($module['permission'])
                            <a href="{{ route('modules.'.$slug) }}" class="feature-card block !min-h-0">
                                <h2 class="font-semibold text-forest-950">{{ $module['label'] }}</h2>
                                <p class="mt-2 text-sm leading-6 text-forest-800/75">Open your authorized workspace.</p>
                            </a>
                        @endcan
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
