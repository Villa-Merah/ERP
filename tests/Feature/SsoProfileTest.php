<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use phpseclib4\Crypt\RSA;
use Tests\TestCase;

class SsoProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_requires_bearer_authentication(): void
    {
        $this->getJson('/api/sso/user')->assertUnauthorized();
    }

    public function test_profile_returns_only_identity_fields(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        Passport::actingAs($user, [], 'api');
        $this->getJson('/api/sso/user')->assertOk()->assertExactJson(['id' => (string) $user->id, 'name' => $user->name, 'email' => $user->email])->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_passport_consent_uses_standard_oauth_approval_form(): void
    {
        $key = RSA::createKey(2048);
        $privateKey = (string) $key;
        config(['passport.private_key' => $privateKey, 'passport.public_key' => (string) $key->getPublicKey()]);
        $user = User::factory()->create(['role' => 'staff']);
        $verifier = str_repeat('a', 64);
        $client = Client::create(['name' => 'Inventory', 'secret' => 'secret', 'redirect_uris' => ['https://inventory.example.test/sso/callback'], 'grant_types' => ['authorization_code', 'refresh_token'], 'revoked' => false]);
        $this->actingAs($user)->get('/oauth/authorize?'.http_build_query(['client_id' => $client->id, 'redirect_uri' => 'https://inventory.example.test/sso/callback', 'response_type' => 'code', 'state' => 'test-state', 'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='), 'code_challenge_method' => 'S256']))
            ->assertOk()->assertSee('Hubungkan Inventory?')->assertSee('name="auth_token"', false)->assertSee(route('passport.authorizations.approve'), false);

        $approval = $this->post(route('passport.authorizations.approve'), [
            'client_id' => $client->id, 'state' => 'test-state', 'auth_token' => session('authToken'),
        ])->assertRedirect();
        parse_str(parse_url($approval->headers->get('Location'), PHP_URL_QUERY), $callback);
        $this->assertSame('test-state', $callback['state']);
        $exchange = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code', 'client_id' => $client->id, 'client_secret' => 'secret',
            'redirect_uri' => 'https://inventory.example.test/sso/callback', 'code' => $callback['code'],
            'code_verifier' => $verifier,
        ])->assertOk()->assertJsonStructure(['access_token']);
        $this->getJson('/api/sso/user', ['Authorization' => 'Bearer '.$exchange->json('access_token')])
            ->assertOk()->assertJsonPath('id', (string) $user->id);
    }
}
