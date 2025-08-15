@extends('layouts.app')

@section('title', 'Coverage Areas')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Coverage Areas</h1>
            <p class="text-sm text-gray-600">Select municipalities (Hoofdgemeente). All towns inside are covered automatically.</p>
        </div>
        <a href="{{ route('plumber.dashboard') }}"
           class="inline-flex items-center rounded-md bg-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-900">
            Back to Dashboard
        </a>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add new municipality --}}
    <div class="lg:col-span-1 bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6 ">
        <h2 class="text-sm font-semibold text-gray-900">Add Municipality</h2>
        <p class="text-xs text-gray-500 mt-1">Type to search Hoofdgemeente names.</p>

        <form method="POST" action="{{ route('plumber.coverage.store') }}" class="mt-4 text-black" id="coverage-form">
            @csrf
            <label class="block text-sm font-medium text-gray-700 mb-1">Hoofdgemeente</label>
            <input id="municipality-input" name="hoofdgemeente" type="text" placeholder="Start typing…"
                   class="w-full border rounded px-3 py-2 text-gray-900" autocomplete="off" required>

            <div id="municipality-results" class="mt-2 hidden rounded-md border border-gray-200 divide-y bg-white max-h-60 overflow-auto"></div>

            <button type="submit"
                    class="mt-3 inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
                Add Coverage
            </button>
        </form>

        <div class="mt-4">
            <h3 class="text-sm font-semibold text-gray-900">Preview towns</h3>
            <p class="text-xs text-gray-500">Shows towns included for the selected municipality.</p>
            <div id="towns-preview" class="mt-2 text-sm text-gray-700 space-y-1 max-h-56 overflow-auto"></div>
        </div>
    </div>

    {{-- Current coverages --}}
    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-900">Your Municipalities</h2>

        @if (session('success'))
            <div class="mt-3 rounded-md bg-green-50 p-3 ring-1 ring-green-600/20 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mt-3 rounded-md bg-red-50 p-3 ring-1 ring-red-600/20 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($coverages->isEmpty())
            <p class="mt-4 text-sm text-gray-600">You have not added any municipalities yet.</p>
        @else
            <ul class="mt-4 divide-y">
                @foreach ($coverages as $cov)
                    <li class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">{{ $cov->hoofdgemeente }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $counts[$cov->hoofdgemeente] ?? 0 }} towns covered
                            </div>
                        </div>
                        <form method="POST" action="{{ route('plumber.coverage.destroy', $cov->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                                Remove
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<script>
const input   = document.getElementById('municipality-input');
const results = document.getElementById('municipality-results');
const towns   = document.getElementById('towns-preview');

let debounceTimer;

input.addEventListener('input', () => {
    const q = input.value.trim();
    clearTimeout(debounceTimer);
    if (q.length < 2) { results.classList.add('hidden'); towns.innerHTML=''; return; }

    debounceTimer = setTimeout(async () => {
        const res = await fetch(`{{ route('municipalities.search') }}?term=${encodeURIComponent(q)}`);
        const data = await res.json();
        if (!data.length) { results.classList.add('hidden'); return; }

        results.innerHTML = data.map(name =>
            `<button type="button" class="w-full text-left px-3 py-2 hover:bg-gray-50" data-name="${name}">${name}</button>`
        ).join('');
        results.classList.remove('hidden');

        // wire click
        results.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', async () => {
                const name = btn.dataset.name;
                input.value = name;
                results.classList.add('hidden');

                // preview towns
                towns.innerHTML = 'Loading...';
                const res2 = await fetch(`{{ url('/municipalities') }}/${encodeURIComponent(name)}/towns`);
                const items = await res2.json();
                towns.innerHTML = items.map(i => `<div>${i.Postcode} – ${i.Plaatsnaam_NL}</div>`).join('');
            });
        });
    }, 200);
});
</script>
@endsection
