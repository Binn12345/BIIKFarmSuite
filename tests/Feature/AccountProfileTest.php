<?php

namespace Tests\Feature;

use App\Livewire\Account\Profile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Livewire\Livewire;
use Tests\TestCase;

class AccountProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_profile_shows_only_the_authenticated_users_personal_information(): void
    {
        $user = User::factory()->create(['name' => 'Farmer User', 'email' => 'farmer@example.test']);
        $user->assignRole('basic-user');

        $this->actingAs($user)
            ->get(route('account.profile'))
            ->assertOk()
            ->assertSee('Farmer User')
            ->assertSee('farmer@example.test')
            ->assertSee('Basic User')
            ->assertDontSee('Password hash');

        $this->get(route('account.profile'))->assertOk();
    }

    public function test_profile_updates_permitted_fields_but_not_privileges_and_keeps_existing_email(): void
    {
        $user = User::factory()->create(['email' => 'farmer@example.test']);
        $user->assignRole('basic-user');

        Livewire::actingAs($user)->test(Profile::class)
            ->set('name', 'Updated Farmer')
            ->set('email', 'farmer@example.test')
            ->set('phone', '+1 555 0100')
            ->set('address', 'Farm road')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Profile updated.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Farmer',
            'email' => 'farmer@example.test',
            'phone' => '+1 555 0100',
            'address' => 'Farm road',
        ]);
        $this->assertTrue($user->fresh()->hasRole('basic-user'));
        $this->assertFalse($user->fresh()->hasRole('admin'));
        $this->assertDatabaseHas('audit_logs', ['event' => 'profile.updated', 'actor_id' => $user->id]);
    }

    public function test_profile_requires_unique_email_and_requires_reverification_after_email_change(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'farmer@example.test', 'email_verified_at' => now()]);
        $user->assignRole('basic-user');
        User::factory()->create(['email' => 'other@example.test']);

        Livewire::actingAs($user)->test(Profile::class)
            ->set('name', 'Farmer')
            ->set('email', 'other@example.test')
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);

        Livewire::actingAs($user)->test(Profile::class)
            ->set('name', 'Farmer')
            ->set('email', 'new@example.test')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('verification.notice'));

        $this->assertNull($user->fresh()->email_verified_at);
        Notification::assertSentTo($user->fresh(), VerifyEmail::class);
    }
}
