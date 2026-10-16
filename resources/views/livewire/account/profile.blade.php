<section class="site-container py-12 sm:py-16">
    <p class="eyebrow">Account</p>
    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">My Profile</h1>
    <p class="mt-2 text-forest-800/75">View and update your own personal details.</p>

    @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
    <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)]">
        <form wire:submit="save" class="grid gap-5 rounded-2xl border border-forest-950/10 bg-white p-6 sm:grid-cols-2">
            <div class="sm:col-span-2"><label for="profile-name" class="form-label">Full name</label><input id="profile-name" wire:model="name" autocomplete="name" class="form-input">@error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
            <div><label for="profile-email" class="form-label">Email address</label><input id="profile-email" type="email" wire:model="email" autocomplete="email" class="form-input">@error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
            <div><label for="profile-phone" class="form-label">Contact number</label><input id="profile-phone" type="tel" wire:model="phone" autocomplete="tel" class="form-input">@error('phone') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
            <div class="sm:col-span-2"><label for="profile-address" class="form-label">Address</label><textarea id="profile-address" wire:model="address" autocomplete="street-address" rows="3" class="form-input"></textarea>@error('address') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror</div>
            <div class="sm:col-span-2"><button type="submit" class="button button-primary">Save profile</button></div>
        </form>
        <aside class="h-fit rounded-2xl border border-forest-950/10 bg-white p-6">
            <div class="grid h-16 w-16 place-items-center rounded-full bg-forest-900 text-xl font-bold text-white" aria-label="Initials avatar">{{ collect(explode(' ', auth()->user()->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</div>
            <h2 class="mt-4 text-lg font-semibold text-forest-950">{{ auth()->user()->name }}</h2>
            <p class="text-sm text-forest-800/75">{{ auth()->user()->email }}</p>
            <dl class="mt-5 space-y-3 text-sm">
                <div><dt class="font-semibold text-forest-800">Role</dt><dd>{{ auth()->user()->hasRole('admin') ? 'Admin' : 'Basic User' }}</dd></div>
                <div><dt class="font-semibold text-forest-800">Account status</dt><dd>{{ auth()->user()->is_active ? 'Active' : 'Inactive' }}</dd></div>
                <div><dt class="font-semibold text-forest-800">Email status</dt><dd>{{ auth()->user()->hasVerifiedEmail() ? 'Verified' : 'Unverified' }}</dd></div>
                <div><dt class="font-semibold text-forest-800">Member since</dt><dd>{{ auth()->user()->created_at?->format('M j, Y') }}</dd></div>
                <div><dt class="font-semibold text-forest-800">Last updated</dt><dd>{{ auth()->user()->updated_at?->format('M j, Y') }}</dd></div>
            </dl>
        </aside>
    </div>
</section>
