@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-cream-50">
        <header class="border-b border-forest-950/10 bg-white">
            <div class="site-container flex min-h-20 items-center justify-between gap-5">
                <a href="{{ route('admin.dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">{{ __('biik.admin_ui.catalog_title') }}</span></span></a>
                <a href="{{ route('admin.dashboard') }}" class="button button-outline !px-5 !py-2.5">{{ __('biik.admin_ui.dashboard') }}</a>
            </div>
        </header>
        <section class="site-container py-12 sm:py-16">
            <p class="eyebrow">{{ __('biik.admin_ui.portal') }}</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">{{ __('biik.admin_ui.catalog_title') }}</h1>
            <p class="mt-3 max-w-3xl leading-7 text-forest-800/75">{{ __('biik.admin_ui.catalog_description') }}</p>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($catalog as $slug => $module)
                    <article class="feature-card !min-h-0">
                        <h2 class="text-base font-semibold text-forest-950">{{ in_array($slug, ['users', 'roles', 'audit-logs'], true) ? __('biik.module_names.admin.'.$slug) : __('biik.module_names.modules.'.$slug) }}</h2>
                        <ul class="mt-4 space-y-2">
                            @foreach ($module['permissions'] as $action => $permission)
                                <li class="flex items-center justify-between gap-3 text-sm">
                                    <span class="text-forest-800">{{ __('biik.permission_actions.'.$action) }}</span>
                                    <code class="rounded bg-cream-100 px-2 py-1 text-xs text-forest-950">{{ $permission }}</code>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
@endsection
