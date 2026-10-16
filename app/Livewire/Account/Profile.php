<?php

namespace App\Livewire\Account;

use App\Services\AuditLogger;
use App\Support\ProfilePictureUpload;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public bool $removePicture = false;

    public bool $hasPicture = false;

    public $picture = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
        $this->hasPicture = $user->newQuery()->whereKey($user->id)->whereNotNull('profile_picture')->exists();
    }

    public function save(): void
    {
        $user = auth()->user();
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'removePicture' => ['boolean'],
        ]);
        $image = $this->picture ? ProfilePictureUpload::inspect($this->picture) : null;

        $emailChanged = $validated['email'] !== $user->email;
        $before = array_keys(array_filter([
            'name' => $validated['name'] !== $user->name,
            'email' => $emailChanged,
            'phone' => $validated['phone'] !== $user->phone,
            'address' => $validated['address'] !== $user->address,
        ]));
        $user->fill(array_intersect_key($validated, array_flip(['name', 'email', 'phone', 'address'])));
        if ($image) {
            $user->profile_picture = $image['bytes'];
            $user->profile_picture_mime_type = $image['mime'];
        } elseif ($validated['removePicture']) {
            $user->profile_picture = null;
            $user->profile_picture_mime_type = null;
        }
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();
        $this->hasPicture = $image !== null || (! $validated['removePicture'] && $this->hasPicture);
        $this->reset('picture', 'removePicture');
        app(AuditLogger::class)->record('profile.updated', $user, [
            'changed_fields' => [...$before, ...(($image || $validated['removePicture']) ? ['profile_picture'] : [])],
        ]);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        session()->flash('status', $emailChanged
            ? __('biik.profile_updated_verify')
            : __('biik.profile_updated'));

        if ($emailChanged) {
            $this->redirectRoute('verification.notice');
        }
    }

    public function render()
    {
        return view('livewire.account.profile')
            ->layout('layouts.app', ['title' => __('biik.my_profile')]);
    }
}
