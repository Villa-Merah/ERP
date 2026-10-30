<x-omni::layouts.dashboard :title="'Pengguna & Role'">
    <h2 class="text-2xl font-semibold mb-2">Pengguna &amp; role</h2>
    <p class="text-sm text-gray-500 mb-6">Superadmin mengelola portal SSO. Role lainnya menunjukkan jabatan pengguna. Role aplikasi lain dikelola di masing-masing aplikasi.</p>
    @if($errors->any())<div role="alert" class="bg-red-50 text-red-700 rounded-lg p-4 mb-5">{{ $errors->first() }}</div>@endif
    <details class="bg-white border border-gray-200 rounded-xl p-6 mb-6" @if($errors->hasAny(['name', 'email', 'password', 'password_confirmation', 'role'])) open @endif>
        <summary class="font-semibold text-green-800 cursor-pointer">+ Tambah pengguna</summary>
        <p class="text-sm text-gray-500 mt-3">Buat akun dan tentukan role. Registrasi publik tidak tersedia.</p>
        <form method="POST" action="{{ route('omni.dashboard.users.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
            @csrf
            <div><label for="new-name" class="block text-sm mb-2">Nama lengkap</label><input id="new-name" name="name" required maxlength="255" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label for="new-email" class="block text-sm mb-2">Email</label><input id="new-email" name="email" type="email" required value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label for="new-password" class="block text-sm mb-2">Kata sandi (minimal 8 karakter)</label><input id="new-password" name="password" type="password" required minlength="8" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label for="new-password-confirmation" class="block text-sm mb-2">Konfirmasi kata sandi</label><input id="new-password-confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label for="new-role" class="block text-sm mb-2">Role</label><select id="new-role" name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2">@foreach(\App\Models\User::ROLES as $role)<option value="{{ $role }}" @selected(old('role', 'staff') === $role)>{{ ucwords($role) }}</option>@endforeach</select></div>
            <div class="flex items-end"><button class="bg-green-800 text-white rounded-lg px-5 py-2">Buat pengguna</button></div>
        </form>
    </details>
    <div class="bg-white border border-gray-200 rounded-xl overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b border-gray-100"><th class="text-left p-5">Pengguna</th><th class="text-left p-5">Role</th><th class="text-left p-5">Terdaftar</th><th class="text-right p-5">Aksi</th></tr></thead><tbody>
        @forelse($users as $user)
            <tr class="border-b border-gray-100"><td class="p-5"><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-gray-400 mt-1">{{ $user->email }}</p></td><td class="p-5">
                @if($user->is(auth()->user()))<span class="text-green-700 font-medium">{{ $user->role }} (Anda)</span>
                @else<form method="POST" action="{{ route('omni.dashboard.users.role', $user->id) }}" class="flex gap-3">@csrf @method('PATCH')<select name="role" aria-label="Role {{ $user->name }}" class="border border-gray-200 rounded-lg px-3 py-2">@foreach(\App\Models\User::ROLES as $role)<option value="{{ $role }}" @selected($user->role === $role)>{{ ucwords($role) }}</option>@endforeach</select><button class="text-green-800 font-semibold">Simpan</button></form>@endif
            </td><td class="p-5 text-gray-500">{{ $user->created_at->format('d M Y') }}</td><td class="p-5"><div class="flex justify-end gap-3"><a href="{{ route('omni.dashboard.users.edit', $user) }}" class="text-green-800 font-semibold">Edit</a>@unless($user->is(auth()->user()))<form method="POST" action="{{ route('omni.dashboard.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')">@csrf @method('DELETE')<button class="text-red-700 font-semibold">Hapus</button></form>@endunless</div></td></tr>
        @empty<tr><td colspan="4" class="p-10 text-center text-gray-500">Belum ada pengguna.</td></tr>@endforelse
    </tbody></table></div><div class="mt-5">{{ $users->links() }}</div>
</x-omni::layouts.dashboard>
