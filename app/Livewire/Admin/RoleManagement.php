<?php

namespace App\Livewire\Admin;

use App\Services\AuditLogger;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagement extends Component
{
    use AuthorizesRequests;

    public ?int $editingRoleId = null;

    public string $name = '';

    public string $search = '';

    public ?int $selectedRoleId = null;

    /** @var list<string> */
    public array $permissions = [];

    public function createRole(): void
    {
        $this->authorize('roles.manage');
        $this->reset('editingRoleId', 'name', 'permissions');
    }

    public function editRole(int $roleId): void
    {
        $this->authorize('roles.manage');

        $role = Role::query()
            ->whereNotIn('name', PermissionCatalog::privilegedRoleNames())
            ->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions()->pluck('name')->all();
    }

    public function viewUsers(int $roleId): void
    {
        $this->authorize('roles.manage');

        $role = Role::query()->findOrFail($roleId);

        $this->selectedRoleId = $role->id;
    }

    public function selectModule(string $module): void
    {
        $this->authorize('permissions.manage');
        $catalogModule = PermissionCatalog::modules()[$module] ?? abort(404);
        $assignablePermissions = array_diff(
            array_values($catalogModule['permissions']),
            PermissionCatalog::protectedPermissions(),
        );
        $this->permissions = array_values(array_unique([
            ...$this->permissions,
            ...$assignablePermissions,
        ]));
    }

    public function clearModule(string $module): void
    {
        $this->authorize('permissions.manage');
        $catalogModule = PermissionCatalog::modules()[$module] ?? abort(404);
        $this->permissions = array_values(array_diff(
            $this->permissions,
            array_values($catalogModule['permissions']),
        ));
    }

    public function saveRole(): void
    {
        $this->authorize('roles.manage');
        $this->authorize('permissions.manage');

        $role = $this->editingRoleId
            ? Role::query()->whereNotIn('name', ['admin', 'super-admin'])->findOrFail($this->editingRoleId)
            : null;

        $this->name = mb_strtolower(trim($this->name));

        if ($role?->name === 'basic-user' && $this->name !== 'basic-user') {
            $this->addError('name', 'The basic-user role name cannot be changed.');

            return;
        }

        if (in_array($this->name, PermissionCatalog::privilegedRoleNames(), true) || ($this->name === 'basic-user' && ! $role)) {
            $this->addError('name', 'This role name is reserved.');

            return;
        }

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('roles', 'name')
                    ->where(fn ($query) => $query->where('guard_name', 'web'))
                    ->ignore($role?->id),
            ],
            'permissions' => ['array', 'max:'.count(PermissionCatalog::permissions())],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::permissions())],
        ]);

        if (array_intersect($validated['permissions'], PermissionCatalog::protectedPermissions()) !== []) {
            $this->addError('permissions', 'Protected administration permissions can only be granted to the administrator role.');

            return;
        }

        $creating = ! $role;
        $previousName = $role?->name;
        $role ??= Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $previousPermissions = $role->permissions()->pluck('name')->all();
        if ($role->name !== 'basic-user') {
            $role->name = $validated['name'];
            $role->save();
        }

        $role->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $validated['permissions'])
                ->get(),
        );
        app(AuditLogger::class)->record($creating ? 'role.created' : 'role.permissions.updated', $role, [
            'role_name_before' => $previousName,
            'role_name_after' => $role->name,
            'permissions_before' => $previousPermissions,
            'permissions_after' => $role->permissions()->pluck('name')->all(),
        ]);

        $this->editingRoleId = $role->id;
        $this->selectedRoleId = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions()->pluck('name')->all();
        session()->flash('status', __('biik.role_permissions_saved'));
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorize('roles.manage');

        $role = Role::query()
            ->whereNotIn('name', [...PermissionCatalog::privilegedRoleNames(), 'basic-user'])
            ->findOrFail($roleId);

        if ($role->users()->exists()) {
            $this->addError('role', 'Remove this role from all users before deleting it.');

            return;
        }

        app(AuditLogger::class)->record('role.deleted', $role, [
            'role' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->all(),
        ]);
        $role->delete();
        $this->reset('editingRoleId', 'name', 'permissions');
        session()->flash('status', __('biik.role_deleted'));
    }

    public function render()
    {
        $this->authorize('roles.manage');

        $roles = Role::query()
            ->withCount('users')
            ->when($this->search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderByRaw("CASE WHEN name IN ('admin', 'super-admin') THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $selectedRole = $this->selectedRoleId
            ? Role::query()
                ->with([
                    'users' => fn ($query) => $query->select('users.id', 'users.name', 'users.email'),
                    'users.roles.permissions',
                    'users.permissions',
                ])
                ->find($this->selectedRoleId)
            : null;

        return view('livewire.admin.roles', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'modules' => PermissionCatalog::modules(),
        ])->layout('layouts.app', ['title' => __('biik.admin_ui.roles')]);
    }
}
