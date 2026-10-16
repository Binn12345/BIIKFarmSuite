<section class="site-container py-10 sm:py-14">
    <p class="eyebrow">{{ __('biik.account') }}</p>
    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">{{ __('biik.my_profile') }}</h1>
    <p class="mt-2 text-forest-800/75">{{ __('biik.profile_description') }}</p>

    @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
    <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(17rem,1fr)]">
        <form wire:submit="save" class="grid gap-5 rounded-2xl border border-forest-950/10 bg-white p-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="profile-picture" class="form-label">{{ __('biik.profile_picture') }}</label>
                <input id="profile-picture" type="file" accept="image/jpeg,image/png,image/webp" wire:model="picture" class="form-input">
                <p class="mt-1 text-xs text-forest-800/65">{{ __('biik.picture_limit') }}</p>
                @error('picture') <p role="alert" class="form-error">{{ $message }}</p> @enderror
                <div class="mt-3 flex items-center gap-4">
                    @if ($picture)
                        <img src="{{ $picture->temporaryUrl() }}" alt="{{ __('biik.picture_preview') }}" class="h-20 w-20 rounded-full object-cover">
                    @elseif ($hasPicture && ! $removePicture)
                        <img src="{{ route('users.profile-picture', auth()->id()) }}" alt="{{ __('biik.current_profile_picture') }}" class="h-20 w-20 rounded-full object-cover">
                    @else
                        <span class="grid h-20 w-20 place-items-center rounded-full bg-forest-900 text-xl font-bold text-white" aria-label="{{ __('biik.initials_avatar') }}">{{ collect(explode(' ', auth()->user()->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</span>
                    @endif
                    @if ($hasPicture)
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-forest-800"><input type="checkbox" wire:model="removePicture" class="form-checkbox">{{ __('biik.remove_picture') }}</label>
                    @endif
                </div>
            </div>
            <div class="sm:col-span-2"><label for="profile-name" class="form-label">{{ __('biik.full_name') }}</label><input id="profile-name" wire:model="name" autocomplete="name" class="form-input">@error('name') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
            <div><label for="profile-email" class="form-label">{{ __('biik.email_address') }}</label><input id="profile-email" type="email" wire:model="email" autocomplete="email" class="form-input">@error('email') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
            <div><label for="profile-phone" class="form-label">{{ __('biik.contact_number') }}</label><input id="profile-phone" type="tel" wire:model="phone" autocomplete="tel" class="form-input">@error('phone') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
            <div class="sm:col-span-2"><label for="profile-address" class="form-label">{{ __('biik.address') }}</label><textarea id="profile-address" wire:model="address" autocomplete="street-address" rows="3" class="form-input"></textarea>@error('address') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
            <div class="sm:col-span-2">
                <label for="profile-locale" class="form-label">{{ __('biik.language') }}</label>
                <select id="profile-locale" wire:model="locale" class="form-input"><option value="en">English</option><option value="fil">Filipino</option></select>
                @error('locale') <p role="alert" class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2"><button type="submit" wire:loading.attr="disabled" wire:target="save,picture" class="button button-primary"><span wire:loading.remove wire:target="save">{{ __('biik.save_changes') }}</span><span wire:loading wire:target="save">{{ __('biik.saving') }}</span></button></div>
        </form>
        <aside class="h-fit rounded-2xl border border-forest-950/10 bg-white p-6">
            @if ($hasPicture)
                <img src="{{ route('users.profile-picture', auth()->id()) }}" alt="{{ __('biik.profile_picture_alt', ['name' => auth()->user()->name]) }}" class="h-24 w-24 rounded-full object-cover">
            @else
                <div class="grid h-24 w-24 place-items-center rounded-full bg-forest-900 text-2xl font-bold text-white" aria-label="{{ __('biik.initials_avatar') }}">{{ collect(explode(' ', auth()->user()->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</div>
            @endif
            <h2 class="mt-4 text-lg font-semibold text-forest-950">{{ auth()->user()->name }}</h2>
            <p class="text-sm text-forest-800/75">{{ auth()->user()->email }}</p>
            <dl class="mt-5 space-y-3 text-sm">
                <div><dt class="font-semibold text-forest-800">{{ __('biik.role') }}</dt><dd>{{ auth()->user()->getRoleNames()->map(fn ($role) => __('biik.roles.'.$role, [], app()->getLocale()) !== 'biik.roles.'.$role ? __('biik.roles.'.$role) : $role)->implode(', ') }}</dd></div>
                <div><dt class="font-semibold text-forest-800">{{ __('biik.account_status') }}</dt><dd>{{ auth()->user()->is_active ? __('biik.active') : __('biik.inactive') }}</dd></div>
                <div><dt class="font-semibold text-forest-800">{{ __('biik.email_status') }}</dt><dd>{{ auth()->user()->hasVerifiedEmail() ? __('biik.verified') : __('biik.unverified') }}</dd></div>
                <div><dt class="font-semibold text-forest-800">{{ __('biik.member_since') }}</dt><dd>{{ auth()->user()->created_at?->translatedFormat('M j, Y') }}</dd></div>
                <div><dt class="font-semibold text-forest-800">{{ __('biik.last_updated') }}</dt><dd>{{ auth()->user()->updated_at?->translatedFormat('M j, Y') }}</dd></div>
            </dl>
        </aside>
    </div>
</section>
