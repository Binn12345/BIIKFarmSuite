@extends('layouts.app')

@section('content')
    <div class="login-page">
        <div class="site-container grid min-h-screen items-center py-12">
            <section class="login-card mx-auto w-full max-w-lg" aria-labelledby="reset-link-heading">
                <a href="{{ route('home') }}" class="brand-mark mb-10" aria-label="BIIK FarmSuite home">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span>
                    <span class="leading-tight"><span class="block text-base font-extrabold">BIIK</span><span class="block text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-forest-800">FarmSuite</span></span>
                </a>
                <h1 id="reset-link-heading" class="text-2xl font-semibold tracking-tight text-forest-950">Reset your password</h1>
                <p class="mt-2 text-sm leading-6 text-forest-800/70">Enter your account email and we’ll send a password reset link.</p>
                @if (session('status'))
                    <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p>
                @endif
                @if ($errors->any())
                    <p role="alert" class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first('email') }}</p>
                @endif
                <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus class="form-input">
                    </div>
                    <button type="submit" class="button button-primary w-full">Send reset link</button>
                </form>
                <p class="mt-7 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold text-forest-800 underline underline-offset-4">Return to log in</a></p>
            </section>
        </div>
    </div>
@endsection
