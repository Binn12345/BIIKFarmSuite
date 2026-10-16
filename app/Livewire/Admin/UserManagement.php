<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Notifications\UserInvitation;
use App\Services\AuditLogger;
use App\Support\PermissionCatalog;
use App\Support\ProfilePictureUpload;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Throwable;

class UserManagement extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $roleToAssign = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $editingUserId = null;

    public ?int $viewingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $roleName = '';

    public bool $isActive = true;

    public bool $removePicture = false;

    public bool $hasCurrentPicture = false;

    public $picture = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function createUser(): void
    {
        $this->authorize('users.create');
        $this->resetEditor();
        $this->roleName = $this->roles()->first()?->name ?? '';
        $this->showModal = true;
    }

    public function viewUser(int $userId): void
    {
        $this->authorize('users.view');
        $this->viewingUserId = User::query()->findOrFail($userId)->id;
        $this->showModal = false;
    }

    public function editUser(int $userId): void
    {
        $this->authorize('users.update');
        $user = User::query()->with('roles')->findOrFail($userId);
        abort_if($user->hasAnyRole(PermissionCatalog::privilegedRoleNames()), 403);

        $this->resetEditor();
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
        $this->roleName = $user->roles->first()?->name ?? '';
        $this->isActive = $user->is_active;
        $this->hasCurrentPicture = User::query()->whereKey($user->id)->whereNotNull('profile_picture')->exists();
        $this->showModal = true;
    }

    public function closeEditor(): void
    {
        $this->authorize('users.view');
        $this->resetEditor();
    }

    public function saveUser(): void
    {
        $creating = $this->editingUserId === null;
        $this->authorize($creating ? 'users.create' : 'users.update');

        $user = $creating ? null : User::query()->findOrFail($this->editingUserId);
        abort_if($user?->hasAnyRole(PermissionCatalog::privilegedRoleNames()), 403);
        abort_if($user && $user->id === auth()->id() && $this->roleName !== $user->getRoleNames()->first(), 403);
        if ($user && $user->is_active !== $this->isActive) {
            $this->authorize('users.deactivate');
            abort_if($user->id === auth()->id() || $user->is_active && $user->hasRole('admin'), 403);
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'lowercase', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'roleName' => [
                'required', 'string',
                Rule::exists('roles', 'name')->where(fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->whereNotIn('name', PermissionCatalog::privilegedRoleNames())),
            ],
            'isActive' => ['boolean'],
            'picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'removePicture' => ['boolean'],
        ]);
        $image = $this->picture ? ProfilePictureUpload::inspect($this->picture) : null;
        $oldRoles = $user?->getRoleNames()->all() ?? [];

        DB::transaction(function () use ($creating, $user, $validated, $image): void {
            $account = $user ?? new User;
            $account->name = $validated['name'];
            $account->email = $validated['email'];
            $account->phone = $validated['phone'] ?? null;
            $account->address = $validated['address'] ?? null;
            $account->is_active = $validated['isActive'];
            if ($creating) {
                $account->password = Str::random(64);
                $account->email_verified_at = null;
            }
            if ($image) {
                $account->profile_picture = $image['bytes'];
                $account->profile_picture_mime_type = $image['mime'];
            } elseif ($validated['removePicture']) {
                $account->profile_picture = null;
                $account->profile_picture_mime_type = null;
            }
            $account->save();
            $account->syncRoles($validated['roleName']);
        });

        $account = $user?->fresh() ?? User::query()->where('email', $validated['email'])->firstOrFail();
        app(AuditLogger::class)->record($creating ? 'user.created' : 'user.updated', $account, [
            'role' => $validated['roleName'],
            'previous_roles' => $oldRoles,
            'is_active' => $account->is_active,
            'picture_changed' => (bool) ($image || $validated['removePicture']),
        ]);

        if ($creating) {
            $this->sendInvitation($account);
        }

        $this->resetEditor();
        session()->flash('status', $creating ? __('biik.user_created') : __('biik.user_updated'));
    }

    public function resendInvitation(int $userId): void
    {
        $this->authorize('users.update');
        $user = User::query()->findOrFail($userId);
        abort_if($user->hasVerifiedEmail(), 409, 'This account has already verified its email.');

        $this->sendInvitation($user);
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
        session()->flash('status', __('biik.user_role_updated'));
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
        session()->flash('status', __('biik.user_role_revoked'));
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
        session()->flash('status', $user->is_active ? __('biik.account_activated') : __('biik.account_deactivated'));
    }

    public function render()
    {
        $this->authorize('users.view');

        $users = User::query()
            ->select(['id', 'name', 'email', 'phone', 'address', 'is_active', 'email_verified_at', 'created_at'])
            ->selectRaw('CASE WHEN profile_picture IS NULL THEN 0 ELSE 1 END AS has_profile_picture')
            ->with(['roles', 'permissions'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter !== '', fn ($query) => $query->whereHas('roles', fn ($roles) => $roles->where('name', $this->roleFilter)))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('is_active', $this->statusFilter === 'active'))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.users', [
            'users' => $users,
            'roles' => $this->roles(),
            'roleFilters' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(),
            'selectedUser' => $this->viewingUserId
                ? User::query()->select([
                    'id', 'name', 'email', 'phone', 'address', 'is_active', 'created_at',
                ])->selectRaw('CASE WHEN profile_picture IS NULL THEN 0 ELSE 1 END AS has_profile_picture')
                    ->with('roles')
                    ->find($this->viewingUserId)
                : null,
        ])->layout('layouts.app', ['title' => __('biik.user_management')]);
    }

    private function roles()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', PermissionCatalog::privilegedRoleNames())
            ->orderBy('name')
            ->get();
    }

    private function resetEditor(): void
    {
        $this->reset([
            'editingUserId', 'viewingUserId', 'name', 'email', 'phone', 'address', 'roleName',
            'picture', 'removePicture',
            'hasCurrentPicture',
        ]);
        $this->isActive = true;
        $this->showModal = false;
        $this->resetValidation();
    }

    private function sendInvitation(User $user): void
    {
        try {
            $token = Password::createToken($user);
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
            $invitation = new UserInvitation(
                $resetUrl,
                $user->getRoleNames()->first() ?? 'Farm user',
            );
            $invitation->locale($user->locale ?? 'en');
            Notification::send($user, $invitation);
            session()->flash('invitation-status', __('biik.invitation_sent', ['email' => $user->email]));
        } catch (Throwable $exception) {
            Log::error('FarmSuite user invitation could not be sent.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);
            session()->flash('invitation-error', __('biik.invitation_failed'));
        }
    }
}
