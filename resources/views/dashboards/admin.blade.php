@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="text-sm text-gray-600">
                Welcome, {{ Auth::user()->full_name ?? Auth::user()->name }} — manage plumbers, clients, WhatsApp, and flows.
            </p>
        </div>
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
           Account Settings
        </a>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 ring-1 ring-green-600/20 text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4 ring-1 ring-red-600/20 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 ">
        {{-- Plumbers --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5 ">
            <div class="flex items-center justify-between text-black">
                <h2 class="text-sm font-semibold text-gray-900">Plumbers</h2>
                <span class="text-xs text-gray-500">CRUD</span>
            </div>
            <p class="mt-1 text-sm text-gray-600">
                Create, update and manage plumbers, service areas, tariffs and availability.
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('plumbers.index') }}"
                   class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
                    View all
                </a>
                <a href="{{ route('plumbers.create') }}"
                   class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Add new
                </a>
            </div>
        </div>

        {{-- Clients --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Clients</h2>
                <span class="text-xs text-gray-500">CRUD</span>
            </div>
            <p class="mt-1 text-sm text-gray-600">Manage registered clients and their requests history.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('clients.index') }}"
                   class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
                    View all
                </a>
            </div>
        </div>

        {{-- Requests --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900">Requests</h2>
            <p class="mt-1 text-sm text-gray-600">Oversee incoming job requests.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('requests.index') }}"
                   class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Open list
                </a>
            </div>
        </div>

        {{-- WhatsApp Connection --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">WhatsApp Connection</h2>
                <span id="wa-status" class="text-xs text-gray-500">Checking…</span>
            </div>
            <p class="mt-1 text-sm text-gray-600">
                Scan the QR to link your WhatsApp session. Keep the Node bot running.
            </p>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="rounded-md border border-dashed border-gray-300 p-3 flex items-center justify-center min-h-[220px]">
                        <img id="wa-qr" src="" alt="QR Code" class="hidden max-h-[260px]">
                        <div id="wa-qr-placeholder" class="text-sm text-gray-500">QR will appear here…</div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button id="btn-refresh-qr"
                                class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
                            Refresh QR
                        </button>
                        <button id="btn-check-status"
                                class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Check Status
                        </button>
                        <a href="{{ route('admin.whatsapp') }}"
                           class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Open WhatsApp page
                        </a>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="rounded-md bg-gray-50 p-3">
                        <h3 class="text-sm font-semibold text-gray-900">Bot URL</h3>
                        <p class="text-sm text-gray-600">
                            Configured in <code>.env</code> as <code>WHATSAPP_BOT_URL</code>.
                        </p>
                        <code class="text-xs text-gray-500">
                            {{ config('services.whatsapp.bot_url') ?? 'http://127.0.0.1:3000' }}
                        </code>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3">
                        <h3 class="text-sm font-semibold text-gray-900">Tips</h3>
                        <ul class="text-sm text-gray-600 list-disc ms-5">
                            <li>Run the Node bot: <code>node whatsapp-bot/index.js</code></li>
                            <li>Keep the terminal open; re-scan QR if needed.</li>
                            <li>Use one number in production; don’t multi-login.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Custom Messages (Flows) --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900">Custom Messages</h2>
            <p class="mt-1 text-sm text-gray-600">Edit interactive WhatsApp flows and nodes.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.admin.flows.index') }}"
                   class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
                   Manage Flows
                </a>
            </div>
        </div>
    </div>

    {{-- Dashboard QR/Status JS (uses Laravel proxy endpoints) --}}
    <script>
        const qrImg = document.getElementById('wa-qr');
        const qrPH  = document.getElementById('wa-qr-placeholder');
        const statusEl = document.getElementById('wa-status');

        async function getQR() {
            try {
                const res = await fetch('{{ route('admin.whatsapp.qr') }}');
                const data = await res.json();
                if (data.qr) {
                    qrImg.src = data.qr;
                    qrImg.classList.remove('hidden');
                    qrPH.classList.add('hidden');
                    statusEl.textContent = 'Awaiting scan';
                } else {
                    qrImg.classList.add('hidden');
                    qrPH.classList.remove('hidden');
                    statusEl.textContent = data.message || 'Connected';
                }
            } catch (e) {
                statusEl.textContent = 'QR fetch failed';
            }
        }

        async function getStatus() {
            try {
                const res = await fetch('{{ route('admin.whatsapp.status') }}');
                const data = await res.json();
                statusEl.textContent = data.status || 'Unknown';
            } catch (e) {
                statusEl.textContent = 'Status check failed';
            }
        }

        document.getElementById('btn-refresh-qr')?.addEventListener('click', getQR);
        document.getElementById('btn-check-status')?.addEventListener('click', getStatus);

        // initial load
        getQR();
        getStatus();
    </script>
@endsection
