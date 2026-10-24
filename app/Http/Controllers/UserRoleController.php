<?php

namespace App\Http\Controllers;

use App\Models\User;
use DeveloperAwam\OmniCentralAuth\Http\Controllers\Dashboard\UserController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRoleController extends UserController
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isOmniAdmin(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', Password::min(8), 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);
        unset($data['password_confirmation']);
        User::create($data);

        return to_route('omni.dashboard.users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function updateRole(Request $request, $userId): RedirectResponse
    {
        abort_unless($request->user()?->isOmniAdmin(), 403);
        $data = $request->validate(['role' => ['required', Rule::in(User::ROLES)]]);
        $user = User::findOrFail($userId);

        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'Role akun sendiri tidak dapat diubah.']);
        }

        $user->update(['role' => $data['role']]);

        return back()->with('success', 'Role '.$user->name.' berhasil diperbarui.');
    }
}
