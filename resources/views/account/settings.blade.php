<x-omni::layouts.dashboard :title="'Pengaturan Akun'">
    <div class="mb-7"><h2 class="text-2xl font-semibold">Pengaturan akun</h2><p class="text-sm text-gray-500 mt-2">Kelola profil, keamanan, dan informasi akses Anda.</p></div>
    @if($errors->any())<div class="mb-6 rounded-lg bg-red-50 text-red-700 p-4 text-sm" role="alert">{{ $errors->first() }}</div>@endif
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="font-semibold text-lg mb-1">Profil akun</h3><p class="text-sm text-gray-500 mb-6">Perubahan email memerlukan verifikasi ulang jika verifikasi email diaktifkan.</p>
            <form method="POST" action="{{ route('account.update') }}" class="space-y-4">
                @csrf @method('PATCH')
                <div><label for="name" class="block text-sm mb-2">Nama lengkap</label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="255" autocomplete="name" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label for="email" class="block text-sm mb-2">Email</label><input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="email" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label for="profile-password" class="block text-sm mb-2">Konfirmasi kata sandi saat ini</label><input id="profile-password" type="password" name="current_password" required autocomplete="current-password" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
                <button class="bg-green-800 text-white px-5 py-2 rounded-lg text-sm">Simpan profil</button>
            </form>
        </section>
        <section class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="font-semibold text-lg mb-1">Ubah kata sandi</h3><p class="text-sm text-gray-500 mb-6">Gunakan minimal 8 karakter untuk kata sandi baru.</p>
            <form method="POST" action="{{ route('account.password') }}" class="space-y-4">
                @csrf @method('PUT')
                <div><label for="current-password" class="block text-sm mb-2">Kata sandi saat ini</label><input id="current-password" type="password" name="current_password" required autocomplete="current-password" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label for="password" class="block text-sm mb-2">Kata sandi baru</label><input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label for="password-confirmation" class="block text-sm mb-2">Ulangi kata sandi baru</label><input id="password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
                <button class="bg-green-800 text-white px-5 py-2 rounded-lg text-sm">Perbarui kata sandi</button>
            </form>
        </section>
    </div>
    <section class="bg-white border border-gray-200 rounded-xl p-6 mt-6"><h3 class="font-semibold">Role &amp; akses</h3><p class="text-sm text-gray-500 mt-2">Role akun Anda: <strong>{{ ucwords(auth()->user()->role) }}</strong>. Superadmin mengelola aplikasi, pengguna, dan log aktivitas. Role lainnya menunjukkan jabatan pengguna.</p>@if(auth()->user()->is_admin)<a href="{{ route('omni.dashboard.users.index') }}" class="inline-block mt-4 text-green-800 text-sm font-semibold">Kelola pengguna &amp; role →</a>@endif</section>
</x-omni::layouts.dashboard>

