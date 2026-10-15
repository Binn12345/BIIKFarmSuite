@extends('layouts.app')

@section('content')
    <div class="login-page">
        <div class="site-container grid min-h-screen items-center py-12">
            <section class="login-card mx-auto w-full max-w-lg" aria-labelledby="reset-password-heading">
                <a href="{{ route('home') }}" class="brand-mark mb-10" aria-label="BIIK FarmSuite home">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span>
                    <span class="leading-tight"><span class="block text-base font-extrabold">BIIK</span><span class="block text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-forest-800">FarmSuite</span></span>
                </a>
                <h1 id="reset-password-heading" class="text-2xl font-semibold tracking-tight text-forest-950">Choose a new password</h1>
                @if ($errors->any())
                    <p role="alert" class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>
                @endif
                <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required class="form-input">
                    </div>
                    <div>
                        <label for="password" class="form-label">New password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required class="form-input">
                        <p class="mt-2 text-xs leading-5 text-forest-800/65">Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.</p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="form-input">
                    </div>
                    <button type="submit" class="button button-primary w-full">Reset password</button>
                </form>
            </section>
        </div>
    </div>
@endsection
