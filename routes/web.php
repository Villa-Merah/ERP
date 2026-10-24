<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\SsoProfileController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('login');
});

Route::get('/api/sso/user', SsoProfileController::class)
    ->middleware(['auth:api', 'throttle:60,1'])
    ->name('sso.profile');

Route::middleware('auth')->group(function (): void {
    Route::get('/account/settings', [AccountSettingsController::class, 'edit'])->name('account.settings');
    Route::patch('/account/settings', [AccountSettingsController::class, 'update'])->middleware('throttle:10,1')->name('account.update');
    Route::put('/account/password', [AccountSettingsController::class, 'password'])->middleware('throttle:10,1')->name('account.password');
});

Route::post('/'.config('omni-central-auth.dashboard.prefix', 'omni-dashboard').'/users', [UserRoleController::class, 'store'])
    ->middleware(['auth', 'omni.admin', 'throttle:20,1'])
    ->name('omni.dashboard.users.store');
