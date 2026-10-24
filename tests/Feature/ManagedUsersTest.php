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
        $admin = User::factory()->create(['role' => 'superadmin']);
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

    /** @return array<string, string> */
    private function userData(): array
    {
        return ['name' => 'New User', 'email' => 'new@example.test', 'password' => 'secure-password-123', 'password_confirmation' => 'secure-password-123', 'role' => 'akademik'];
    }
}
