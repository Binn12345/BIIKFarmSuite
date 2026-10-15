<div class="min-h-screen bg-cream-50">
    <header class="border-b border-forest-950/10 bg-white">
        <div class="site-container flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('admin.dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">User management</span></span></a>
            <a href="{{ route('admin.dashboard') }}" class="button button-outline !px-5 !py-2.5">Admin dashboard</a>
        </div>
    </header>
    <section class="site-container py-12 sm:py-16">
        <p class="eyebrow">Administrator portal</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">User management</h1>
        @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
        @error('roleToAssign') <p role="alert" class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p> @enderror
        <div class="mt-7 grid gap-4 rounded-2xl border border-forest-950/10 bg-white p-5 sm:grid-cols-[1fr_auto_auto] sm:items-end">
            <div>
                <label for="user-search" class="form-label">Search users</label>
                <input id="user-search" type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Search by name or email">
            </div>
            <div>
                <label for="role-to-assign" class="form-label">Role for selected account</label>
                <select id="role-to-assign" wire:model="roleToAssign" class="form-input">
                    <option value="">Choose role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <span class="text-sm text-forest-800/70">Administrator roles are only granted by CLI.</span>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-forest-950/10 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[48rem] text-left text-sm">
                    <thead class="bg-cream-100 text-xs uppercase tracking-wider text-forest-800">
                        <tr><th class="px-5 py-4">User</th><th class="px-5 py-4">Roles</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y divide-forest-950/10">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-5 py-4"><span class="block font-semibold text-forest-950">{{ $user->name }}</span><span class="mt-1 block text-forest-800/70">{{ $user->email }}</span></td>
                                <td class="px-5 py-4">
                                    @forelse ($user->roles as $role)
                                        <span class="mr-1 inline-flex items-center gap-2 rounded-full bg-cream-100 px-2.5 py-1 text-xs font-semibold text-forest-800">
                                            {{ $role->name }}
                                            @if ($role->name !== 'admin')
                                                <button type="button" wire:click="revokeRole({{ $user->id }}, '{{ $role->name }}')" class="font-bold text-pig-500" aria-label="Revoke {{ $role->name }} role from {{ $user->name }}">×</button>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-forest-800/60">No roles</span>
                                    @endforelse
                                    @if (! $user->hasRole('admin'))
                                        <button type="button" wire:click="assignRole({{ $user->id }})" class="ml-2 text-xs font-semibold text-forest-800 underline underline-offset-4">Assign selected role</button>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="{{ $user->is_active ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }} rounded-full px-2.5 py-1 text-xs font-semibold">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if (! $user->hasRole('admin') && $user->id !== auth()->id())
                                        <button type="button" wire:click="toggleActive({{ $user->id }})" class="font-semibold text-forest-800 underline underline-offset-4">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    @else
                                        <span class="text-xs text-forest-800/60">Protected account</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-forest-800/70">No matching users.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-forest-950/10 px-5 py-4">{{ $users->links() }}</div>
        </div>
    </section>
</div>
