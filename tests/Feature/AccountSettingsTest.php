<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_settings(): void
    {
        $this->get(route('account.settings'))->assertRedirect(route('login'));
    }

    public function test_profile_update_does_not_allow_role_escalation(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $this->actingAs($user)->patch(route('account.update'), [
            'name' => 'Updated Name', 'email' => 'updated@example.test',
            'current_password' => 'password', 'role' => 'superadmin', 'is_admin' => true,
        ])->assertSessionHasNoErrors();
        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('account.password'), [
            'current_password' => 'incorrect', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_admin_can_change_role_and_admin_access_together(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff']);
        $this->actingAs($admin)->patch(route('omni.dashboard.users.role', $user), ['role' => 'superadmin'])->assertSessionHasNoErrors();
        $this->assertTrue($user->fresh()->is_admin);
        $this->actingAs($admin)->patch(route('omni.dashboard.users.role', $user), ['role' => 'staff'])->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_users_cannot_manage_roles_and_admin_cannot_demote_self(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff']);
        $this->actingAs($user)->patch(route('omni.dashboard.users.role', $admin), ['role' => 'staff'])->assertForbidden();
        $this->actingAs($admin)->patch(route('omni.dashboard.users.role', $admin), ['role' => 'staff'])->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_all_listed_roles_are_supported_and_unknown_roles_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'staff']);

        foreach (User::ROLES as $role) {
            $this->actingAs($admin)->patch(route('omni.dashboard.users.role', $user), ['role' => $role])
                ->assertSessionHasNoErrors();
            $this->assertSame($role, $user->fresh()->role);
            $this->assertSame($role === 'superadmin', $user->fresh()->is_admin);
        }

        $this->actingAs($admin)->patch(route('omni.dashboard.users.role', $user), ['role' => 'invalid'])
            ->assertSessionHasErrors('role');
        $this->assertSame('pengajar', $user->fresh()->role);
    }

    public function test_legacy_registration_roles_are_normalized(): void
    {
        $this->assertSame('superadmin', User::factory()->create(['role' => 'admin'])->role);
        $this->assertSame('staff', User::factory()->create(['role' => 'user'])->role);
    }
}
