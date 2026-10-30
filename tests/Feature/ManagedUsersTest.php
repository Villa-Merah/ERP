<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManagedUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_unavailable(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', $this->userData())->assertNotFound();
        $this->get('/login')->assertOk()->assertDontSee('href="'.url('/register').'"', false);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_superadmin_can_create_user_with_role(): void
    {
        $admin = User::factory()->create(['name' => 'Old Name', 'role' => 'superadmin']);
        $this->actingAs($admin)->post(route('omni.dashboard.users.store'), $this->userData())
            ->assertRedirect(route('omni.dashboard.users.index'))->assertSessionHasNoErrors();
        $user = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertSame('akademik', $user->role);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('secure-password-123', $user->password));
    }

    public function test_staff_and_guests_cannot_create_users(): void
    {
        $this->post(route('omni.dashboard.users.store'), $this->userData())->assertRedirect(route('login'));
        $user = User::factory()->create(['role' => 'staff']);
        $this->actingAs($user)->post(route('omni.dashboard.users.store'), $this->userData())->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'new@example.test']);
    }

    public function test_creation_validates_role_unique_email_and_password_confirmation(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $data = array_merge($this->userData(), ['email' => $admin->email, 'role' => 'invalid', 'password_confirmation' => 'different']);
        $this->actingAs($admin)->post(route('omni.dashboard.users.store'), $data)
            ->assertSessionHasErrors(['email', 'role', 'password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_superadmin_can_edit_user_details_role_and_password(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.test', 'role' => 'staff']);

        $this->actingAs($admin)->get(route('omni.dashboard.users.edit', $user))
            ->assertOk()->assertSee('Edit pengguna')->assertSee($user->email);
        $this->put(route('omni.dashboard.users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.test',
            'role' => 'akademik',
            'password' => 'new-secure-password-123',
            'password_confirmation' => 'new-secure-password-123',
        ])->assertRedirect(route('omni.dashboard.users.index'))->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertSame('akademik', $user->role);
        $this->assertTrue(Hash::check('new-secure-password-123', $user->password));
    }

    public function test_editing_user_without_new_password_keeps_existing_password(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff', 'password' => 'existing-password']);

        $this->actingAs($admin)->put(route('omni.dashboard.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('omni.dashboard.users.index'))->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('existing-password', $user->fresh()->password));
    }

    public function test_edit_validates_duplicate_email_and_preserves_own_superadmin_role(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $secondAdmin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff']);
        $other = User::factory()->create(['email' => 'other@example.test']);

        $this->actingAs($admin)->put(route('omni.dashboard.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $other->email,
            'role' => 'staff',
        ])->assertSessionHasErrors('email');

        $this->put(route('omni.dashboard.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'staff',
        ])->assertRedirect(route('omni.dashboard.users.index'))->assertSessionHasNoErrors();

        $this->assertSame('superadmin', $admin->fresh()->role);

        $this->put(route('omni.dashboard.users.update', $secondAdmin), [
            'name' => $secondAdmin->name,
            'email' => $secondAdmin->email,
            'role' => 'staff',
        ])->assertRedirect(route('omni.dashboard.users.index'))->assertSessionHasNoErrors();
        $this->assertSame('staff', $secondAdmin->fresh()->role);
    }

    public function test_admin_can_delete_another_user_but_not_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)->get(route('omni.dashboard.users.index'))
            ->assertOk()->assertSee(route('omni.dashboard.users.edit', $user))
            ->assertSee(route('omni.dashboard.users.destroy', $user));
        $this->delete(route('omni.dashboard.users.destroy', $user))
            ->assertRedirect(route('omni.dashboard.users.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $this->delete(route('omni.dashboard.users.destroy', $admin))
            ->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_non_admin_cannot_edit_or_delete_users(): void
    {
        $admin = User::factory()->create(['name' => 'Old Name', 'role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff']);

        $this->get(route('omni.dashboard.users.edit', $user))->assertRedirect(route('login'));
        $this->put(route('omni.dashboard.users.update', $user), [
            'name' => 'Changed',
            'email' => 'changed@example.test',
            'role' => 'staff',
        ])->assertRedirect(route('login'));

        $this->actingAs($user)->get(route('omni.dashboard.users.edit', $admin))->assertForbidden();
        $this->delete(route('omni.dashboard.users.destroy', $admin))->assertForbidden();
        $this->assertSame('Old Name', $admin->fresh()->name);
    }

    /** @return array<string, string> */
    private function userData(): array
    {
        return ['name' => 'New User', 'email' => 'new@example.test', 'password' => 'secure-password-123', 'password_confirmation' => 'secure-password-123', 'role' => 'akademik'];
    }
}
