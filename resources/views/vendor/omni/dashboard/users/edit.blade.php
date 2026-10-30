<x-omni::layouts.dashboard :title="'Edit Pengguna'">
    <h2 class="text-2xl font-semibold mb-2">Edit pengguna</h2>
    <p class="text-sm text-gray-500 mb-6">Perbarui informasi akun. Kosongkan kata sandi jika tidak ingin menggantinya.</p>

    @if($errors->any())
        <div role="alert" class="bg-red-50 text-red-700 rounded-lg p-4 mb-5">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('omni.dashboard.users.update', $user) }}" class="bg-white border border-gray-200 rounded-xl p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @method('PUT')

        <div>
            <label for="user-name" class="block text-sm mb-2">Nama lengkap</label>
            <input id="user-name" name="name" required maxlength="255" value="{{ old('name', $user->name) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label for="user-email" class="block text-sm mb-2">Email</label>
            <input id="user-email" name="email" type="email" required maxlength="255" value="{{ old('email', $user->email) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        @if(!$user->is(auth()->user()))
            <div>
                <label for="user-role" class="block text-sm mb-2">Role</label>
                <select id="user-role" name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @foreach(\App\Models\User::ROLES as $role)
                        <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucwords($role) }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="role" value="{{ $user->role }}">
            <div>
                <span class="block text-sm mb-2">Role</span>
                <p class="border border-gray-200 rounded-lg px-3 py-2 text-gray-600">{{ ucwords($user->role) }} (Anda)</p>
            </div>
        @endif
        <div>
            <label for="user-password" class="block text-sm mb-2">Kata sandi baru (opsional, minimal 8 karakter)</label>
            <input id="user-password" name="password" type="password" minlength="8" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label for="user-password-confirmation" class="block text-sm mb-2">Konfirmasi kata sandi baru</label>
            <input id="user-password-confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2 flex gap-3">
            <button class="bg-green-800 text-white rounded-lg px-5 py-2">Simpan perubahan</button>
            <a href="{{ route('omni.dashboard.users.index') }}" class="border border-gray-300 rounded-lg px-5 py-2">Batal</a>
        </div>
    </form>
</x-omni::layouts.dashboard>
