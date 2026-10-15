@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-cream-50">
        <header class="border-b border-forest-950/10 bg-white">
            <div class="site-container flex min-h-20 items-center justify-between gap-5">
                <a href="{{ route('dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">FarmSuite</span></span></a>
                <a href="{{ route('dashboard') }}" class="button button-outline !px-5 !py-2.5">Dashboard</a>
            </div>
        </header>
        <section class="site-container py-16 sm:py-24">
            <p class="eyebrow">Farm workspace</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-forest-950">{{ $module }}</h1>
            <div class="mt-8 max-w-2xl rounded-3xl border border-forest-950/10 bg-white p-7 shadow-sm sm:p-9">
                <p class="leading-7 text-forest-800/75">You have permission to access this module. Farm management features will be added in a later phase.</p>
            </div>
        </section>
    </div>
@endsection
