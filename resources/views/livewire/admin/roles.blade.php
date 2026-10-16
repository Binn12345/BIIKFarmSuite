<div class="min-h-screen bg-cream-50">
    <header class="border-b border-forest-950/10 bg-white">
        <div class="site-container flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('admin.dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">{{ __('biik.admin_ui.roles') }}</span></span></a>
            <a href="{{ route('admin.dashboard') }}" class="button button-outline !px-5 !py-2.5">{{ __('biik.admin_ui.dashboard') }}</a>
        </div>
    </header>
    <section class="site-container py-12 sm:py-16">
        <p class="eyebrow">{{ __('biik.admin_ui.portal') }}</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">{{ __('biik.admin_ui.roles') }}</h1>
        <p class="mt-3 max-w-2xl leading-7 text-forest-800/75">{{ __('biik.admin_ui.role_description') }}</p>
        @if (session('status')) <p role="status" class="mt-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p> @endif
        @error('role') <p role="alert" class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p> @enderror

        <div class="mt-8 grid items-start gap-6 lg:grid-cols-[0.85fr_1.15fr]">
            <section class="rounded-2xl border border-forest-950/10 bg-white p-5">
                <h2 class="font-semibold text-forest-950">{{ __('biik.admin_ui.available_roles') }}</h2>
                <label for="role-search" class="sr-only">{{ __('biik.admin_ui.search_roles') }}</label>
                <input id="role-search" type="search" wire:model.live.debounce.300ms="search" class="form-input mt-4" placeholder="{{ __('biik.admin_ui.search_roles') }}">
                <ul class="mt-4 divide-y divide-forest-950/10">
                    @foreach ($roles as $role)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <button type="button" wire:click="viewUsers({{ $role->id }})" class="text-left">
                                <span class="block font-semibold text-forest-950">{{ in_array($role->name, ['admin', 'super-admin'], true) ? 'Super Admin' : ucwords(str_replace('-', ' ', $role->name)) }}</span>
                                <span class="text-xs text-forest-800/65">{{ __('biik.admin_ui.assigned_view_users', ['count' => $role->users_count]) }}</span>
                            </button>
                            <div class="flex items-center gap-3">
                                @if (! in_array($role->name, ['admin', 'super-admin'], true))
                                    <button type="button" wire:click="editRole({{ $role->id }})" class="text-sm font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.admin_ui.edit') }}</button>
                                @endif
                                @if (! in_array($role->name, ['admin', 'super-admin', 'basic-user'], true))
                                    <button type="button" wire:click="deleteRole({{ $role->id }})" wire:confirm="{{ __('biik.admin_ui.delete_role_confirm') }}" class="text-sm font-semibold text-red-700 underline underline-offset-4">{{ __('biik.admin_ui.delete') }}</button>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                <button type="button" wire:click="createRole" class="button button-secondary mt-4 w-full">{{ __('biik.admin_ui.create_role') }}</button>
                @if ($selectedRole)
                    <div class="mt-6 border-t border-forest-950/10 pt-5">
                        <h3 class="font-semibold text-forest-950">{{ __('biik.admin_ui.users_with_role', ['role' => in_array($selectedRole->name, ['admin', 'super-admin'], true) ? __('biik.admin_ui.super_admin') : ucwords(str_replace('-', ' ', $selectedRole->name))]) }}</h3>
                        <ul class="mt-3 space-y-3">
                            @forelse ($selectedRole->users as $user)
                                <li class="rounded-xl bg-cream-50 p-3">
                                    <span class="block text-sm font-semibold text-forest-950">{{ $user->name }}</span>
                                    <span class="text-xs text-forest-800/70">{{ $user->email }}</span>
                                    <span class="mt-2 block text-xs leading-5 text-forest-800/75">
                                        {{ __('biik.admin_ui.effective_permissions') }}
                                        {{ $user->getAllPermissions()->pluck('name')->sort()->implode(', ') ?: __('biik.admin_ui.none') }}
                                    </span>
                                </li>
                            @empty
                                <li class="text-sm text-forest-800/70">{{ __('biik.admin_ui.no_users') }}</li>
                            @endforelse
                        </ul>
                    </div>
                @endif
            </section>

            <form wire:submit="saveRole" class="rounded-2xl border border-forest-950/10 bg-white p-5 sm:p-7">
                <h2 class="text-lg font-semibold text-forest-950">{{ $editingRoleId ? __('biik.admin_ui.edit_role') : __('biik.admin_ui.create_role') }}</h2>
                <div class="mt-5">
                    <label for="role-name" class="form-label">{{ __('biik.admin_ui.role_name') }}</label>
                    <input id="role-name" type="text" wire:model="name" required maxlength="100" @disabled($editingRoleId && $name === 'basic-user') class="form-input">
                    @error('name') <p class="form-error" role="alert">{{ $message }}</p> @enderror
                    @if ($editingRoleId && $name === 'basic-user') <p class="mt-2 text-xs text-forest-800/65">{{ __('biik.admin_ui.basic_role_protected') }}</p> @endif
                </div>
                <fieldset class="mt-6">
                    <legend class="form-label">{{ __('biik.admin_ui.module_permissions') }}</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($modules as $slug => $module)
                            <fieldset class="rounded-xl border border-forest-950/10 p-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <legend class="font-semibold text-forest-950">{{ in_array($slug, ['users', 'roles', 'audit-logs'], true) ? __('biik.module_names.admin.'.$slug) : __('biik.module_names.modules.'.$slug) }}</legend>
                                    <div class="flex gap-3 text-xs">
                                        <button type="button" wire:click="selectModule('{{ $slug }}')" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.admin_ui.select_all') }}</button>
                                        <button type="button" wire:click="clearModule('{{ $slug }}')" class="font-semibold text-forest-800 underline underline-offset-4">{{ __('biik.admin_ui.deselect_all') }}</button>
                                    </div>
                                </div>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    @foreach ($module['permissions'] as $action => $permission)
                                        <label for="permission-{{ $slug }}-{{ $action }}" class="flex cursor-pointer items-center gap-2 text-sm text-forest-800">
                                            <input
                                                id="permission-{{ $slug }}-{{ $action }}"
                                                type="checkbox"
                                                value="{{ $permission }}"
                                                wire:model="permissions"
                                                @disabled(in_array($permission, \App\Support\PermissionCatalog::protectedPermissions(), true))
                                                class="form-checkbox"
                                            >
                                            <span>{{ __('biik.permission_actions.'.$action) }} <code class="text-xs text-forest-800/60">{{ $permission }}</code>@if (in_array($permission, \App\Support\PermissionCatalog::protectedPermissions(), true)) <span class="text-xs text-pig-500">{{ __('biik.admin_ui.admin_only') }}</span> @endif</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                        @error('permissions') <p class="form-error" role="alert">{{ $message }}</p> @enderror
                        @error('permissions.*') <p class="form-error" role="alert">{{ $message }}</p> @enderror
                    </fieldset>
                    <button type="submit" wire:confirm="{{ __('biik.admin_ui.save_confirm') }}" class="button button-primary mt-6 w-full">{{ __('biik.admin_ui.save_permissions') }}</button>
            </form>
        </div>
    </section>
</div>
