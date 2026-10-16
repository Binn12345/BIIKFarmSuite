<section class="site-container py-10 sm:py-14">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">{{ __('biik.administration') }}</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ __('biik.user_management') }}</h1>
        </div>
        @can('users.create')
            <button type="button" wire:click="createUser" class="button button-primary">{{ __('biik.create_user') }}</button>
        @endcan
    </div>

    @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
    @if (session('invitation-status')) <p role="status" class="mt-3 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('invitation-status') }}</p> @endif
    @if (session('invitation-error')) <p role="alert" class="mt-3 rounded-xl bg-amber-50 p-3 text-sm text-amber-900">{{ session('invitation-error') }}</p> @endif
    @error('roleToAssign') <p role="alert" class="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p> @enderror

    <div class="mt-7 grid gap-4 rounded-2xl border border-forest-950/10 bg-white p-5 md:grid-cols-3">
        <div>
            <label for="user-search" class="form-label">{{ __('biik.search_users') }}</label>
            <input id="user-search" type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="{{ __('biik.search_name_email') }}">
        </div>
        <div>
            <label for="user-role-filter" class="form-label">{{ __('biik.filter_role') }}</label>
            <select id="user-role-filter" wire:model.live="roleFilter" class="form-input">
                <option value="">{{ __('biik.all_roles') }}</option>
                @foreach ($roleFilters as $role)<option value="{{ $role->name }}">{{ $role->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="user-status-filter" class="form-label">{{ __('biik.filter_status') }}</label>
            <select id="user-status-filter" wire:model.live="statusFilter" class="form-input">
                <option value="">{{ __('biik.all_statuses') }}</option>
                <option value="active">{{ __('biik.active') }}</option>
                <option value="inactive">{{ __('biik.inactive') }}</option>
            </select>
        </div>
    </div>

    <div class="mt-5 overflow-hidden rounded-2xl border border-forest-950/10 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[58rem] text-left text-sm">
                <thead class="bg-cream-100 text-xs uppercase tracking-wider text-forest-800">
                    <tr><th class="px-5 py-4">{{ __('biik.user') }}</th><th class="px-5 py-4">{{ __('biik.role') }}</th><th class="px-5 py-4">{{ __('biik.status') }}</th><th class="px-5 py-4">{{ __('biik.created') }}</th><th class="px-5 py-4">{{ __('biik.actions') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-forest-950/10">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($user->has_profile_picture)
                                        <img src="{{ route('users.profile-picture', $user->id) }}" alt="" class="h-11 w-11 rounded-full object-cover">
                                    @else
                                        <span class="grid h-11 w-11 place-items-center rounded-full bg-forest-900 text-sm font-bold text-white" aria-hidden="true">{{ collect(explode(' ', $user->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</span>
                                    @endif
                                    <span><span class="block font-semibold text-forest-950">{{ $user->name }}</span><span class="block text-forest-800/70">{{ $user->email }}</span></span>
                                </div>
                            </td>
                            <td class="px-5 py-4">{{ $user->getRoleNames()->implode(', ') ?: __('biik.no_roles') }}</td>
                            <td class="px-5 py-4"><span class="{{ $user->is_active ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }} rounded-full px-2.5 py-1 text-xs font-semibold">{{ $user->is_active ? __('biik.active') : __('biik.inactive') }}</span></td>
                            <td class="px-5 py-4">{{ $user->created_at?->translatedFormat('M j, Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" wire:click="viewUser({{ $user->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.view') }}</button>
                                    @can('users.update')
                                        @if (! $user->hasAnyRole(\App\Support\PermissionCatalog::privilegedRoleNames()))
                                            <button type="button" wire:click="editUser({{ $user->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.edit') }}</button>
                                            @if (! $user->email_verified_at)
                                                <button type="button" wire:click="resendInvitation({{ $user->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.resend_invitation') }}</button>
                                            @endif
                                        @endif
                                    @endcan
                                    @if (! $user->hasAnyRole(\App\Support\PermissionCatalog::privilegedRoleNames()) && $user->id !== auth()->id())
                                        @can('users.deactivate')
                                            <button type="button" wire:click="toggleActive({{ $user->id }})" wire:confirm="{{ $user->is_active ? __('biik.confirm_deactivate') : __('biik.confirm_activate') }}" class="font-semibold text-pig-500 underline underline-offset-4">{{ $user->is_active ? __('biik.deactivate') : __('biik.activate') }}</button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-forest-800/70">{{ __('biik.no_users') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-forest-950/10 px-5 py-4">{{ $users->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-forest-950/55 p-0 sm:items-center sm:p-5" role="presentation" wire:keydown.escape="closeEditor">
            <section x-data x-init="$nextTick(() => $refs.userName.focus())" @keydown.tab="
                const focusable = [...$el.querySelectorAll('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])')].filter(element => element.offsetParent !== null);
                const first = focusable[0], last = focusable[focusable.length - 1];
                if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); }
                else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }
            " role="dialog" aria-modal="true" aria-labelledby="user-modal-title" class="max-h-[95vh] w-full max-w-2xl overflow-y-auto rounded-t-3xl bg-white p-6 shadow-2xl sm:rounded-3xl">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="eyebrow">{{ __('biik.administration') }}</p><h2 id="user-modal-title" class="mt-2 text-2xl font-semibold text-forest-950">{{ $editingUserId ? __('biik.edit_user') : __('biik.create_user') }}</h2></div>
                    <button type="button" wire:click="closeEditor" class="rounded-lg p-2 text-2xl text-forest-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-forest-700" aria-label="{{ __('biik.close') }}">×</button>
                </div>
                <form wire:submit="saveUser" class="mt-6 space-y-4">
                    <div>
                        <label for="user-picture" class="form-label">{{ __('biik.profile_picture') }}</label>
                        <input id="user-picture" type="file" accept="image/jpeg,image/png,image/webp" wire:model="picture" class="form-input">
                        <p class="mt-1 text-xs text-forest-800/65">{{ __('biik.picture_limit') }}</p>
                        @error('picture') <p role="alert" class="form-error">{{ $message }}</p> @enderror
                        <div class="mt-3 flex items-center gap-3">
                            @if ($picture)
                                <img src="{{ $picture->temporaryUrl() }}" alt="{{ __('biik.picture_preview') }}" class="h-16 w-16 rounded-full object-cover">
                            @elseif ($editingUserId && $hasCurrentPicture)
                                <img src="{{ route('users.profile-picture', $editingUserId) }}" alt="{{ __('biik.current_profile_picture') }}" class="h-16 w-16 rounded-full object-cover">
                            @else
                                <span class="grid h-16 w-16 place-items-center rounded-full bg-forest-900 text-lg font-bold text-white">{{ collect(explode(' ', $name ?: 'BIIK User'))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</span>
                            @endif
                            @if ($editingUserId)
                                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" wire:model="removePicture" class="form-checkbox">{{ __('biik.remove_picture') }}</label>
                            @endif
                        </div>
                    </div>
                    <div><label for="user-name" class="form-label">{{ __('biik.full_name') }}</label><input x-ref="userName" id="user-name" wire:model="name" autocomplete="name" autofocus required class="form-input">@error('name') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
                    <div><label for="user-email" class="form-label">{{ __('biik.email_address') }}</label><input id="user-email" type="email" wire:model="email" autocomplete="email" required class="form-input">@error('email') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label for="user-phone" class="form-label">{{ __('biik.contact_number') }}</label><input id="user-phone" type="tel" wire:model="phone" autocomplete="tel" class="form-input">@error('phone') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
                        <div><label for="user-role" class="form-label">{{ __('biik.role') }}</label><select id="user-role" wire:model="roleName" required class="form-input">@foreach ($roles as $role)<option value="{{ $role->name }}">{{ $role->name }}</option>@endforeach</select>@error('roleName') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
                    </div>
                    <div><label for="user-address" class="form-label">{{ __('biik.address') }}</label><textarea id="user-address" wire:model="address" autocomplete="street-address" rows="2" class="form-input"></textarea>@error('address') <p role="alert" class="form-error">{{ $message }}</p> @enderror</div>
                    @if ($editingUserId)
                        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" wire:model="isActive" class="form-checkbox">{{ __('biik.account_active') }}</label>
                    @endif
                    <div class="flex flex-col-reverse gap-3 border-t border-forest-950/10 pt-5 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeEditor" class="button button-outline">{{ __('biik.cancel') }}</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveUser,picture" class="button button-primary"><span wire:loading.remove wire:target="saveUser">{{ __('biik.save') }}</span><span wire:loading wire:target="saveUser">{{ __('biik.saving') }}</span></button>
                    </div>
                </form>
            </section>
        </div>
    @endif
    @if ($selectedUser)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-forest-950/55 p-0 sm:items-center sm:p-5" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="user-details-title" class="max-h-[95vh] w-full max-w-xl overflow-y-auto rounded-t-3xl bg-white p-6 shadow-2xl sm:rounded-3xl">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="eyebrow">{{ __('biik.user') }}</p><h2 id="user-details-title" class="mt-2 text-2xl font-semibold text-forest-950">{{ $selectedUser->name }}</h2></div>
                    <button type="button" wire:click="closeEditor" class="rounded-lg p-2 text-2xl text-forest-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-forest-700" aria-label="{{ __('biik.close') }}">×</button>
                </div>
                @if ($selectedUser->has_profile_picture ?? false)
                    <img src="{{ route('users.profile-picture', $selectedUser->id) }}" alt="" class="mt-4 h-20 w-20 rounded-full object-cover">
                @endif
                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div><dt class="form-label">{{ __('biik.email_address') }}</dt><dd>{{ $selectedUser->email }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.role') }}</dt><dd>{{ $selectedUser->getRoleNames()->implode(', ') ?: __('biik.no_roles') }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.contact_number') }}</dt><dd>{{ $selectedUser->phone ?: '—' }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.status') }}</dt><dd>{{ $selectedUser->is_active ? __('biik.active') : __('biik.inactive') }}</dd></div>
                    <div class="sm:col-span-2"><dt class="form-label">{{ __('biik.address') }}</dt><dd>{{ $selectedUser->address ?: '—' }}</dd></div>
                    <div><dt class="form-label">{{ __('biik.created') }}</dt><dd>{{ $selectedUser->created_at?->translatedFormat('M j, Y') }}</dd></div>
                </dl>
            </section>
        </div>
    @endif
</section>
