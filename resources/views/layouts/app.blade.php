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
        @php($currentUser = auth()->user())
        @php($hasNavigationPicture = $currentUser->newQuery()->whereKey($currentUser->id)->whereNotNull('profile_picture')->exists())
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" x-init="$watch('sidebarOpen', value => { document.body.classList.toggle('overflow-hidden', value); if (value) $nextTick(() => $refs.closeSidebar.focus()); else $refs.openSidebar.focus() })">
            <header class="sticky top-0 z-40 border-b border-forest-950/10 bg-white">
                <div class="site-container flex min-h-16 items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button x-ref="openSidebar" type="button" class="grid h-10 w-10 place-items-center rounded-xl border border-forest-950/15 text-xl text-forest-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-pig-500 lg:hidden" @click="sidebarOpen = true" aria-controls="mobile-sidebar" :aria-expanded="sidebarOpen.toString()" aria-label="{{ __('biik.open_navigation') }}">☰</button>
                        <a href="{{ route('dashboard') }}" class="brand-mark font-extrabold">BIIK <span class="font-normal">FarmSuite</span></a>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <form method="POST" action="{{ route('locale.switch') }}" class="flex items-center gap-2">
                            @csrf
                            <label for="locale-select" class="sr-only">{{ __('biik.language') }}</label>
                            <select id="locale-select" name="locale" onchange="this.form.submit()" class="rounded-lg border border-forest-950/15 bg-white px-2 py-2 text-sm text-forest-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-forest-700" aria-label="{{ __('biik.language') }}">
                                <option value="en" @selected(app()->getLocale() === 'en')>English</option>
                                <option value="fil" @selected(app()->getLocale() === 'fil')>Filipino</option>
                            </select>
                        </form>
                        <a href="{{ route('account.profile') }}" class="inline-flex items-center gap-2 rounded-xl px-2 py-1.5 text-sm font-semibold text-forest-900 hover:bg-cream-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-forest-700">
                            @if ($hasNavigationPicture)
                                <img src="{{ route('users.profile-picture', $currentUser->id) }}" alt="" class="h-9 w-9 rounded-full object-cover">
                            @else
                                <span class="grid h-9 w-9 place-items-center rounded-full bg-forest-900 text-xs font-bold text-white" aria-hidden="true">{{ collect(explode(' ', $currentUser->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</span>
                            @endif
                            <span class="hidden sm:inline">{{ __('biik.profile') }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-xl px-2 py-2 text-sm font-semibold text-pig-500 hover:bg-cream-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-pig-500 sm:px-3">{{ __('biik.sign_out') }}</button>
                        </form>
                    </div>
                </div>
            </header>
            <div class="mx-auto flex w-full max-w-[100rem]">
                <aside class="sticky top-16 hidden h-[calc(100vh-4rem)] w-72 shrink-0 overflow-y-auto border-r border-forest-950/10 bg-white px-4 py-6 lg:block">
                    @include('layouts.sidebar')
                </aside>
                <div class="min-w-0 flex-1">
                    <main id="main-content" class="min-h-[calc(100vh-4rem)]">
                        @yield('content')
                        {{ $slot ?? '' }}
                    </main>
                </div>
            </div>
            <div id="mobile-sidebar" x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="{{ __('biik.farm_navigation') }}" @keydown.tab="
                const focusable = [...$el.querySelectorAll('a[href], button:not([disabled]), summary')].filter(element => element.offsetParent !== null);
                const first = focusable[0], last = focusable[focusable.length - 1];
                if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); }
                else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }
            ">
                <button type="button" class="absolute inset-0 bg-forest-950/60" @click="sidebarOpen = false" aria-label="{{ __('biik.close_navigation') }}"></button>
                <aside class="relative h-full w-[min(88vw,21rem)] overflow-y-auto bg-white px-4 py-5 shadow-2xl">
                    <div class="mb-5 flex items-center justify-between">
                        <span class="brand-mark font-extrabold">BIIK <span class="font-normal">FarmSuite</span></span>
                        <button x-ref="closeSidebar" type="button" class="rounded-lg px-3 py-2 text-xl text-forest-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-forest-700" @click="sidebarOpen = false" aria-label="{{ __('biik.close_navigation') }}">×</button>
                    </div>
                    @include('layouts.sidebar')
                </aside>
            </div>
        </div>
    @endauth
    @guest
    <form method="POST" action="{{ route('locale.switch') }}" class="fixed bottom-4 right-4 z-50 flex items-center gap-2 rounded-xl border border-forest-950/10 bg-white/95 p-2 shadow-sm">
        @csrf
        <label for="guest-locale" class="sr-only">{{ __('biik.language') }}</label>
        <select id="guest-locale" name="locale" onchange="this.form.submit()" class="rounded-lg border-0 bg-transparent px-2 py-1 text-sm text-forest-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-forest-700" aria-label="{{ __('biik.language') }}">
            <option value="en" @selected(app()->getLocale() === 'en')>English</option>
            <option value="fil" @selected(app()->getLocale() === 'fil')>Filipino</option>
        </select>
    </form>
    <main id="main-content">
        @yield('content')
        {{ $slot ?? '' }}
    </main>
    @endguest
    @livewireScripts
</body>
</html>
