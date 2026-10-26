<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_regular_roles_can_open_user_dashboard_without_admin_access(): void
    {
        foreach (array_diff(User::ROLES, ['superadmin']) as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/user')->assertOk();
            $this->get(route('omni.dashboard.index'))->assertForbidden();
        }
    }

    public function test_superadmin_opening_user_page_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->get('/user')->assertRedirect(route('omni.dashboard.index'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/user')->assertRedirect(route('login'));
    }

    public function test_unknown_role_is_not_allowed_to_open_user_page(): void
    {
        $user = User::factory()->create(['role' => 'unrecognized']);
        $this->actingAs($user)->get('/user')->assertForbidden();
    }

    public function test_staff_login_can_reach_its_default_destination(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'password' => 'password']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/user');
        $this->get('/user')->assertOk();
    }
}
