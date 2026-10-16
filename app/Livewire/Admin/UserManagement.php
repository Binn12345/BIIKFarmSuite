<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\AuditLogger;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $roleToAssign = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function assignRole(int $userId): void
    {
        $this->authorize('users.update');

        $validated = validator(
            ['roleToAssign' => $this->roleToAssign],
            ['roleToAssign' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where(fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->whereNotIn('name', PermissionCatalog::privilegedRoleNames())),
            ]],
        )->validate();

        $user = User::query()->findOrFail($userId);
        abort_if($user->hasAnyRole(PermissionCatalog::privilegedRoleNames()), 403);

        $oldRoles = $user->getRoleNames()->all();
        $user->syncRoles($validated['roleToAssign']);
        app(AuditLogger::class)->record('user.roles.assigned', $user, [
            'previous_roles' => $oldRoles,
            'roles' => $user->getRoleNames()->all(),
        ]);
        $this->reset('roleToAssign');
        session()->flash('status', 'User role updated.');
    }

    public function revokeRole(int $userId, string $roleName): void
    {
        $this->authorize('users.update');
        abort_if(in_array($roleName, PermissionCatalog::privilegedRoleNames(), true), 403);

        $user = User::query()->findOrFail($userId);
        abort_if($user->hasAnyRole(PermissionCatalog::privilegedRoleNames()), 403);

        $role = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->whereNotIn('name', PermissionCatalog::privilegedRoleNames())
            ->firstOrFail();

        $user->removeRole($role);
        app(AuditLogger::class)->record('user.role.revoked', $user, [
            'role' => $role->name,
        ]);
        session()->flash('status', 'User role revoked.');
    }

    public function toggleActive(int $userId): void
    {
        $this->authorize('users.deactivate');

        $user = User::query()->findOrFail($userId);
        abort_if(
            $user->id === auth()->id() || $user->hasAnyRole(PermissionCatalog::privilegedRoleNames()),
            403,
        );

        $user->forceFill(['is_active' => ! $user->is_active])->save();
        app(AuditLogger::class)->record(
            $user->is_active ? 'user.activated' : 'user.deactivated',
            $user,
            ['is_active' => $user->is_active],
        );
        session()->flash('status', $user->is_active ? 'Account activated.' : 'Account deactivated.');
    }

    public function render()
    {
        $this->authorize('users.view');

        $users = User::query()
            ->with(['roles', 'permissions', 'roles.permissions'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.users', [
            'users' => $users,
            'roles' => Role::query()->whereNotIn('name', PermissionCatalog::privilegedRoleNames())->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'User management']);
    }
}
