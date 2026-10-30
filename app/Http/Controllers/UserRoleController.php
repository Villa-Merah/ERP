<?php

namespace App\Http\Controllers;

use App\Models\User;
use DeveloperAwam\OmniCentralAuth\Http\Controllers\Dashboard\UserController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

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

    public function edit(User $user): View
    {
        return view('vendor.omni.dashboard.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $isEditingOwnAccount = $user->is($request->user());
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => ['nullable', Password::min(8), 'confirmed'],
        ]);

        if ($isEditingOwnAccount) {
            unset($data['role']);
        } elseif ($user->role === 'superadmin'
            && $data['role'] !== 'superadmin'
            && User::where('role', 'superadmin')->count() <= 1) {
            return back()->withErrors(['role' => 'Akun superadmin terakhir tidak dapat diturunkan rolenya.'])->withInput();
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return to_route('omni.dashboard.users.index')->with('success', 'Pengguna '.$user->name.' berhasil diperbarui.');
    }

    public function destroy($userId): RedirectResponse
    {
        $user = User::findOrFail($userId);

        if ($user->is(request()->user())) {
            return back()->withErrors(['delete' => 'Akun sendiri tidak dapat dihapus.']);
        }

        if ($user->role === 'superadmin' && User::where('role', 'superadmin')->count() <= 1) {
            return back()->withErrors(['delete' => 'Akun superadmin terakhir tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($user): void {
            $accessTokenIds = DB::table('oauth_access_tokens')
                ->where('user_id', $user->id)
                ->pluck('id');

            if ($accessTokenIds->isNotEmpty()) {
                DB::table('oauth_refresh_tokens')
                    ->whereIn('access_token_id', $accessTokenIds)
                    ->update(['revoked' => true]);
                DB::table('oauth_access_tokens')
                    ->whereIn('id', $accessTokenIds)
                    ->update(['revoked' => true]);
            }

            DB::table('oauth_auth_codes')
                ->where('user_id', $user->id)
                ->update(['revoked' => true]);
            DB::table('oauth_device_codes')
                ->where('user_id', $user->id)
                ->update(['revoked' => true]);
            DB::table('sessions')->where('user_id', $user->id)->delete();

            $user->delete();
        });

        return to_route('omni.dashboard.users.index')->with('success', 'Pengguna '.$user->name.' berhasil dihapus.');
    }
}
