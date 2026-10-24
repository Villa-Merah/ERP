<?php

namespace App\Providers;

use App\Http\Controllers\UserRoleController;
use DeveloperAwam\OmniCentralAuth\Http\Controllers\Dashboard\UserController;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;
use Laravel\Passport\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserController::class,
            UserRoleController::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('omni::dashboard.index', function (BladeView $view): void {
            $applications = Client::query()
                ->where('revoked', false)
                ->orderBy('name')
                ->get(['id', 'name', 'redirect_uris', 'grant_types'])
                ->filter(fn (Client $client): bool => in_array('authorization_code', $client->grant_types ?? [], true))
                ->map(function (Client $client): array {
                    $callback = $client->redirect_uris[0] ?? '';
                    $parts = parse_url($callback);
                    $url = null;

                    if (is_array($parts) && in_array($parts['scheme'] ?? '', ['http', 'https'], true) && ! empty($parts['host'])) {
                        $url = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
                    }

                    return ['name' => $client->name, 'url' => $url, 'host' => $parts['host'] ?? 'Alamat belum tersedia'];
                });

            $view->with('applications', $applications);
        });
    }
}
