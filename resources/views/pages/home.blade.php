@extends('layouts.app')

@section('content')
    <header class="border-b border-forest-950/10 bg-cream-50">
        <nav class="site-container flex min-h-20 items-center justify-between gap-6" aria-label="Main navigation">
            <a href="{{ route('home') }}" class="brand-mark" aria-label="BIIK FarmSuite home">
                <svg viewBox="0 0 40 40" aria-hidden="true" class="h-10 w-10 shrink-0">
                    <rect width="40" height="40" rx="13" fill="#214a37"/>
                    <path d="M11 26.5 19.8 12l9.1 14.5H11Z" fill="#f6f4ec"/>
                    <path d="M20 12v14.5M14.8 26.5c.9-3.3 3.1-5 5.2-5s4.3 1.7 5.2 5" fill="none" stroke="#dfe8d8" stroke-linecap="round" stroke-width="1.6"/>
                    <circle cx="20" cy="17" r="2.4" fill="#e9a9a2"/>
                </svg>
                <span class="leading-tight">
                    <span class="block text-base font-extrabold tracking-tight">BIIK</span>
                    <span class="block text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-forest-800">FarmSuite</span>
                </span>
            </a>

            <div class="hidden items-center gap-8 md:flex">
                <a class="nav-link" href="#home">Home</a>
                <a class="nav-link" href="#features">Features</a>
                <a class="nav-link" href="#about">About</a>
                <a class="nav-link" href="#contact">Contact</a>
                <a class="button button-outline !px-5 !py-2.5" href="{{ route('login') }}">Login</a>
                <a class="button button-primary !px-5 !py-2.5" href="{{ route('register') }}">Register</a>
            </div>

            <details class="mobile-nav relative md:hidden">
                <summary class="mobile-nav-toggle" aria-label="Toggle navigation menu">
                    <span class="sr-only">Open navigation menu</span>
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                    </svg>
                </summary>
                <div class="mobile-nav-menu">
                    <a href="#home">Home</a>
                    <a href="#features">Features</a>
                    <a href="#about">About</a>
                    <a href="#contact">Contact</a>
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                </div>
            </details>
        </nav>
    </header>

    <section id="home" class="hero-section scroll-mt-8">
        <div class="site-container grid items-center gap-14 py-16 sm:py-20 lg:grid-cols-[1.04fr_0.96fr] lg:gap-12 lg:py-24">
            <div class="max-w-2xl">
                <p class="eyebrow"><span class="eyebrow-dot"></span> A calmer way to run your farm</p>
                <h1 class="mt-6 text-4xl font-semibold leading-[1.08] tracking-tight text-forest-950 sm:text-5xl lg:text-[3.65rem]">
                    Smarter Pig Farm Management Starts Here.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-forest-800/80">
                    BIIK FarmSuite helps you keep piglet, breeding, and animal records together with sales and farm expenses—so the details of your farm are easier to manage.
                </p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" class="button button-primary">
                        Get Started
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="#features" class="button button-secondary">Explore Features</a>
                </div>
                <p class="mt-5 text-sm text-forest-800/65">A practical home for the records behind your day-to-day work.</p>
            </div>

            <div class="hero-art" aria-label="Illustration of farm records organized in one workspace" role="img">
                <div class="hero-art-orbit hero-art-orbit-one"></div>
                <div class="hero-art-orbit hero-art-orbit-two"></div>
                <div class="workspace-card">
                    <div class="flex items-center justify-between border-b border-forest-950/10 px-5 py-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-forest-800/60">Farm workspace</p>
                            <p class="mt-1 text-sm font-semibold text-forest-950">Everything in its place</p>
                        </div>
                        <span class="workspace-icon">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path d="M4 19V9l8-5 8 5v10H4Z" stroke-linejoin="round"/>
                                <path d="M9 19v-6h6v6M4 9l8 5 8-5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <div class="workspace-scene">
                        <svg viewBox="0 0 460 230" class="h-full w-full" fill="none" aria-hidden="true">
                            <path d="M0 153c62-36 112-31 173-2 63 30 107-9 170-23 42-10 80-1 117 16v86H0v-77Z" fill="#b6c6a1"/>
                            <path d="M0 183c75-37 134-21 197 7 72 31 156-33 263-18v53H0v-42Z" fill="#8ca680"/>
                            <path d="m307 119 61-38 63 38v62H307v-62Z" fill="#f5eee0"/>
                            <path d="M297 121h144l-73-49-71 49Z" fill="#214a37"/>
                            <path d="M357 101h22v23h-22z" fill="#e9a9a2"/>
                            <path d="M357 148h17v33h-17z" fill="#d5b77b"/>
                            <path d="M28 166c8-23 20-34 38-38m-38 38c7-15 17-20 31-21m14 32c6-27 21-42 42-48m-42 48c8-16 20-23 35-23" stroke="#54714b" stroke-linecap="round" stroke-width="4"/>
                            <circle cx="75" cy="127" r="12" fill="#71916a"/>
                            <circle cx="111" cy="119" r="14" fill="#71916a"/>
                            <path d="M159 174c0-14 11-25 26-25h24c14 0 25 11 25 25v6h-75v-6Z" fill="#f1c1b8"/>
                            <ellipse cx="199" cy="176" rx="41" ry="24" fill="#e9a9a2"/>
                            <ellipse cx="234" cy="170" rx="18" ry="16" fill="#e9a9a2"/>
                            <path d="m222 157 2-16 13 12m-63 0-4-14 15 9" fill="#e9a9a2" stroke="#c98079" stroke-linejoin="round" stroke-width="2"/>
                            <ellipse cx="245" cy="174" rx="9" ry="6" fill="#cf817b"/>
                            <circle cx="229" cy="166" r="2" fill="#543d36"/>
                            <path d="M176 194v12m42-12v12m-50-33c-9-6-14-2-14 5 0 4 3 6 8 5" stroke="#c98079" stroke-linecap="round" stroke-width="3"/>
                            <path d="M270 144c4-21 16-31 35-31m-29 31c2-14 10-20 22-22" stroke="#54714b" stroke-linecap="round" stroke-width="4"/>
                            <circle cx="308" cy="111" r="10" fill="#71916a"/>
                        </svg>
                    </div>
                    <div class="grid grid-cols-3 gap-2 px-4 py-4 sm:gap-3">
                        <div class="workspace-tile"><span class="tile-dot bg-forest-700"></span><span>Animals</span></div>
                        <div class="workspace-tile"><span class="tile-dot bg-pig-400"></span><span>Breeding</span></div>
                        <div class="workspace-tile"><span class="tile-dot bg-amber-500"></span><span>Records</span></div>
                    </div>
                </div>
                <span class="hero-floating-note">Made for the work you do every day</span>
            </div>
        </div>
    </section>

    <section id="features" class="section-pad scroll-mt-8 bg-white">
        <div class="site-container">
            <div class="section-heading">
                <p class="eyebrow">One suite, useful details</p>
                <h2 class="section-title">The essentials of farm management, together.</h2>
                <p class="section-copy">Keep the information you rely on organized and accessible as your farm work moves from one day to the next.</p>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <article class="feature-card">
                    <span class="feature-icon feature-icon-green">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M4 19V8l8-4 8 4v11H4Z" stroke-linejoin="round"/><path d="M8 19v-6h8v6M4 8l8 5 8-5" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3>Animal Management</h3>
                    <p>Keep animal profiles and important details in one organized place.</p>
                </article>
                <article class="feature-card">
                    <span class="feature-icon feature-icon-pink">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M12 20s-7-4.3-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.7-7 10-7 10Z" stroke-linejoin="round"/><path d="M8 12h2l1.2-2.2L13 15l1.2-3H16" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3>Breeding and Farrowing</h3>
                    <p>Record breeding activity and farrowing information for your herd.</p>
                </article>
                <article class="feature-card">
                    <span class="feature-icon feature-icon-cream">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M5 19V9l7-5 7 5v10H5Z" stroke-linejoin="round"/><path d="M9 19v-5h6v5M9 10h.01M15 10h.01" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <h3>Piglet Records</h3>
                    <p>Track piglet details from birth and keep their records connected.</p>
                </article>
                <article class="feature-card">
                    <span class="feature-icon feature-icon-cream">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M4 7h16v13H4zM8 7V4h8v3M8 12h8m-8 4h5" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 10h.01" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <h3>Farm Sales</h3>
                    <p>Keep your farm sales recorded alongside the rest of your activity.</p>
                </article>
                <article class="feature-card">
                    <span class="feature-icon feature-icon-pink">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M4 19h16M6 16V9m6 7V5m6 11v-4" stroke-linecap="round"/><path d="m5 7 6-3 6 5 3-2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3>Farm Reports</h3>
                    <p>Review information from your farm records with clear, practical reports.</p>
                </article>
                <article class="feature-card sm:col-span-2 lg:col-span-1">
                    <span class="feature-icon feature-icon-green">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M4 19h16M6 16V9m6 7V5m6 11v-4" stroke-linecap="round"/><path d="m5 7 6-3 6 5 3-2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3>Farm Expenses</h3>
                    <p>Keep operating costs recorded alongside the rest of your farm activity.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="about" class="section-pad scroll-mt-8 bg-cream-100">
        <div class="site-container grid items-center gap-12 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="about-art" aria-hidden="true">
                <div class="about-art-inner">
                    <svg viewBox="0 0 180 180" class="h-36 w-36 sm:h-44 sm:w-44" fill="none">
                        <path d="M90 29v113M44 74h92M59 52l31-24 31 24M59 74v55m62-55v55M42 142h96" stroke="#214a37" stroke-linecap="round" stroke-linejoin="round" stroke-width="5"/>
                        <path d="M68 145c0-13 10-23 22-23s22 10 22 23" stroke="#e9a9a2" stroke-linecap="round" stroke-width="5"/>
                        <circle cx="90" cy="90" r="10" fill="#e9a9a2"/>
                    </svg>
                </div>
                <span class="about-leaf about-leaf-one"></span>
                <span class="about-leaf about-leaf-two"></span>
            </div>
            <div class="max-w-xl">
                <p class="eyebrow">About BIIK FarmSuite</p>
                <h2 class="section-title mt-4">Built around the records that keep a farm moving.</h2>
                <p class="mt-5 leading-7 text-forest-800/80">
                    Farm work involves many connected details, from animal histories and breeding events to piglet records and everyday finances. BIIK FarmSuite brings those management needs into one focused digital workspace.
                </p>
                <p class="mt-4 leading-7 text-forest-800/80">
                    Our goal is to make farm information easier to organize and review, helping farmers spend less time searching through scattered records and more time tending to the work in front of them.
                </p>
                <a href="{{ route('login') }}" class="button button-primary mt-7">Get started <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </section>

    <section id="contact" class="section-pad scroll-mt-8 bg-white">
        <div class="site-container">
            <div class="contact-panel">
                <div class="max-w-xl">
                    <p class="eyebrow">Contact</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Let’s talk about your farm.</h2>
                    <p class="mt-4 leading-7 text-forest-800/80">For product questions or information about BIIK FarmSuite, use the contact details below.</p>
                </div>
                <div class="contact-details">
                    @if (filled(config('farmsuite.contact.email')))
                        <p><span>Email</span><a href="mailto:{{ config('farmsuite.contact.email') }}">{{ config('farmsuite.contact.email') }}</a></p>
                    @endif
                    @if (filled(config('farmsuite.contact.phone')))
                        <p><span>Phone</span><a href="tel:{{ config('farmsuite.contact.phone') }}">{{ config('farmsuite.contact.phone') }}</a></p>
                    @endif
                    @if (filled(config('farmsuite.contact.address')))
                        <p><span>Address</span><span>{{ config('farmsuite.contact.address') }}</span></p>
                    @endif
                    @unless (filled(config('farmsuite.contact.email')) || filled(config('farmsuite.contact.phone')) || filled(config('farmsuite.contact.address')))
                        <p class="text-sm leading-6 text-forest-800/70">Contact details have not been configured yet.</p>
                    @endunless
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-forest-950 text-white">
        <div class="site-container flex flex-col gap-6 py-8 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 font-semibold tracking-tight" aria-label="BIIK FarmSuite home">
                <svg viewBox="0 0 40 40" aria-hidden="true" class="h-9 w-9">
                    <rect width="40" height="40" rx="13" fill="#315a43"/>
                    <path d="M11 26.5 19.8 12l9.1 14.5H11Z" fill="#f6f4ec"/>
                    <path d="M20 12v14.5m-5.2 0c.9-3.3 3.1-5 5.2-5s4.3 1.7 5.2 5" fill="none" stroke="#dfe8d8" stroke-linecap="round" stroke-width="1.6"/>
                    <circle cx="20" cy="17" r="2.4" fill="#e9a9a2"/>
                </svg>
                <span>BIIK <span class="font-normal text-white/70">FarmSuite</span></span>
            </a>
            <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-white/75" aria-label="Footer navigation">
                <a class="footer-link" href="#home">Home</a>
                <a class="footer-link" href="#features">Features</a>
                <a class="footer-link" href="#about">About</a>
                <a class="footer-link" href="#contact">Contact</a>
                <a class="footer-link" href="{{ route('login') }}">Login</a>
            </nav>
            <p class="text-sm text-white/60">&copy; {{ now()->year }} BIIK FarmSuite</p>
        </div>
    </footer>
@endsection
