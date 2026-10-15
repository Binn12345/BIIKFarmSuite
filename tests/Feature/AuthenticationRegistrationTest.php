<?php

namespace Tests\Feature;

use App\Livewire\Auth\Register;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_registration_page_is_public_and_linked_from_the_landing_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('register'));

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create an account')
            ->assertSee('Confirm password');
    }

    public function test_registration_creates_a_hashed_unverified_user_and_sends_verification_notification(): void
    {
        Notification::fake();

        Livewire::test(Register::class)
            ->set('name', 'Farm Owner')
            ->set('email', 'owner@example.test')
            ->set('password', 'Strong-Piglet-Pass-42')
            ->set('password_confirmation', 'Strong-Piglet-Pass-42')
            ->call('register')
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'owner@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertTrue($user->hasRole('basic-user'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue(Hash::check('Strong-Piglet-Pass-42', $user->password));
        $this->assertNotSame('Strong-Piglet-Pass-42', $user->password);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_rejects_weak_or_unconfirmed_passwords(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Farm Owner')
            ->set('email', 'owner@example.test')
            ->set('password', 'weak')
            ->set('password_confirmation', 'different')
            ->call('register')
            ->assertHasErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_valid_signed_link_verifies_the_authenticated_user(): void
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_an_unsigned_or_expired_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.verify', [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]))
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_password_reset_link_is_sent_and_valid_token_changes_the_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'owner@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::createToken($user);
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'New-Strong-Pig-Pass-45',
            'password_confirmation' => 'New-Strong-Pig-Pass-45',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('New-Strong-Pig-Pass-45', $user->fresh()->password));
        $this->assertNotSame('New-Strong-Pig-Pass-45', $user->fresh()->password);
    }

    public function test_password_reset_rejects_an_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.test']);

        $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'New-Strong-Pig-Pass-45',
            'password_confirmation' => 'New-Strong-Pig-Pass-45',
        ])->assertSessionHasErrors('email');
    }
}
