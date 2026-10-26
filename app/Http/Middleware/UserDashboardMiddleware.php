<?php

namespace App\Http\Middleware;

use Closure;
use DeveloperAwam\OmniCentralAuth\Http\Middleware\OmniUserMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserDashboardMiddleware extends OmniUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isOmniAdmin()) {
            return to_route('omni.dashboard.index');
        }

        return parent::handle($request, $next);
    }
}
