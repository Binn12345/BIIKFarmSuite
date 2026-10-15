<?php

namespace App\Livewire\Admin;

use App\Models\User;
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
        $this->authorize('users.manage');

        $validated = validator(
            ['roleToAssign' => $this->roleToAssign],
            ['roleToAssign' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where(fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->where('name', '!=', 'admin')),
            ]],
        )->validate();

        $user = User::query()->findOrFail($userId);
        abort_if($user->hasRole('admin'), 403);

        $user->syncRoles($validated['roleToAssign']);
        $this->reset('roleToAssign');
        session()->flash('status', 'User role updated.');
    }

    public function revokeRole(int $userId, string $roleName): void
    {
        $this->authorize('users.manage');
        abort_if($roleName === 'admin', 403);

        $user = User::query()->findOrFail($userId);
        abort_if($user->hasRole('admin'), 403);

        $role = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->where('name', '!=', 'admin')
            ->firstOrFail();

        $user->removeRole($role);
        session()->flash('status', 'User role revoked.');
    }

    public function toggleActive(int $userId): void
    {
        $this->authorize('users.manage');

        $user = User::query()->findOrFail($userId);
        abort_if($user->id === auth()->id() || $user->hasRole('admin'), 403);

        $user->forceFill(['is_active' => ! $user->is_active])->save();
        session()->flash('status', $user->is_active ? 'Account activated.' : 'Account deactivated.');
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
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
            'roles' => Role::query()->where('name', '!=', 'admin')->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'User management']);
    }
}
