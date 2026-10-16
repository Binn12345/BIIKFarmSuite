<?php

namespace App\Livewire\Account;

use Livewire\Component;

class Settings extends Component
{
    public string $background = 'soft-neutral';

    public string $accent = 'blue';

    public function mount(): void
    {
        $user = auth()->user();
        $background = $user->theme_background ?? session('theme_background', 'soft-neutral');
        $accent = $user->theme_accent ?? session('theme_accent', 'blue');
        $this->background = in_array($background, ['soft-neutral', 'clean-white', 'cool-gray'], true) ? $background : 'soft-neutral';
        $this->accent = in_array($accent, ['blue', 'teal', 'purple'], true) ? $accent : 'blue';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'background' => ['required', 'in:soft-neutral,clean-white,cool-gray'],
            'accent' => ['required', 'in:blue,teal,purple'],
        ]);

        auth()->user()->forceFill([
            'theme_background' => $validated['background'],
            'theme_accent' => $validated['accent'],
        ])->save();

        session()->put([
            'theme_background' => $validated['background'],
            'theme_accent' => $validated['accent'],
        ]);

        session()->flash('status', __('biik.theme_saved'));
        $this->dispatch('theme-updated', background: $validated['background'], accent: $validated['accent']);
    }

    public function render()
    {
        return view('livewire.account.settings')
            ->layout('layouts.app', ['title' => __('biik.settings')]);
    }
}
