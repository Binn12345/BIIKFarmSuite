<?php

namespace Tests\Feature;

use App\Livewire\Admin\AuditLogIndex;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use LogicException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_successful_and_failed_login_are_audited_without_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'audited@example.test',
            'password' => 'Correct-Horse-Password-42',
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Incorrect-Horse-Password-42')
            ->call('authenticate');

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Correct-Horse-Password-42')
            ->call('authenticate');

        $failed = AuditLog::query()->where('event', 'authentication.login.failed')->latest('id')->firstOrFail();
        $succeeded = AuditLog::query()->where('event', 'authentication.login.succeeded')->latest('id')->firstOrFail();

        $this->assertNull($failed->actor_id);
        $this->assertSame('web', $failed->metadata['guard']);
        $this->assertSame($user->id, $succeeded->actor_id);
        $this->assertSame('web', $succeeded->metadata['guard']);
        $this->assertStringNotContainsString('Correct-Horse-Password-42', json_encode($succeeded->toArray()));
        $this->assertStringNotContainsString('Incorrect-Horse-Password-42', json_encode($failed->toArray()));
        $this->assertStringNotContainsString('audited@example.test', json_encode($failed->toArray()));

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'authentication.logout',
            'actor_id' => $user->id,
        ]);
    }

    public function test_role_and_account_changes_are_audited(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('roleToAssign', 'basic-user')
            ->call('assignRole', $target->id)
            ->call('toggleActive', $target->id);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.roles.assigned',
            'subject_type' => User::class,
            'subject_id' => (string) $target->id,
            'actor_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.deactivated',
            'subject_type' => User::class,
            'subject_id' => (string) $target->id,
            'actor_id' => $admin->id,
        ]);
    }

    public function test_audit_log_page_is_restricted_to_administrators_with_audit_permission(): void
    {
        $basic = User::factory()->create();
        $basic->assignRole('basic-user');

        $this->actingAs($basic)
            ->get(route('admin.audit-logs'))
            ->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        AuditLog::create([
            'actor_id' => $admin->id,
            'event' => 'role.permissions.updated',
            'metadata' => ['role' => 'farm-staff'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs'))
            ->assertOk()
            ->assertSee('Audit trail');

        Livewire::actingAs($admin)
            ->test(AuditLogIndex::class)
            ->set('search', 'role.permissions.updated')
            ->assertSee('role.permissions.updated')
            ->set('eventFilter', 'role.permissions.updated')
            ->assertSee('farm-staff');
    }

    public function test_audit_log_entries_cannot_be_updated_or_deleted_through_the_model(): void
    {
        $log = app(AuditLogger::class)->record('security.test');

        $this->expectException(LogicException::class);
        $log->delete();
    }

    public function test_audit_log_entries_cannot_be_updated_through_the_model(): void
    {
        $log = app(AuditLogger::class)->record('security.test');

        $this->expectException(LogicException::class);
        $log->event = 'security.changed';
        $log->save();
    }

    public function test_registration_is_audited_without_password_metadata(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Audited Farmer')
            ->set('email', 'audited-registration@example.test')
            ->set('password', 'Strong-Piglet-Pass-42')
            ->set('password_confirmation', 'Strong-Piglet-Pass-42')
            ->call('register');

        $log = AuditLog::query()->where('event', 'user.registered')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.registered',
            'subject_type' => User::class,
        ]);
        $this->assertSame([], $log->metadata);
        $this->assertStringNotContainsString('Strong-Piglet-Pass-42', json_encode($log->toArray()));
    }

    public function test_audit_logger_does_not_copy_sensitive_request_input_into_metadata(): void
    {
        request()->merge([
            'password' => 'never-log-this-password',
            'token' => 'never-log-this-token',
            'session' => 'never-log-this-session',
        ]);

        $log = app(AuditLogger::class)->record('security.safe-metadata', metadata: [
            'action' => 'test',
            'nested' => [
                'password' => 'never-log-this-nested-password',
                'retained' => 'safe',
            ],
        ]);

        $this->assertSame([
            'action' => 'test',
            'nested' => ['retained' => 'safe'],
        ], $log->metadata);
        $this->assertStringNotContainsString('never-log-this', json_encode($log->toArray()));
    }
}
