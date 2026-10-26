<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Dashboard' }} — {{ config('omni-central-auth.server.app_name') }}</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-omni-50: #fff7ed;
            --color-omni-100: #ffedd5;
            --color-omni-500: #FF6B35;
            --color-omni-600: #ea580c;
            --color-omni-700: #c2410c;
        }
    </style>
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: Inter, 'Segoe UI', sans-serif; background: #f5f6fa; color: #252f4a; }
        aside { background: #1e1e2d !important; width: 265px !important; flex-shrink: 0; }
        aside > div:first-child { padding: 30px 26px; border-color: #303040; }
        aside nav { padding: 28px 18px; }
        aside nav a { padding: 14px 16px; margin-bottom: 7px; font-weight: 500; border-radius: 8px; }
        aside nav a:not(.bg-omni-500) { color: #a2a3b7; }
        aside nav a:hover { background: #2a2a3c; color: white; }
        aside nav a.bg-omni-500 { background: #33303a; color: #ff986c; }
        aside nav svg { width: 20px; height: 20px; }
        main { background: #f5f6fa; }
        main > header { min-height: 90px; border-color: #eef0f5; padding: 22px 36px; }
        main > header h1 { font-size: 20px; letter-spacing: -.5px; }
        main > div { max-width: 1600px; margin: auto; padding: 32px 36px; }
        main .bg-white { border-color: #edf0f5; box-shadow: 0 3px 16px rgba(20, 30, 60, .025); border-radius: 12px; }
        main table th { color: #99a1b7; font-size: 11px; letter-spacing: .6px; text-transform: uppercase; }
        main table td { padding-top: 18px; padding-bottom: 18px; }
        .page-intro { margin-bottom: 28px; }
        .overview-stats > div { border-bottom: 3px solid #e9edf3; }
        .overview-stats > div:nth-child(1) { border-bottom-color: #ff986c; }
        .overview-stats > div:nth-child(2) { border-bottom-color: #7239ea; }
        .overview-stats > div:nth-child(3) { border-bottom-color: #17c653; }
        .overview-stats > div:nth-child(4) { border-bottom-color: #f8285a; }
        .overview-stats .text-3xl { margin-top: 18px; font-size: 36px; letter-spacing: -1px; }
        .page-intro p { color: #99a1b7; font-size: 13px; margin-top: 5px; }
        .workspace-label { color: #646477; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; padding: 0 16px 18px; }
        .mobile-toggle { display: none; }
        .sidebar-overlay { display: none; }
        @media (max-width: 900px) {
            aside { position: fixed; inset: 0 auto 0 0; z-index: 50; transform: translateX(-100%); transition: transform .2s; }
            body.sidebar-open aside { transform: translateX(0); }
            body.sidebar-open .sidebar-overlay { display: block; position: fixed; inset: 0; background: #10102080; z-index: 40; }
            .mobile-toggle { display: inline-flex; margin-right: 12px; padding: 8px; border-radius: 6px; background: #f1f1f4; }
            main > header { padding: 18px 20px; min-height: 76px; }
            main > div { padding: 24px 20px; }
        }
        body aside { background: #eef2ec !important; color: #263e31; border-right: 1px solid #e0e7df; }
        body aside > div:first-child { border-color: #e0e7df; }
        body aside .text-white { color: #263e31; }
        body aside nav a:not(.bg-omni-500) { color: #79847a; }
        body aside nav a:hover { background: #e2e9df; color: #263e31; }
        body aside nav a.bg-omni-500 { background: #263e31; color: #e4efdf; }
        body aside .workspace-label { color: #98a394; }
        body aside > div:last-child { border-color: #e0e7df; }
        body aside > div:last-child .bg-omni-500 { background: #d5e6c8; }
        body main { background: #f7f9f5; }
        body main > header { background: #f7f9f5; border-color: #e6ece3; }
    </style>
</head>

<body class="bg-gray-100">
    <button class="sidebar-overlay" aria-label="Close navigation" onclick="document.body.classList.remove('sidebar-open')"></button>

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="px-6 py-5 border-b border-gray-700">
                <span class="text-white font-bold text-lg tracking-tight">{{ config('omni-central-auth.server.app_name') }}</span>
                <p class="text-gray-400 text-xs mt-2">Identity &amp; Access Management</p>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                <p class="workspace-label">WORKSPACE</p>
                <a href="{{ route('account.settings') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('account.*') ? 'bg-omni-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">⚙ Pengaturan Akun</a>
                @php $prefix = config('omni-central-auth.dashboard.prefix', 'omni-dashboard'); @endphp

                <a href="{{ auth()->user()->isOmniAdmin() ? route('omni.dashboard.index') : route('user.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm
                      {{ (request()->is($prefix) || request()->routeIs('user.dashboard')) ? 'bg-omni-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    Overview
                </a>

                @if (auth()->user()->isOmniAdmin() && config('omni-central-auth.dashboard.features.clients'))
                    <a href="/{{ $prefix }}/clients"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm
                      {{ request()->is($prefix . '/clients*') ? 'bg-omni-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                        </svg>
                        OAuth Clients
                    </a>
                @endif

                @if (auth()->user()->isOmniAdmin() && config('omni-central-auth.dashboard.features.users'))
                    <a href="/{{ $prefix }}/users"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm
                      {{ request()->is($prefix . '/users*') ? 'bg-omni-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                        </svg>
                        Users & Roles
                    </a>
                @endif

                @if (auth()->user()->isOmniAdmin() && config('omni-central-auth.dashboard.features.audit_log'))
                    <a href="/{{ $prefix }}/audit-log"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm
                      {{ request()->is($prefix . '/audit-log*') ? 'bg-omni-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Audit Log
                    </a>
                @endif
            </nav>

            <div class="px-4 py-4 border-t border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-omni-500 flex items-center justify-center text-sm font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 overflow-auto">
            {{-- Top bar --}}
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <button type="button" class="mobile-toggle" aria-label="Open navigation" onclick="document.body.classList.add('sidebar-open')">☰</button>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
                        <p class="text-xs text-gray-400 mt-1">Home <span class="mx-2">/</span> {{ $title ?? 'Dashboard' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-red-500 transition-colors bg-gray-50 px-4 py-2 rounded-lg">
                        Logout
                    </button>
                </form>
            </header>

            <div class="p-8">
                {{-- Flash messages --}}
                @if (session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                <div x-data="{ show: false, message: '' }"
                    x-on:notify.window="
                        message = $event.detail.message;
                        show = true;
                        setTimeout(() => show = false, 3000);
                    ">
                    <div x-show="show" x-cloak
                        class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                        <span x-text="message"></span>
                    </div>
                </div>

                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>

</html>
