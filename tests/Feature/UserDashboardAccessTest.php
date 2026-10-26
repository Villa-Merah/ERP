<?php

namespace Tests\Feature;

use App\Models\User;
use DeveloperAwam\OmniCentralAuth\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
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

    public function test_staff_portal_displays_active_applications_without_management_links_or_secrets(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $client = Client::create(['name' => 'Ruang Kerja', 'secret' => 'private-secret', 'redirect_uris' => ['https://ruangkerja.example.test/sso/callback'], 'grant_types' => ['authorization_code'], 'revoked' => false]);
        Client::create(['name' => 'Revoked App', 'redirect_uris' => ['https://revoked.example.test/callback'], 'grant_types' => ['authorization_code'], 'revoked' => true]);
        $this->actingAs($staff)->get('/user')->assertOk()
            ->assertSee('Ruang Kerja')->assertSee('https://ruangkerja.example.test', false)
            ->assertSee('Buka aplikasi')->assertSee('Pengaturan Akun')
            ->assertDontSee('Revoked App')->assertDontSee('Kelola aplikasi')
            ->assertDontSee('OAuth Clients')->assertDontSee('Users &amp; Roles', false)->assertDontSee('Audit Log')
            ->assertDontSee('private-secret')->assertDontSee($client->secret, false);
        $this->get(route('omni.dashboard.clients.index'))->assertForbidden();
        $this->get(route('omni.dashboard.users.index'))->assertForbidden();
        $this->get(route('omni.dashboard.audit-log.index'))->assertForbidden();
    }

    public function test_staff_login_count_only_includes_own_activity(): void
    {
        $this->freezeTime();
        $staff = User::factory()->create(['role' => 'staff']);
        $other = User::factory()->create(['role' => 'staff']);
        foreach ([$staff->id, $other->id, $other->id] as $userId) {
            AuditLog::create(['user_id' => $userId, 'event' => 'login', 'ip_address' => '127.0.0.1', 'occurred_at' => now()]);
        }
        $this->actingAs($staff)->get('/user')->assertSee('<dt>Login Anda hari ini</dt><dd>1 ', false);
    }

    public function test_superadmin_portal_keeps_application_management_link(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->get(route('omni.dashboard.index'))->assertOk()
            ->assertSee('Kelola aplikasi')->assertSee('OAuth Clients')->assertSee('Audit Log');
    }

    public function test_staff_login_can_reach_its_default_destination(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'password' => 'password']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/user');
        $this->get('/user')->assertOk();
    }
}
