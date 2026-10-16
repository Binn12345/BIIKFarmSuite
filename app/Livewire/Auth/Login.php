<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public bool $showPassword = false;

    public function authenticate(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->reset('password');
            $this->addError('email', trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]));

            return;
        }

        if (! Auth::attempt([...$credentials, 'is_active' => true], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->reset('password');
            $this->addError('email', trans('auth.failed'));

            return;
        }

        RateLimiter::clear($key);
        $this->reset('password');
        session()->regenerate();

        $this->redirectRoute('dashboard');
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = ! $this->showPassword;
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.app', ['title' => __('biik.log_in')]);
    }
}
