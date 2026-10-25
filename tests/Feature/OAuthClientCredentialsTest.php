<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Tests\TestCase;

class OAuthClientCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_shows_usable_plain_secret_while_storing_only_hash(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $response = $this->actingAs($admin)->post(route('omni.dashboard.clients.store'), [
            'name' => 'Ruang Kerja', 'redirect' => 'https://ruangkerja.example.test/sso/callback',
        ])->assertCreated()->assertViewIs('omni::dashboard.clients.credentials')
            ->assertHeader('Cache-Control', 'no-store, private');
        $secret = $response->viewData('secret');
        $client = Client::firstOrFail();
        $this->assertIsString($secret);
        $this->assertTrue(Hash::check($secret, $client->secret));
        $this->assertNotSame($secret, $client->secret);
        $response->assertSee($secret)->assertDontSee($client->secret, false);
        $this->get(route('omni.dashboard.clients.edit', $client))->assertOk()
            ->assertDontSee($secret, false)->assertDontSee($client->secret, false)->assertSee('Buat Ulang Secret');
    }

    public function test_rotation_invalidates_old_secret_and_displays_new_secret_once(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $client = $this->client();
        $response = $this->actingAs($admin)->post(route('omni.dashboard.clients.secret', $client))
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $secret = $response->viewData('secret');
        $this->assertTrue(Hash::check($secret, $client->fresh()->secret));
        $this->assertFalse(Hash::check('old-secret', $client->fresh()->secret));
        $this->get(route('omni.dashboard.clients.edit', $client))->assertDontSee($secret, false);
    }

    public function test_staff_cannot_create_clients_or_rotate_secrets(): void
    {
        $client = $this->client();
        $this->actingAs(User::factory()->create(['role' => 'staff']))
            ->post(route('omni.dashboard.clients.secret', $client))->assertForbidden();
        $this->post(route('omni.dashboard.clients.store'), ['name' => 'Unauthorized', 'redirect' => 'https://example.test/callback'])->assertForbidden();
        $this->assertTrue(Hash::check('old-secret', $client->fresh()->secret));
        $this->assertDatabaseCount('oauth_clients', 1);
    }

    public function test_revoked_client_cannot_have_its_secret_rotated(): void
    {
        $client = $this->client();
        $client->update(['revoked' => true]);
        $this->actingAs(User::factory()->create(['role' => 'superadmin']))
            ->post(route('omni.dashboard.clients.secret', $client))->assertNotFound();
        $this->assertTrue(Hash::check('old-secret', $client->fresh()->secret));
    }

    private function client(): Client
    {
        return Client::create([
            'name' => 'Ruang Kerja', 'secret' => 'old-secret', 'redirect_uris' => ['https://ruangkerja.example.test/sso/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'], 'revoked' => false,
        ]);
    }
}
