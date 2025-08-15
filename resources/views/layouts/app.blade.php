<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- App assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body{
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
            background:
                radial-gradient(1200px 600px at -10% -10%, #1f2954 0%, transparent 60%),
                radial-gradient(800px 500px at 110% 0%, #0c6775 0%, transparent 55%),
                linear-gradient(180deg, #0a0f1e 0%, #0c1327 100%);
            min-height: 100vh;
        }
        .glass { background: rgba(255,255,255,.95); backdrop-filter: blur(6px); }
        .admin-grid { display: grid; grid-template-columns: 260px 1fr; gap: 16px; }
        @media (max-width: 1024px){ .admin-grid { grid-template-columns: 1fr; } }
        .side-link { display:flex; align-items:center; gap:8px; padding:8px 12px; border-radius:8px; font-size:14px; font-weight:600; color:#374151; text-decoration:none; }
        .side-link:hover { background:#f9fafb; }
        .side-link.active { background:#ecfeff; color:#0e7490; outline:1px solid rgba(14,116,144,.2); }
        .side-section { margin-top:12px; }
        .side-title { font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; margin:12px 0 6px; }
    </style>
</head>
<body class="text-slate-100">
    <div class="min-h-screen">
        {{-- Top Navigation (role-aware) --}}
        @include('layouts.navigation')

        {{-- Page Heading (optional) --}}
        @hasSection('header')
            <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="glass shadow-sm ring-1 ring-gray-200 rounded-lg">
                    <div class="px-4 sm:px-6 lg:px-8 py-5">
                        @yield('header')
                    </div>
                </div>
            </header>
        @endif

        {{-- Flash messages --}}
        @if (session('success') || session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                @if (session('success'))
                    <div class="rounded-md bg-green-50 p-4 ring-1 ring-green-600/20 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="rounded-md bg-red-50 p-4 ring-1 ring-red-600/20 text-red-800 mt-3">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        @endif

        @php
            $user = Illuminate\Support\Facades\Auth::user();
            $role = $user->role ?? null;
            $isAdmin = $role === 'admin';
        @endphp

        {{-- Page Body --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-6">
            @if ($isAdmin)
                {{-- Admin: sidebar + content --}}
                <div class="admin-grid">
                    {{-- Sidebar --}}
                    <aside class="glass ring-1 ring-gray-200 rounded-lg p-4 h-fit">
                        {{-- Custom sidebar hook (optional) --}}
                        @hasSection('sidebar')
                            @yield('sidebar')
                        @else
                            {{-- Default Admin menu wired to YOUR routes --}}
                            <a href="{{ route('admin.dashboard') }}"
                               class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0L2 8.586V18a2 2 0 002 2h4a1 1 0 001-1v-4h2v4a1 1 0 001 1h4a2 2 0 002-2V8.586l-7.293-7.293z"/></svg>
                                Dashboard
                            </a>

                            <div class="side-section">
                                <div class="side-title">Management</div>
                                {{-- Plumbers resource -> names are plumbers.* (points to /admin/plumbers) --}}
                                <a href="{{ route('plumbers.index') }}"
                                   class="side-link {{ request()->routeIs('plumbers.*') ? 'active' : '' }}">
                                    Plumbers
                                </a>

                                {{-- Admin Requests: use URL to avoid name collision with client requests --}}
                                <a href="{{ url('/admin/requests') }}"
                                   class="side-link {{ request()->is('admin/requests*') ? 'active' : '' }}">
                                    Requests
                                </a>
                            </div>

                            <div class="side-section">
                                <div class="side-title">WhatsApp</div>
                                <a href="{{ route('admin.whatsapp') }}"
                                   class="side-link {{ request()->routeIs('admin.whatsapp') ? 'active' : '' }}">
                                    Connection
                                </a>
                            </div>

                            <div class="side-section">
                                <div class="side-title">Account</div>
                                <a href="{{ route('profile.edit') }}"
                                   class="side-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                                    Profile
                                </a>
                            </div>
                        @endif
                    </aside>

                    {{-- Main content --}}
                    <div class="glass shadow-sm ring-1 ring-gray-200 rounded-lg">
                        <div class="px-4 sm:px-6 lg:px-8 py-6">
                            @yield('content')
                        </div>
                    </div>
                </div>
            @else
                {{-- Client/Plumber/Guest: single-column card --}}
                <main class="glass shadow-sm ring-1 ring-gray-200 rounded-lg">
                    <div class="px-4 sm:px-6 lg:px-8 py-6">
                        @yield('content')
                    </div>
                </main>
            @endif
        </div>
    </div>
</body>
</html>
