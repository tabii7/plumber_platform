@extends('layouts.app')

@section('title', 'Plumber Dashboard')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Plumber Dashboard</h1>
            <p class="text-sm text-gray-600">Welcome, {{ Auth::user()->full_name ?? Auth::user()->name }} — manage your availability and jobs here.</p>
        </div>

        @php
            $status = Auth::user()->status ?? 'unknown';
            $badge = match($status) {
                'available' => 'bg-green-100 text-green-800 ring-green-600/20',
                'busy'      => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
                'holiday'   => 'bg-blue-100 text-blue-800 ring-blue-600/20',
                default     => 'bg-gray-100 text-gray-800 ring-gray-600/20',
            };
            $dot = match($status) {
                'available' => 'bg-green-500',
                'busy'      => 'bg-yellow-500',
                'holiday'   => 'bg-blue-500',
                default     => 'bg-gray-400',
            };
        @endphp

        <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-semibold ring-1 ring-inset {{ $badge }}">
            <span class="h-2 w-2 rounded-full {{ $dot }}"></span>
            Status: {{ ucfirst($status) }}
        </span>
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

    {{-- Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Quick Status --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900">Quick status</h2>
            <p class="mt-1 text-sm text-gray-600">Update what clients see when matching.</p>

            <div class="mt-4 flex flex-wrap gap-2">
                <form method="POST" action="{{ route('plumber.status.update') }}">
                    @csrf
                    <input type="hidden" name="status" value="available">
                    <button class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700" type="submit">
                        Set Available
                    </button>
                </form>
                <form method="POST" action="{{ route('plumber.status.update') }}">
                    @csrf
                    <input type="hidden" name="status" value="busy">
                    <button class="inline-flex items-center rounded-md bg-yellow-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-600" type="submit">
                        Set Busy
                    </button>
                </form>
                <form method="POST" action="{{ route('plumber.status.update') }}">
                    @csrf
                    <input type="hidden" name="status" value="holiday">
                    <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700" type="submit">
                        On Holiday
                    </button>
                </form>
            </div>

            <p class="mt-3 text-xs text-gray-500">
                Tip: You can also set status via WhatsApp by sending <span class="font-semibold">plumber</span> then choosing 1/2/3.
            </p>
        </div>

        {{-- Coverage Areas --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5 flex flex-col">
            <h2 class="text-sm font-semibold text-gray-900">Coverage Areas</h2>
            <p class="mt-1 text-sm text-gray-600">
                Select municipalities (Hoofdgemeente) you serve. All towns inside are covered automatically.
            </p>

            @php
                // These can be passed from controller; fallbacks keep the view safe.
                $municipalitiesCount = $coverageSummary['municipalities'] ?? null;
                $townsCount          = $coverageSummary['towns'] ?? null;
            @endphp

            @if(!is_null($municipalitiesCount) || !is_null($townsCount))
                <dl class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-md bg-gray-50 p-3 text-center">
                        <dt class="text-xs text-gray-500">Municipalities</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $municipalitiesCount ?? '—' }}</dd>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3 text-center">
                        <dt class="text-xs text-gray-500">Towns Covered</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $townsCount ?? '—' }}</dd>
                    </div>
                </dl>
            @endif

            <div class="mt-4">
                <a href="{{ route('plumber.coverage.index') }}"
                   class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
                    Manage Coverage
                </a>
            </div>
        </div>

        {{-- Shortcuts --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900">Shortcuts</h2>
            <div class="mt-4 space-y-2">
                <a href="{{ route('requests.index') }}" class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2 hover:bg-gray-50">
                    <span class="text-sm font-medium text-gray-700">View Requests</span>
                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </a>
                <a href="{{ route('plumber.coverage.index') }}" class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2 hover:bg-gray-50">
                    <span class="text-sm font-medium text-gray-700">Coverage Areas</span>
                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </a>
                <a href="{{ route('plumber.categories.edit') }}" class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2 hover:bg-gray-50">
                    <span class="text-sm font-medium text-gray-700">Service Categories</span>
                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </a>

                <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2 hover:bg-gray-50">
                    <span class="text-sm font-medium text-gray-700">Edit Profile</span>
                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </a>
            </div>
        </div>

        {{-- My Stats --}}
        <div class="md:col-span-3 bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900">My stats</h2>
            @php
                $pending   = $stats['pending']   ?? 0;
                $accepted  = $stats['accepted']  ?? 0;
                $completed = $stats['completed'] ?? 0;
            @endphp
            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-md bg-gray-50 p-3 text-center">
                    <dt class="text-xs text-gray-500">Pending</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $pending }}</dd>
                </div>
                <div class="rounded-md bg-gray-50 p-3 text-center">
                    <dt class="text-xs text-gray-500">Accepted</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $accepted }}</dd>
                </div>
                <div class="rounded-md bg-gray-50 p-3 text-center">
                    <dt class="text-xs text-gray-500">Completed</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $completed }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Recent Requests (optional) --}}
    @isset($recentRequests)
        <div class="mt-8 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Recent job opportunities</h3>
                <a href="{{ route('requests.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse ($recentRequests as $req)
                    <li class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $req->service ?? 'Service' }}</p>
                            <p class="text-xs text-gray-600">
                                {{ $req->postal_code ?? '—' }} • {{ ucfirst($req->status ?? 'pending') }}
                                <span class="text-gray-400">•</span>
                                {{ $req->created_at?->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('requests.show', $req->id) }}" class="text-sm text-blue-600 hover:underline">Open</a>
                    </li>
                @empty
                    <li class="px-5 py-6 text-sm text-gray-500">No recent requests.</li>
                @endforelse
            </ul>
        </div>
    @endisset
@endsection
