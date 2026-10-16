@extends('layouts.app')

@section('content')
    <div class="login-page">
        <div class="site-container grid min-h-screen items-center py-12">
            <section class="login-card mx-auto w-full max-w-lg text-center" aria-labelledby="verify-email-heading">
                <span class="feature-icon feature-icon-green mx-auto" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6h16v12H4z" stroke-linejoin="round"/><path d="m5 7 7 6 7-6m-2 6 3 4m-12-4-3 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h1 id="verify-email-heading" class="mt-6 text-2xl font-semibold tracking-tight text-forest-950">{{ __('biik.password_ui.verify_email') }}</h1>
                <p class="mt-3 leading-7 text-forest-800/75">{{ __('biik.password_ui.verify_intro') }}</p>
                @if (session('status') === 'verification-link-sent')
                    <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ __('biik.password_ui.fresh_verification') }}</p>
                @endif
                <form method="POST" action="{{ route('verification.send') }}" class="mt-7">
                    @csrf
                    <button type="submit" class="button button-primary w-full">{{ __('biik.password_ui.resend_verification') }}</button>
                </form>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.password_ui.log_out') }}</button>
                </form>
            </section>
        </div>
    </div>
@endsection
