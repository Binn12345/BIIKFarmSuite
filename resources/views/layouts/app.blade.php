<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f6f4ec">
    <title>{{ $title ?? config('app.name', 'BIIK FarmSuite') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-cream-50 font-sans text-forest-950 antialiased">
    <a href="#main-content" class="skip-link">Skip to content</a>
    @auth
        @php($navigationModules = \App\Support\DashboardModules::for(auth()->user()))
        <header class="sticky top-0 z-30 border-b border-forest-950/10 bg-white">
            <div class="site-container flex min-h-16 items-center justify-between gap-4">
                <a href="{{ route('dashboard') }}" class="brand-mark font-extrabold">BIIK <span class="font-normal">FarmSuite</span></a>
                <details class="relative">
                    <summary class="cursor-pointer list-none rounded-xl border border-forest-950/15 px-4 py-2 text-sm font-semibold text-forest-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-pig-500">
                        Navigation <span aria-hidden="true">⌄</span>
                    </summary>
                    <nav aria-label="Authenticated navigation" class="absolute right-0 mt-2 max-h-[75vh] w-[min(92vw,24rem)] overflow-y-auto rounded-2xl border border-forest-950/10 bg-white p-4 shadow-xl">
                        <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-cream-100 text-forest-950' : 'text-forest-800 hover:bg-cream-50' }}">Dashboard</a>
                        @foreach ($navigationModules as $category => $items)
                            <h2 class="px-3 pb-1 pt-4 text-xs font-bold uppercase tracking-wider text-forest-700">{{ $category }}</h2>
                            @foreach ($items as $item)
                                <a href="{{ route($item['route']) }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs($item['route']) ? 'bg-cream-100 font-semibold text-forest-950' : 'text-forest-800 hover:bg-cream-50' }}">{{ $item['title'] }}</a>
                            @endforeach
                        @endforeach
                        <h2 class="px-3 pb-1 pt-4 text-xs font-bold uppercase tracking-wider text-forest-700">Account</h2>
                        <a href="{{ route('account.profile') }}" class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('account.profile') ? 'bg-cream-100 font-semibold text-forest-950' : 'text-forest-800 hover:bg-cream-50' }}">My Profile</a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-3 border-t border-forest-950/10 pt-3">
                            @csrf
                            <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-pig-500 hover:bg-cream-50">Log Out</button>
                        </form>
                    </nav>
                </details>
            </div>
        </header>
    @endauth
    <main id="main-content">
        @yield('content')
        {{ $slot ?? '' }}
    </main>
    @livewireScripts
</body>
</html>
