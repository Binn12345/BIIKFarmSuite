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
                <p class="eyebrow">Get started</p>
                <h1 class="mt-5 text-4xl font-semibold leading-tight tracking-tight text-forest-950 sm:text-5xl">Create your farm workspace account.</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-forest-800/75">Register to organize your farm records in BIIK FarmSuite.</p>
            </div>
        </section>

        <section class="login-card" aria-labelledby="register-heading">
            <div class="mb-8">
                <h2 id="register-heading" class="text-2xl font-semibold tracking-tight text-forest-950">Create an account</h2>
                <p class="mt-2 text-sm leading-6 text-forest-800/70">We’ll send a verification link to your email address.</p>
            </div>

            <form wire:submit="register" class="space-y-5" novalidate>
                @csrf
                <div>
                    <label for="name" class="form-label">Full name</label>
                    <input id="name" type="text" wire:model="name" autocomplete="name" required maxlength="255" @error('name') aria-invalid="true" @enderror class="form-input">
                    @error('name') <p class="form-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" wire:model="email" autocomplete="email" inputmode="email" required maxlength="255" @error('email') aria-invalid="true" @enderror class="form-input">
                    @error('email') <p class="form-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password" required class="form-input" aria-describedby="password-hint">
                    <p id="password-hint" class="mt-2 text-xs leading-5 text-forest-800/65">Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.</p>
                    @error('password') <p class="form-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required class="form-input">
                </div>
                <button type="submit" class="button button-primary w-full" wire:loading.attr="disabled" wire:target="register">
                    <span wire:loading.remove wire:target="register">Create account</span>
                    <span wire:loading wire:target="register">Creating account…</span>
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-forest-800/70">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-forest-800 underline decoration-forest-800/30 underline-offset-4 hover:text-forest-950">Log in</a>
            </p>
        </section>
    </div>
</div>
