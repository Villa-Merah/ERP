<x-omni::layouts.dashboard :title="'Kredensial Aplikasi'">
    <div class="max-w-2xl bg-white border border-gray-200 rounded-xl p-6">
        <h2 class="text-xl font-semibold">Kredensial {{ $client->name }}</h2>
        <p class="mt-3 text-sm text-gray-600">Salin Client ID dan Client Secret berikut ke konfigurasi aplikasi. Secret asli hanya ditampilkan pada halaman ini. Jika hilang, buat ulang secret dari halaman Edit Client.</p>
        <dl class="mt-6 space-y-5">
            <div><dt class="text-sm font-semibold mb-2">Client ID</dt><dd class="font-mono text-sm bg-gray-50 border border-gray-200 rounded-lg p-3 break-all select-all">{{ $client->id }}</dd></div>
            <div><dt class="text-sm font-semibold mb-2">Client Secret</dt><dd class="font-mono text-sm bg-gray-50 border border-gray-200 rounded-lg p-3 break-all select-all">{{ $secret }}</dd></div>
        </dl>
        <p class="text-sm text-gray-500 mt-5">Gunakan nilai di atas persis seperti ditampilkan. Simpan secret di .env aplikasi yang bersangkutan.</p>
        <a href="{{ route('omni.dashboard.clients.edit', $client->id) }}" class="inline-block mt-6 px-5 py-2 rounded-lg bg-green-800 text-white text-sm font-semibold">Selesai, kembali ke aplikasi</a>
    </div>
</x-omni::layouts.dashboard>
