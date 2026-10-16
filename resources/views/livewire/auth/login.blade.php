<div class="login-page">
    <div class="site-container grid min-h-[calc(100vh-2rem)] items-center gap-12 py-10 lg:grid-cols-[1fr_0.85fr] lg:py-16">
        <section class="login-intro">
            <a href="{{ route('home') }}" class="brand-mark" aria-label="BIIK FarmSuite home">
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
            <div class="mt-14 max-w-xl">
                <p class="eyebrow">{{ __('biik.welcome_back') }}</p>
                <h1 class="mt-5 text-4xl font-semibold leading-tight tracking-tight text-forest-950 sm:text-5xl">{{ __('biik.workspace_waiting') }}</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-forest-800/75">{{ __('biik.sign_in_intro') }}</p>
            </div>
            <div class="login-illustration mt-12 hidden max-w-lg sm:block" aria-hidden="true">
                <svg viewBox="0 0 440 185" class="w-full" fill="none">
                    <path d="M0 151c74-31 116-4 181 8 79 15 150-34 259-8v34H0v-34Z" fill="#b6c6a1"/>
                    <path d="M30 158c4-31 21-47 51-48m-37 51c8-20 20-28 38-28m210 24c7-38 29-56 65-57m-50 59c9-22 23-33 44-34" stroke="#54714b" stroke-linecap="round" stroke-width="5"/>
                    <path d="m284 92 53-34 57 34v60H284V92Z" fill="#f5eee0"/>
                    <path d="M276 94h126l-64-45-62 45Z" fill="#214a37"/>
                    <path d="M326 81h21v20h-21z" fill="#e9a9a2"/>
                    <path d="M329 128h18v24h-18z" fill="#d5b77b"/>
                    <path d="M121 147c0-13 10-23 22-23h24c13 0 23 10 23 23v7h-69v-7Z" fill="#f1c1b8"/>
                    <ellipse cx="158" cy="151" rx="38" ry="22" fill="#e9a9a2"/>
                    <ellipse cx="190" cy="146" rx="17" ry="15" fill="#e9a9a2"/>
                    <path d="m179 133 2-14 12 10m-54 0-4-12 13 8" fill="#e9a9a2" stroke="#c98079" stroke-linejoin="round" stroke-width="2"/>
                    <ellipse cx="201" cy="150" rx="8" ry="5" fill="#cf817b"/>
                    <circle cx="186" cy="142" r="2" fill="#543d36"/>
                </svg>
            </div>
        </section>

        <section class="login-card" aria-labelledby="login-heading">
            <div class="mb-8">
                <h2 id="login-heading" class="text-2xl font-semibold tracking-tight text-forest-950">{{ __('biik.log_in') }}</h2>
                <p class="mt-2 text-sm leading-6 text-forest-800/70">{{ __('biik.login_details_prompt') }}</p>
            </div>

            <form wire:submit="authenticate" class="space-y-5" novalidate>
                @csrf
                <div>
                    <label for="email" class="form-label">{{ __('biik.email_address') }}</label>
                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        autocomplete="username"
                        inputmode="email"
                        required
                        autofocus
                        aria-describedby="@error('email') email-error @enderror"
                        @error('email') aria-invalid="true" @enderror
                        class="form-input"
                    >
                    @error('email')
                        <p id="email-error" class="form-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="form-label">{{ __('biik.password') }}</label>
                    <div class="relative">
                        <input
                            id="password"
                            type="{{ $showPassword ? 'text' : 'password' }}"
                            wire:model="password"
                            autocomplete="current-password"
                            required
                            aria-describedby="@error('password') password-error @enderror"
                            @error('password') aria-invalid="true" @enderror
                            class="form-input pr-20"
                        >
                        <button
                            type="button"
                            wire:click="togglePasswordVisibility"
                            aria-label="{{ $showPassword ? __('biik.hide_password') : __('biik.show_password') }}"
                            aria-pressed="{{ $showPassword ? 'true' : 'false' }}"
                            class="password-toggle"
                        >{{ $showPassword ? __('biik.hide') : __('biik.show') }}</button>
                    </div>
                    @error('password')
                        <p id="password-error" class="form-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label for="remember" class="inline-flex cursor-pointer items-center gap-2.5 text-sm text-forest-800">
                        <input id="remember" type="checkbox" wire:model="remember" class="form-checkbox">
                        {{ __('biik.remember_me') }}
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-forest-800 underline decoration-forest-800/30 underline-offset-4 hover:text-forest-950">{{ __('biik.forgot_password') }}</a>
                    @endif
                </div>

                <button type="submit" class="button button-primary w-full" wire:loading.attr="disabled" wire:target="authenticate">
                    <span wire:loading.remove wire:target="authenticate">{{ __('biik.log_in') }}</span>
                    <span wire:loading wire:target="authenticate">{{ __('biik.signing_in') }}</span>
                    <svg wire:loading.remove wire:target="authenticate" viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-forest-800/70">
                <a href="{{ route('home') }}" class="font-semibold text-forest-800 underline decoration-forest-800/30 underline-offset-4 hover:text-forest-950">{{ __('biik.return_to_farmsuite') }}</a>
            </p>
            <p class="mt-3 text-center text-sm text-forest-800/70">
                {{ __('biik.new_to_farmsuite') }}
                <a href="{{ route('register') }}" class="font-semibold text-forest-800 underline decoration-forest-800/30 underline-offset-4 hover:text-forest-950">{{ __('biik.create_account') }}</a>
            </p>
        </section>
    </div>
</div>
