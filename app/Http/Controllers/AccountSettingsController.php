<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function edit(): View
    {
        return view('account.settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['required', 'current_password'],
        ]);

        if ($user->email !== $data['email']) {
            $user->email_verified_at = null;
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email']])->save();

        return back()->with('success', 'Profil akun berhasil diperbarui.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $request->user()->forceFill(['password' => $data['password']])->save();
        $request->session()->regenerate();

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
