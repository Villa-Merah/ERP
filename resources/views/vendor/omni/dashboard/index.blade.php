<x-omni::layouts.dashboard :title="'Dashboard'">
    <style>
        .portal-hero { background: #172c25; border-radius: 24px; padding: 42px; color: white; position: relative; overflow: hidden; margin: 28px 0; }
        .portal-hero::after { content: 'V.'; position: absolute; right: 48px; bottom: -30px; color: #ceef80; font-size: 180px; font-weight: 800; opacity: .14; transform: rotate(-12deg); }
        .portal-hero h2 { font-size: clamp(28px, 4vw, 42px); font-weight: 600; letter-spacing: -1.5px; max-width: 650px; line-height: 1.2; }
        .portal-hero p { color: #bacac0; max-width: 510px; margin: 16px 0 25px; font-size: 14px; line-height: 1.8; }
        .portal-button { display: inline-flex; align-items: center; gap: 20px; background: #ceef80; color: #203329; padding: 12px 20px; border-radius: 10px; font-weight: 600; font-size: 13px; position: relative; z-index: 1; }
        .portal-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
        .application-card { display: flex; flex-direction: column; align-items: center; text-align: center; min-width: 0; padding: 28px; border: 1px solid #e3e9e3; border-radius: 18px; background: white; transition: transform .2s, box-shadow .2s; }
        .application-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px #1935290a; }
        .application-icon { width: 64px; height: 64px; display: grid; place-items: center; background: #edf3e7; color: #426044; border-radius: 20px; margin-bottom: 16px; }
        .application-icon svg { width: 30px; height: 30px; }
        .application-status { display: inline-flex; align-items: center; gap: 6px; color: #15803d; background: #f0fdf4; padding: 4px 12px; border-radius: 999px; font-size: 11px; margin-bottom: 18px; }
        .application-status svg { width: 13px; height: 13px; }
        .application-launch { display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; margin-top: auto; padding: 12px 16px; border-radius: 10px; background: #edf3e7; color: #263e31; font-size: 13px; font-weight: 600; transition: background .2s, color .2s; }
        .application-launch:hover { background: #263e31; color: white; }
        .application-launch:focus-visible { outline: 3px solid #426044; outline-offset: 4px; }
        .application-launch svg { width: 17px; height: 17px; flex-shrink: 0; }
        @media (prefers-reduced-motion: reduce) { .application-card, .application-launch { transition: none; } .application-card:hover { transform: none; } }
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
        <div class="portal-stat"><dt>{{ auth()->user()->isOmniAdmin() ? 'Login hari ini' : 'Login Anda hari ini' }}</dt><dd>{{ $stats['logins_today'] }} <span class="text-xs font-normal">aktivitas</span></dd></div>
        <div class="portal-stat"><dt>Peran Anda</dt><dd>{{ ucwords(auth()->user()->role) }}</dd></div>
        <div class="portal-stat"><dt>Sesi akun</dt><dd><span class="text-green-600 text-sm">●</span> Terhubung</dd></div>
    </dl>
    <section id="aplikasi" style="scroll-margin-top: 24px;">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div><h2 class="text-lg font-semibold">Aplikasi Anda <span class="text-sm text-gray-400 ml-2">{{ $applications->count() }}</span></h2><p class="text-xs text-gray-500 mt-1">Aplikasi SSO terdaftar yang belum dinonaktifkan.</p></div>
            @if(auth()->user()->isOmniAdmin() && config('omni-central-auth.dashboard.features.clients'))
                <a href="{{ route('omni.dashboard.clients.index') }}" class="text-sm text-gray-500 hover:text-green-700">Kelola aplikasi ↗</a>
            @endif
        </div>
        <div class="portal-grid">
            @forelse($applications as $application)
                @php
                    $applicationName = mb_strtolower($application['name']);
                    $iconPath = match (true) {
                        str_contains($applicationName, 'ads') => 'M3 11v2a2 2 0 0 0 2 2h2l3 5h3l-3-5h1l9 4V5l-9 4H5a2 2 0 0 0-2 2Zm4-2v6M20 9l2-1m-2 7 2 1',
                        str_contains($applicationName, 'lead'), str_contains($applicationName, 'crm') => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8m8-7.87a4 4 0 0 1 0 7.75',
                        str_contains($applicationName, 'inventory') => 'm12 3 9 5-9 5-9-5 9-5Zm-9 5v9l9 5 9-5V8m-9 5v9M7.5 5.5l9 5',
                        str_contains($applicationName, 'pendaftaran') => 'M9 5H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3M9 3h6v4H9V3Zm-1 11 3 3 5-6',
                        str_contains($applicationName, 'rapat') => 'M15 8l6-4v16l-6-4M3 6h10a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z',
                        str_contains($applicationName, 'rekap') => 'M4 3v18h17M8 16v-5m5 5V7m5 9v-8',
                        default => 'M3 3h7v7H3V3Zm11 0h7v7h-7V3ZM3 14h7v7H3v-7Zm11 0h7v7h-7v-7Z',
                    };
                @endphp
                <article class="application-card">
                    <div class="application-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $iconPath }}" /></svg></div>
                    <span class="application-status"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="m8 12 3 3 5-6" /></svg>Terdaftar</span>
                    <h3 class="text-lg font-semibold mb-2">{{ $application['name'] }}</h3>
                    <p class="text-xs text-gray-400 break-all mb-7">{{ $application['host'] }}</p>
                    @if($application['url'])
                        <a href="{{ $application['url'] }}" target="_blank" rel="noopener noreferrer" class="application-launch"><span>Buka aplikasi</span><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6m0-6L10 14M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5" /></svg><span class="sr-only"> (tab baru)</span></a>
                    @else
                        <p class="text-xs text-gray-400 mt-auto">Alamat aplikasi belum tersedia.</p>
                    @endif
                </article>
            @empty
                <div class="col-span-full text-center rounded-xl border border-dashed border-gray-300 p-12"><h3 class="font-semibold">Belum ada aplikasi terhubung</h3><p class="text-sm text-gray-500 mt-2">{{ auth()->user()->isOmniAdmin() ? 'Daftarkan aplikasi melalui menu OAuth Clients untuk menampilkannya di sini.' : 'Hubungi superadmin untuk menghubungkan aplikasi ke portal SSO.' }}</p></div>
            @endforelse
        </div>
    </section>
</x-omni::layouts.dashboard>
