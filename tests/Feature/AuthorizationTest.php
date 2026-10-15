<?php

namespace Tests\Feature;

use App\Livewire\Admin\RoleManagement;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Login;
use App\Models\User;
use App\Support\FarmModules;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_are_redirected_from_the_admin_portal(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_only_administrators_can_open_admin_portal_routes(): void
    {
        $basicUser = User::factory()->create();
        $basicUser->assignRole('basic-user');

        $this->actingAs($basicUser)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->assertTrue($admin->fresh()->hasRole('admin'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Administrator portal');
    }

    public function test_administrators_are_redirected_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_basic_users_cannot_access_ungranted_modules_but_can_access_granted_modules(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');

        $this->actingAs($user)
            ->get(route('modules.animals'))
            ->assertForbidden();

        Role::findByName('basic-user')->givePermissionTo('animals.view');

        $this->actingAs($user)
            ->get(route('modules.animals'))
            ->assertOk()
            ->assertSee('Animal Management');
    }

    public function test_public_registration_cannot_assign_administrator_roles(): void
    {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'New Farmer')
            ->set('email', 'new-farmer@example.test')
            ->set('password', 'Strong-Piglet-Pass-42')
            ->set('password_confirmation', 'Strong-Piglet-Pass-42')
            ->call('register');

        $user = User::where('email', 'new-farmer@example.test')->firstOrFail();

        $this->assertSame(['basic-user'], $user->getRoleNames()->all());
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasPermissionTo('dashboard.view'));
        $this->assertFalse($user->hasPermissionTo('animals.view'));
    }

    public function test_administrators_cannot_grant_the_admin_role_through_user_management(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('roleToAssign', 'admin')
            ->call('assignRole', $target->id)
            ->assertHasErrors('roleToAssign');

        $this->assertFalse($target->fresh()->hasRole('admin'));
    }

    public function test_user_management_can_assign_and_revoke_only_non_admin_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('roleToAssign', 'basic-user')
            ->call('assignRole', $target->id)
            ->assertHasNoErrors();

        $this->assertTrue($target->fresh()->hasRole('basic-user'));

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('revokeRole', $target->id, 'basic-user')
            ->assertHasNoErrors();

        $this->assertFalse($target->fresh()->hasRole('basic-user'));
    }

    public function test_role_management_rejects_non_module_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(RoleManagement::class)
            ->set('name', 'farm-manager')
            ->set('permissions', ['users.manage', 'roles.manage'])
            ->call('saveRole')
            ->assertHasErrors(['permissions.0', 'permissions.1']);

        $this->assertDatabaseMissing('roles', ['name' => 'farm-manager']);
    }

    public function test_role_management_can_assign_module_permissions_to_a_custom_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $permissions = FarmModules::permissions();

        Livewire::actingAs($admin)
            ->test(RoleManagement::class)
            ->set('name', 'farm-manager')
            ->set('permissions', ['animals.view', 'reports.view'])
            ->call('saveRole')
            ->assertHasNoErrors();

        $role = Role::findByName('farm-manager');
        $this->assertSame(['animals.view', 'reports.view'], $role->permissions()->orderBy('name')->pluck('name')->all());
        $this->assertContains('animals.view', $permissions);
    }

    public function test_basic_users_cannot_invoke_admin_livewire_actions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');

        Livewire::actingAs($user)
            ->test(UserManagement::class)
            ->call('toggleActive', $user->id)
            ->assertForbidden();
    }

    public function test_administrators_cannot_deactivate_themselves_or_another_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('toggleActive', $admin->id)
            ->assertForbidden();

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('toggleActive', $otherAdmin->id)
            ->assertForbidden();

        $this->assertTrue($admin->fresh()->is_active);
        $this->assertTrue($otherAdmin->fresh()->is_active);
    }

    public function test_administrators_can_deactivate_a_basic_account_and_deactivated_users_are_blocked(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $user = User::factory()->create();
        $user->assignRole('basic-user');

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('toggleActive', $user->id)
            ->assertHasNoErrors();

        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($user->fresh())->get(route('dashboard'))->assertForbidden();
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.test',
            'password' => 'correct-horse-battery',
            'is_active' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'inactive@example.test')
            ->set('password', 'correct-horse-battery')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_provisioning_requires_a_verified_account_and_confirmation(): void
    {
        $unverified = User::factory()->unverified()->create([
            'email' => 'unverified@example.test',
        ]);

        $this->artisan('farmsuite:provision-admin', ['email' => $unverified->email])
            ->expectsOutput('Verify the account email before provisioning administrator access.')
            ->assertExitCode(1);

        $verified = User::factory()->create(['email' => 'verified@example.test']);

        $this->artisan('farmsuite:provision-admin', ['email' => $verified->email])
            ->expectsConfirmation("Grant administrator access to {$verified->email}?", 'no')
            ->assertExitCode(0);

        $this->assertFalse($verified->fresh()->hasRole('admin'));

        $this->artisan('farmsuite:provision-admin', ['email' => $verified->email])
            ->expectsConfirmation("Grant administrator access to {$verified->email}?", 'yes')
            ->assertExitCode(0);

        $this->assertTrue($verified->fresh()->hasRole('admin'));
    }

    public function test_admin_provisioning_refuses_inactive_accounts(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive-admin@example.test',
            'is_active' => false,
        ]);

        $this->artisan('farmsuite:provision-admin', ['email' => $user->email])
            ->expectsOutput('Activate the account before provisioning administrator access.')
            ->assertExitCode(1);

        $this->assertFalse($user->fresh()->hasRole('admin'));
    }
}
