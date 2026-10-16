@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-cream-50">
        <header class="border-b border-forest-950/10 bg-white">
            <div class="site-container flex min-h-20 items-center justify-between gap-5">
                <a href="{{ route('admin.dashboard') }}" class="brand-mark" aria-label="BIIK FarmSuite {{ __('biik.admin_ui.dashboard') }}">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span>
                    <span class="leading-tight"><span class="block text-base font-extrabold">BIIK</span><span class="block text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-forest-800">{{ __('biik.roles.admin') }}</span></span>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="button button-outline !px-5 !py-2.5">{{ __('biik.admin_ui.logout') }}</button></form>
            </div>
        </header>
        <section class="site-container py-14 sm:py-20">
            <p class="eyebrow">{{ __('biik.admin_ui.portal') }}</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-forest-950">{{ __('biik.admin_ui.farm_admin') }}</h1>
            <p class="mt-3 max-w-2xl leading-7 text-forest-800/75">{{ __('biik.admin_ui.dashboard_intro') }}</p>
            <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('admin.users') }}" class="feature-card block">
                    <span class="feature-icon feature-icon-green" aria-hidden="true">U</span>
                    <h2 class="mt-4 text-lg font-semibold text-forest-950">{{ __('biik.admin_ui.users_title') }}</h2>
                    <p>{{ __('biik.admin_ui.users_description') }}</p>
                </a>
                <a href="{{ route('admin.roles') }}" class="feature-card block">
                    <span class="feature-icon feature-icon-pink" aria-hidden="true">R</span>
                    <h2 class="mt-4 text-lg font-semibold text-forest-950">{{ __('biik.admin_ui.roles_title') }}</h2>
                    <p>{{ __('biik.admin_ui.roles_description') }}</p>
                </a>
                <a href="{{ route('admin.permissions') }}" class="feature-card block">
                    <span class="feature-icon feature-icon-cream" aria-hidden="true">P</span>
                    <h2 class="mt-4 text-lg font-semibold text-forest-950">{{ __('biik.admin_ui.catalog_title') }}</h2>
                    <p>{{ __('biik.admin_ui.catalog_intro') }}</p>
                </a>
                <a href="{{ route('admin.audit-logs') }}" class="feature-card block">
                    <span class="feature-icon feature-icon-pink" aria-hidden="true">A</span>
                    <h2 class="mt-4 text-lg font-semibold text-forest-950">{{ __('biik.admin_ui.audit_card') }}</h2>
                    <p>{{ __('biik.admin_ui.audit_card_description') }}</p>
                </a>
            </div>
            <div class="mt-8 rounded-2xl border border-forest-950/10 bg-white p-6">
                <h2 class="font-semibold text-forest-950">{{ __('biik.admin_ui.provisioning_title') }}</h2>
                <p class="mt-2 text-sm leading-6 text-forest-800/75">{{ __('biik.admin_ui.provisioning_description') }}</p>
            </div>
        </section>
    </div>
@endsection
