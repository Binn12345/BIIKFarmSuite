<?php

namespace Tests\Feature;

use App\Livewire\Account\Profile;
use App\Livewire\Admin\UserManagement;
use App\Models\User;
use App\Notifications\UserInvitation;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_create_and_edit_a_user_with_a_role_and_send_a_secure_invitation(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(UserManagement::class)
            ->call('createUser')
            ->set('name', 'Farm Worker')
            ->set('email', 'worker@example.test')
            ->set('phone', '+63 900 000 0000')
            ->set('address', 'Demonstration farm')
            ->set('roleName', 'farm-staff')
            ->call('saveUser')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $user = User::query()->where('email', 'worker@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('farm-staff'));
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertNotSame('password', $user->password);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
        Notification::assertSentTo($user, UserInvitation::class);

        Livewire::actingAs($admin)->test(UserManagement::class)
            ->call('editUser', $user->id)
            ->set('name', 'Updated Farm Worker')
            ->set('roleName', 'farm-supervisor')
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->assertSame('Updated Farm Worker', $user->fresh()->name);
        $this->assertTrue($user->fresh()->hasRole('farm-supervisor'));
        $this->assertFalse($user->fresh()->hasRole('admin'));
    }

    public function test_user_creation_rejects_duplicate_email_and_privileged_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        User::factory()->create(['email' => 'taken@example.test']);

        Livewire::actingAs($admin)->test(UserManagement::class)
            ->call('createUser')
            ->set('name', 'Duplicate')
            ->set('email', 'taken@example.test')
            ->set('roleName', 'basic-user')
            ->call('saveUser')
            ->assertHasErrors(['email' => 'unique']);

        $this->assertDatabaseCount('users', 2);

        Livewire::actingAs($admin)->test(UserManagement::class)
            ->call('createUser')
            ->set('name', 'Attempted Admin')
            ->set('email', 'admin-target@example.test')
            ->set('roleName', 'admin')
            ->call('saveUser')
            ->assertHasErrors('roleName');

        $this->assertDatabaseMissing('users', ['email' => 'admin-target@example.test']);
        $this->assertSame(1, Role::query()->where('name', 'admin')->count());
    }

    public function test_mail_delivery_failure_does_not_undo_the_created_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        Log::shouldReceive('error')->once();

        Livewire::actingAs($admin)->test(UserManagement::class)
            ->call('createUser')
            ->set('name', 'Persisted User')
            ->set('email', 'persisted@example.test')
            ->set('roleName', 'basic-user')
            ->call('saveUser')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertSee('The account was saved, but the invitation could not be delivered.');

        $this->assertDatabaseHas('users', ['email' => 'persisted@example.test']);
        $this->assertDatabaseHas('model_has_roles', [
            'model_type' => User::class,
            'model_id' => User::query()->where('email', 'persisted@example.test')->value('id'),
            'role_id' => Role::findByName('basic-user')->id,
        ]);
    }

    public function test_profile_picture_upload_persists_blob_and_is_private_to_the_owner_or_authorized_admin(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('basic-user');
        $image = UploadedFile::fake()->image('profile.png', 32, 32);

        Livewire::actingAs($owner)->test(Profile::class)
            ->set('picture', $image)
            ->call('save')
            ->assertHasNoErrors();

        $row = DB::table('users')->where('id', $owner->id)->first();
        $this->assertNotEmpty($row->profile_picture);
        $this->assertSame('image/png', $row->profile_picture_mime_type);
        $this->assertStringContainsString(route('users.profile-picture', $owner->id), $this->actingAs($owner)->get(route('account.profile'))->getContent());
        $this->get(route('users.profile-picture', $owner))->assertOk()->assertHeader('Content-Type', 'image/png');

        $other = User::factory()->create();
        $other->assignRole('basic-user');
        $this->actingAs($other)->get(route('users.profile-picture', $owner))->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get(route('users.profile-picture', $owner))->assertOk();

        $this->assertArrayNotHasKey('profile_picture', $owner->fresh()->toArray());
    }

    public function test_invalid_image_and_unsupported_locale_are_rejected(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');

        Livewire::actingAs($user)->test(Profile::class)
            ->set('picture', UploadedFile::fake()->createWithContent('bad.png', 'not an image'))
            ->call('save')
            ->assertHasErrors('picture')
            ->set('picture', UploadedFile::fake()->image('oversized.png', 32, 32)->size(2049))
            ->call('save')
            ->assertHasErrors('picture');

        $this->actingAs($user)->from(route('account.profile'))
            ->post(route('locale.switch'), ['locale' => 'xx'])
            ->assertSessionHasErrors('locale');
    }

    public function test_profile_picture_can_be_replaced_and_removed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        $component = Livewire::actingAs($user)->test(Profile::class)
            ->assertSet('hasPicture', false)
            ->set('picture', UploadedFile::fake()->image('first.png', 32, 32))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('hasPicture', true)
            ->set('picture', UploadedFile::fake()->image('replacement.png', 32, 32))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('image/png', DB::table('users')->where('id', $user->id)->value('profile_picture_mime_type'));

        $component->set('removePicture', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('hasPicture', false);

        $this->assertNull(DB::table('users')->where('id', $user->id)->value('profile_picture'));
        $this->assertNull(DB::table('users')->where('id', $user->id)->value('profile_picture_mime_type'));
        $this->actingAs($user)->get(route('users.profile-picture', $user))->assertNotFound();
    }

    public function test_guest_and_authenticated_language_choices_are_validated_and_persisted(): void
    {
        $this->from(route('login'))->post(route('locale.switch'), ['locale' => 'fil'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('locale', 'fil');
        $this->get(route('login'))->assertOk()->assertSee('Mag-log in');

        $user = User::factory()->create();
        $user->assignRole('basic-user');
        $this->actingAs($user)->post(route('locale.switch'), ['locale' => 'fil'])
            ->assertRedirect();

        $this->assertSame('fil', $user->fresh()->locale);
        $this->get(route('dashboard'))->assertOk()->assertSee('Pangunahing dashboard');

        $this->post(route('locale.switch'), ['locale' => 'en'])->assertRedirect();
        $this->assertSame('en', $user->fresh()->locale);
    }
}
