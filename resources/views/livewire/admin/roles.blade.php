<div class="min-h-screen bg-cream-50">
    <header class="border-b border-forest-950/10 bg-white">
        <div class="site-container flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('admin.dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">Roles and permissions</span></span></a>
            <a href="{{ route('admin.dashboard') }}" class="button button-outline !px-5 !py-2.5">Admin dashboard</a>
        </div>
    </header>
    <section class="site-container py-12 sm:py-16">
        <p class="eyebrow">Administrator portal</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Roles and permissions</h1>
        <p class="mt-3 max-w-2xl leading-7 text-forest-800/75">Grant only the modules each role needs. Administrator role permissions are protected and cannot be changed here.</p>
        @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
        @error('role') <p role="alert" class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p> @enderror

        <div class="mt-8 grid items-start gap-6 lg:grid-cols-[0.85fr_1.15fr]">
            <section class="rounded-2xl border border-forest-950/10 bg-white p-5">
                <h2 class="font-semibold text-forest-950">Available roles</h2>
                <ul class="mt-4 divide-y divide-forest-950/10">
                    @foreach ($roles as $role)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <div><span class="block font-semibold text-forest-950">{{ $role->name }}</span><span class="text-xs text-forest-800/65">{{ $role->users()->count() }} assigned</span></div>
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="editRole({{ $role->id }})" class="text-sm font-semibold text-forest-800 underline underline-offset-4">Edit</button>
                                @if ($role->name !== 'basic-user')
                                    <button type="button" wire:click="deleteRole({{ $role->id }})" class="text-sm font-semibold text-red-700 underline underline-offset-4">Delete</button>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                <button type="button" wire:click="createRole" class="button button-secondary mt-4 w-full">Create role</button>
            </section>

            <form wire:submit="saveRole" class="rounded-2xl border border-forest-950/10 bg-white p-5 sm:p-7">
                <h2 class="text-lg font-semibold text-forest-950">{{ $editingRoleId ? 'Edit role' : 'Create role' }}</h2>
                <div class="mt-5">
                    <label for="role-name" class="form-label">Role name</label>
                    <input id="role-name" type="text" wire:model="name" required maxlength="100" @disabled($editingRoleId && $name === 'basic-user') class="form-input">
                    @error('name') <p class="form-error" role="alert">{{ $message }}</p> @enderror
                    @if ($editingRoleId && $name === 'basic-user') <p class="mt-2 text-xs text-forest-800/65">The system basic-user role name is protected.</p> @endif
                </div>
                <fieldset class="mt-6">
                    <legend class="form-label">Farm module permissions</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($modules as $slug => $module)
                            <label for="permission-{{ $slug }}" class="flex cursor-pointer items-center gap-3 rounded-xl border border-forest-950/10 p-3 text-sm text-forest-800">
                                <input id="permission-{{ $slug }}" type="checkbox" value="{{ $module['permission'] }}" wire:model="permissions" class="form-checkbox">
                                {{ $module['label'] }}
                            </label>
                        @endforeach
                    </div>
                    @error('permissions.*') <p class="form-error" role="alert">{{ $message }}</p> @enderror
                </fieldset>
                <button type="submit" class="button button-primary mt-6 w-full">Save role permissions</button>
            </form>
        </div>
    </section>
</div>
