<x-omni::layouts.dashboard :title="'Dashboard'">
    <style>
        .portal-hero { background: #172c25; border-radius: 24px; padding: 42px; color: white; position: relative; overflow: hidden; margin: 28px 0; }
        .portal-hero::after { content: 'V.'; position: absolute; right: 48px; bottom: -30px; color: #ceef80; font-size: 180px; font-weight: 800; opacity: .14; transform: rotate(-12deg); }
        .portal-hero h2 { font-size: clamp(28px, 4vw, 42px); font-weight: 600; letter-spacing: -1.5px; max-width: 650px; line-height: 1.2; }
        .portal-hero p { color: #bacac0; max-width: 510px; margin: 16px 0 25px; font-size: 14px; line-height: 1.8; }
        .portal-button { display: inline-flex; align-items: center; gap: 20px; background: #ceef80; color: #203329; padding: 12px 20px; border-radius: 10px; font-weight: 600; font-size: 13px; position: relative; z-index: 1; }
        .portal-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
        .application-card { display: flex; flex-direction: column; padding: 26px; border: 1px solid #e3e9e3; border-radius: 18px; background: white; transition: transform .2s, box-shadow .2s; }
        .application-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px #1935290a; }
        .application-icon { width: 50px; height: 50px; display: grid; place-items: center; background: #edf3e7; color: #426044; border-radius: 15px; font-size: 20px; font-weight: 700; }
        .application-card:nth-child(3n+2) .application-icon { background: #edf0fc; color: #6461a1; }
        .application-card:nth-child(3n) .application-icon { background: #fff1e8; color: #a76d42; }
        .portal-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 35px; }
        .portal-stat { border: 1px solid #e3e9e3; border-radius: 14px; padding: 20px; background: #ffffff90; }
        .portal-stat dt { font-size: 12px; color: #819087; margin-bottom: 8px; }
        .portal-stat dd { font-size: 19px; font-weight: 600; color: #263e31; }
        @media(max-width: 1100px) { .portal-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .portal-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media(max-width: 600px) { .portal-grid { grid-template-columns: 1fr; } .portal-hero { padding: 28px; } .portal-hero::after { right: 5px; } }
    </style>
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <p class="text-xs tracking-widest text-gray-400 mb-2">PORTAL VILLA MERAH</p>
            <h2 class="text-2xl font-semibold tracking-tight">Selamat datang, {{ auth()->user()->name }}.</h2>
            <p class="text-sm text-gray-500 mt-2">Semua aplikasi Anda dalam satu tempat.</p>
        </div>
        <span class="text-xs text-gray-500 border border-gray-200 rounded-full px-4 py-2">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
    </div>
    <section class="portal-hero">
        <div class="text-xs tracking-widest mb-5" style="color: #ceef80;">● AKSES KERJA TERPADU</div>
        <h2>Satu akun.<br>Semua ruang kerja.</h2>
        <p>Mulai hari Anda dengan lebih terarah. Temukan aplikasi yang terhubung ke akun SSO Anda, langsung dari portal ini.</p>
        <a href="#aplikasi" class="portal-button">Jelajahi aplikasi <span aria-hidden="true">↗</span></a>
    </section>
    <dl class="portal-stats">
        <div class="portal-stat"><dt>Aplikasi terdaftar</dt><dd>{{ $applications->count() }} <span class="text-xs font-normal">ruang kerja</span></dd></div>
        <div class="portal-stat"><dt>Login hari ini</dt><dd>{{ $stats['logins_today'] }} <span class="text-xs font-normal">aktivitas</span></dd></div>
        <div class="portal-stat"><dt>Peran Anda</dt><dd>{{ ucwords(auth()->user()->role) }}</dd></div>
        <div class="portal-stat"><dt>Sesi akun</dt><dd><span class="text-green-600 text-sm">●</span> Terhubung</dd></div>
    </dl>
    <section id="aplikasi" style="scroll-margin-top: 24px;">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div><h2 class="text-lg font-semibold">Aplikasi Anda <span class="text-sm text-gray-400 ml-2">{{ $applications->count() }}</span></h2><p class="text-xs text-gray-500 mt-1">Aplikasi SSO terdaftar yang belum dinonaktifkan.</p></div>
            @if(config('omni-central-auth.dashboard.features.clients'))
                <a href="{{ route('omni.dashboard.clients.index') }}" class="text-sm text-gray-500 hover:text-green-700">Kelola aplikasi ↗</a>
            @endif
        </div>
        <div class="portal-grid">
            @forelse($applications as $application)
                <article class="application-card">
                    <div class="flex items-center justify-between mb-6"><div class="application-icon">{{ mb_strtoupper(mb_substr($application['name'], 0, 1)) }}</div><span class="text-xs text-green-700 bg-green-50 px-3 py-1 rounded-full">Terdaftar</span></div>
                    <h3 class="text-lg font-semibold mb-2">{{ $application['name'] }}</h3>
                    <p class="text-xs text-gray-400 break-all mb-7">{{ $application['host'] }}</p>
                    @if($application['url'])
                        <a href="{{ $application['url'] }}" target="_blank" rel="noopener noreferrer" class="mt-auto flex items-center justify-between text-sm font-medium border-t border-gray-100 pt-4 text-green-800">Buka aplikasi <span aria-hidden="true">↗</span><span class="sr-only"> (tab baru)</span></a>
                    @else
                        <p class="text-xs text-gray-400 mt-auto">Alamat aplikasi belum tersedia.</p>
                    @endif
                </article>
            @empty
                <div class="col-span-full text-center rounded-xl border border-dashed border-gray-300 p-12"><h3 class="font-semibold">Belum ada aplikasi terhubung</h3><p class="text-sm text-gray-500 mt-2">Daftarkan aplikasi melalui menu OAuth Clients untuk menampilkannya di sini.</p></div>
            @endforelse
        </div>
    </section>
</x-omni::layouts.dashboard>

