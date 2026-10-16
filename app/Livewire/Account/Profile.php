<?php

namespace App\Livewire\Account;

use App\Services\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
    }

    public function save(): void
    {
        $user = auth()->user();
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $emailChanged = $validated['email'] !== $user->email;
        $before = array_keys(array_filter([
            'name' => $validated['name'] !== $user->name,
            'email' => $emailChanged,
            'phone' => $validated['phone'] !== $user->phone,
            'address' => $validated['address'] !== $user->address,
        ]));
        $user->fill($validated);
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();

        app(AuditLogger::class)->record('profile.updated', $user, ['changed_fields' => $before]);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        session()->flash('status', $emailChanged
            ? 'Profile updated. Verify your new email address to continue.'
            : 'Profile updated.');

        if ($emailChanged) {
            $this->redirectRoute('verification.notice');
        }
    }

    public function render()
    {
        return view('livewire.account.profile')
            ->layout('layouts.app', ['title' => 'My Profile']);
    }
}
