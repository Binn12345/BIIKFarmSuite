@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-cream-50">
        <section class="site-container py-8 sm:py-10">
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
