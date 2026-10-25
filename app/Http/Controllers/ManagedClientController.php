<?php

namespace App\Http\Controllers;

use DeveloperAwam\OmniCentralAuth\Http\Controllers\Dashboard\ClientController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class ManagedClientController extends ClientController
{
    public function store(Request $request): Response
    {
        abort_unless($request->user()?->isOmniAdmin(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'redirect' => ['required', 'url:http,https'],
        ]);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            user: $request->user(),
            name: $data['name'],
            redirectUris: [$data['redirect']],
            confidential: true,
            enableDeviceFlow: false,
        );

        return $this->credentials($client, 201);
    }

    public function regenerateSecret(Request $request, Client $client): Response
    {
        abort_unless($request->user()?->isOmniAdmin(), 403);
        abort_if($client->revoked, 404);
        app(ClientRepository::class)->regenerateSecret($client);

        return $this->credentials($client);
    }

    private function credentials(Client $client, int $status = 200): Response
    {
        return response()->view('omni::dashboard.clients.credentials', [
            'client' => $client,
            'secret' => $client->plainSecret,
        ], $status)->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
