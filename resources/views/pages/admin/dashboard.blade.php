@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-cream-50">
        <header class="border-b border-forest-950/10 bg-white">
            <div class="site-container flex min-h-20 items-center justify-between gap-5">
                <a href="{{ route('admin.dashboard') }}" class="brand-mark" aria-label="BIIK FarmSuite admin dashboard">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span>
                    <span class="leading-tight"><span class="block text-base font-extrabold">BIIK</span><span class="block text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-forest-800">Admin</span></span>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="button button-outline !px-5 !py-2.5">Log out</button></form>
            </div>
        </header>
        <section class="site-container py-14 sm:py-20">
            <p class="eyebrow">Administrator portal</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-forest-950">FarmSuite administration</h1>
            <p class="mt-3 max-w-2xl leading-7 text-forest-800/75">Manage user access and configure the farm modules available to basic-user roles.</p>
            <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('admin.users') }}" class="feature-card block">
                    <span class="feature-icon feature-icon-green" aria-hidden="true">U</span>
                    <h2 class="mt-4 text-lg font-semibold text-forest-950">User management</h2>
                    <p>Search accounts, assign non-administrator roles, and activate or deactivate access.</p>
                </a>
                <a href="{{ route('admin.roles') }}" class="feature-card block">
                    <span class="feature-icon feature-icon-pink" aria-hidden="true">R</span>
                    <h2 class="mt-4 text-lg font-semibold text-forest-950">Roles and permissions</h2>
                    <p>Configure module-level access for the basic-user role and other non-admin roles.</p>
                </a>
                <a href="{{ route('admin.permissions') }}" class="feature-card block">
                    <span class="feature-icon feature-icon-cream" aria-hidden="true">P</span>
                    <h2 class="mt-4 text-lg font-semibold text-forest-950">Permission catalog</h2>
                    <p>Review every supported module and action permission.</p>
                </a>
                <a href="{{ route('admin.audit-logs') }}" class="feature-card block">
                    <span class="feature-icon feature-icon-pink" aria-hidden="true">A</span>
                    <h2 class="mt-4 text-lg font-semibold text-forest-950">Audit trail</h2>
                    <p>Search and review authentication and access-control events.</p>
                </a>
            </div>
            <div class="mt-8 rounded-2xl border border-forest-950/10 bg-white p-6">
                <h2 class="font-semibold text-forest-950">Administrator provisioning</h2>
                <p class="mt-2 text-sm leading-6 text-forest-800/75">New administrator access is granted through the verified-account CLI provisioning command. It cannot be assigned during public registration or in user management.</p>
            </div>
        </section>
    </div>
@endsection
