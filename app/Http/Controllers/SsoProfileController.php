<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SsoProfileController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user('api');

        return response()->json([
            'id' => (string) $user->getAuthIdentifier(),
            'name' => $user->name,
            'email' => $user->email,
        ])->header('Cache-Control', 'no-store');
    }
}
