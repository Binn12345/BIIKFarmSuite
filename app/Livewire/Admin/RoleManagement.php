<?php

namespace App\Livewire\Admin;

use App\Support\FarmModules;
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
            ->where('name', '!=', 'admin')
            ->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions()->pluck('name')->all();
    }

    public function saveRole(): void
    {
        $this->authorize('roles.manage');

        $role = $this->editingRoleId
            ? Role::query()->where('name', '!=', 'admin')->findOrFail($this->editingRoleId)
            : null;

        if ($role?->name === 'basic-user' && $this->name !== 'basic-user') {
            $this->addError('name', 'The basic-user role name cannot be changed.');

            return;
        }

        if ($this->name === 'admin' || ($this->name === 'basic-user' && ! $role)) {
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
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(FarmModules::permissions())],
        ]);

        $role ??= Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
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

        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions()->pluck('name')->all();
        session()->flash('status', 'Role permissions saved.');
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorize('roles.manage');

        $role = Role::query()
            ->whereNotIn('name', ['admin', 'basic-user'])
            ->findOrFail($roleId);

        if ($role->users()->exists()) {
            $this->addError('role', 'Remove this role from all users before deleting it.');

            return;
        }

        $role->delete();
        $this->reset('editingRoleId', 'name', 'permissions');
        session()->flash('status', 'Role deleted.');
    }

    public function render()
    {
        return view('livewire.admin.roles', [
            'roles' => Role::query()->with('permissions')->where('name', '!=', 'admin')->orderBy('name')->get(),
            'modules' => FarmModules::all(),
        ])->layout('layouts.app', ['title' => 'Role and permission management']);
    }
}
