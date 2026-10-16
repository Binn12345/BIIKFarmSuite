<?php

namespace Tests\Feature;

use App\Livewire\Account\Settings;
use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\Pen;
use App\Models\User;
use App\Support\AdminDashboardAnalytics;
use App\Support\DashboardModules;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_dashboard_shows_database_backed_metrics_and_responsive_charts(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $pen = Pen::create(['name' => 'Analytics pen', 'capacity' => 10, 'status' => 'active']);
        foreach (range(1, 2) as $index) {
            Animal::create([
                'tag_number' => 'ANALYTICS-ANIMAL-'.$index,
                'sex' => 'female',
                'status' => 'active',
                'pen_id' => $pen->id,
                'category' => 'Breeding sow',
            ]);
        }

        FarmRecord::create([
            'module' => 'sales', 'reference' => 'ANALYTICS-SALE-1', 'title' => 'Completed sale',
            'record_date' => now()->startOfMonth()->toDateString(), 'status' => 'completed', 'amount' => 125.50,
        ]);
        FarmRecord::create([
            'module' => 'sales', 'reference' => 'ANALYTICS-SALE-2', 'title' => 'Cancelled sale',
            'record_date' => now()->startOfMonth()->toDateString(), 'status' => 'cancelled', 'amount' => 900,
        ]);

        $analytics = AdminDashboardAnalytics::for($admin, 3);
        $this->assertSame(3, $analytics['period']);
        $this->assertSame(125.5, array_sum(array_column($analytics['sales'], 'value')));
        $this->assertSame('2', collect($analytics['metrics'])->firstWhere('key', 'active_animals')['value']);
        $this->assertSame(2, $analytics['animalCategories'][0]['value']);

        $this->actingAs($admin)->get(route('dashboard', ['period' => 3]))
            ->assertOk()
            ->assertSee('Farm operations at a glance')
            ->assertSee('Completed sales revenue; not profit')
            ->assertSee('Sales revenue by month')
            ->assertSee('Animals by category')
            ->assertSee('ANALYTICS-SALE-1')
            ->assertSee('125.50')
            ->assertDontSee('900.00');
    }

    public function test_basic_dashboard_is_concise_and_only_shows_authorized_navigation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo(['animals.view']);

        $response = $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('Welcome, '.$user->name.'.')
            ->assertSee(route('modules.animals'))
            ->assertDontSee(route('admin.users'))
            ->assertDontSee('Farm operations at a glance')
            ->assertDontSee('Open module');

        $this->assertFalse(str_contains($response->getContent(), 'module-category-'));
    }

    public function test_account_dropdown_has_profile_settings_and_post_signout_without_language_selector(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Settings')
            ->assertSee('Sign out')
            ->assertSee(route('account.profile'))
            ->assertSee(route('account.settings'))
            ->assertSee('aria-haspopup="true"', false)
            ->assertSee('@click.outside="accountMenuOpen = false"', false)
            ->assertSee('@keydown.escape.window=', false)
            ->assertDontSee('locale-select')
            ->assertDontSee('guest-locale');

        $this->assertSame('POST', Route::getRoutes()->getByName('logout')->methods()[0]);
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_theme_preferences_are_validated_persisted_and_applied_to_the_authenticated_layout(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');

        Livewire::actingAs($user)->test(Settings::class)
            ->set('background', 'cool-gray')
            ->set('accent', 'teal')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Your theme preferences have been saved.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'theme_background' => 'cool-gray',
            'theme_accent' => 'teal',
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('data-theme-background="cool-gray"', false)
            ->assertSee('data-theme-accent="teal"', false);

        Livewire::actingAs($user)->test(Settings::class)
            ->assertSet('background', 'cool-gray')
            ->assertSet('accent', 'teal')
            ->set('background', 'dark')
            ->set('accent', 'orange')
            ->call('save')
            ->assertHasErrors(['background', 'accent']);
    }

    public function test_dashboard_navigation_entries_reference_registered_routes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        foreach (DashboardModules::for($admin) as $items) {
            foreach ($items as $item) {
                $this->assertTrue(Route::has($item['route']), $item['route'].' must be a registered route.');
            }
        }
    }

    public function test_admin_dashboard_has_safe_empty_states_when_farm_records_are_absent(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()
            ->assertSee('No completed sales in this period.')
            ->assertSee('No animal categories have been recorded.')
            ->assertSee('No recent farm records to show.');
    }
}
