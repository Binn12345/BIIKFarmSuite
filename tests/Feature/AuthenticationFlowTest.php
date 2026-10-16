<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_the_homepage_is_public_and_displays_the_public_sections(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Smarter Pig Farm Management Starts Here.')
            ->assertSee('Animal Management')
            ->assertSee('Breeding and Farrowing')
            ->assertSee('Piglet Records')
            ->assertSee('Farm Sales')
            ->assertSee('Farm Expenses')
            ->assertSee('Farm Reports')
            ->assertSee('Contact details have not been configured yet.');
    }

    public function test_guests_can_open_the_login_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in')
            ->assertSee('Remember me')
            ->assertSee('Forgot password?')
            ->assertSee('Show password');
    }

    public function test_guests_are_redirected_from_the_dashboard_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_dashboard_and_are_redirected_from_login(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No modules are available yet')
            ->assertSee('Basic User')
            ->assertSee('Profile')
            ->assertSee('Sign out');

        $this->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_a_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'farmer@example.test',
            'password' => 'correct-horse-battery',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'farmer@example.test')
            ->set('password', 'correct-horse-battery')
            ->set('remember', true)
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'farmer@example.test',
            'password' => 'correct-horse-battery',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'farmer@example.test')
            ->set('password', 'incorrect-password')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_repeated_login_failures_are_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'farmer@example.test',
            'password' => 'correct-horse-battery',
        ]);

        $login = Livewire::test(Login::class)
            ->set('email', 'farmer@example.test')
            ->set('password', 'incorrect-password');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $login->call('authenticate')
                ->set('password', 'incorrect-password');
        }

        $login->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
