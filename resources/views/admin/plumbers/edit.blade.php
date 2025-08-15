@extends('layouts.app')

@section('title', 'Edit Plumber')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Plumber</h1>
            <p class="text-sm text-gray-600">{{ $plumber->user->full_name ?? $plumber->user->name ?? 'Plumber' }}</p>
        </div>
        <a href="{{ route('plumbers.index') }}"
           class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Back to list
        </a>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 ring-1 ring-green-600/20 text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-4 ring-1 ring-red-600/20 text-red-800">
            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('plumbers.update', $plumber) }}" class="space-y-6 text-gray-600">
        @csrf @method('PUT')

        {{-- Account --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900">Account</h2>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full name</label>
                    <input type="text" name="full_name"
                           value="{{ old('full_name', $plumber->user->full_name ?? $plumber->user->name) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $plumber->user->email) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $plumber->user->phone) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">WhatsApp number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $plumber->user->whatsapp_number) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Postal code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $plumber->user->postal_code) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" name="city" value="{{ old('city', $plumber->user->city) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Country</label>
                    <input type="text" name="country" value="{{ old('country', $plumber->user->country ?? 'Belgium') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
            </div>
        </div>

        {{-- Plumber settings --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900">Plumber Settings</h2>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hourly tariff (€)</label>
                    <input type="number" step="0.01" min="0" name="tariff"
                           value="{{ old('tariff', $plumber->tariff) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Availability</label>
                    <select name="availability_status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                        @php $avail = old('availability_status', $plumber->availability_status) @endphp
                        <option value="available" @selected($avail === 'available')>Available</option>
                        <option value="busy" @selected($avail === 'busy')>Busy (not working)</option>
                        <option value="holiday" @selected($avail === 'holiday')>On holiday (not working)</option>
                    </select>
                </div>
            </div>

            {{-- Municipalities --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Municipalities (Hoofdgemeente)</label>
                <p class="text-xs text-gray-500">Selecting a municipality automatically covers all towns within it.</p>
                @php
                    $selectedMunicipalities = collect(old('municipalities', $plumber->municipalities ?? []))->map(fn($v)=> (string)$v);
                @endphp

                @if(isset($municipalities) && count($municipalities))
                    <select name="municipalities[]" multiple
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 min-h-[160px]">
                        @foreach($municipalities as $m)
                            <option value="{{ $m }}" @selected($selectedMunicipalities->contains($m))>{{ $m }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="municipalities_csv"
                           value="{{ old('municipalities_csv', $selectedMunicipalities->implode(', ')) }}"
                           placeholder="Type comma-separated municipalities e.g. Brugge, Oostkamp"
                           class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                    <p class="text-xs text-gray-500 mt-1">Tip: pass $municipalities to render a multi-select.</p>
                @endif
            </div>

            {{-- Categories --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Service Categories</label>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @php
                        // try relation; fallback to JSON on model
                        $selectedCats = collect(old('categories', isset($plumber->categories)
                            ? $plumber->categories->pluck('id')->all()
                            : (is_string($plumber->service_categories ?? null)
                                ? (json_decode($plumber->service_categories, true) ?? [])
                                : ($plumber->service_categories ?? []))));
                    @endphp
                    @forelse($categories ?? [] as $cat)
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-600"
                                   @checked($selectedCats->contains($cat->id))>
                            <span>{{ $cat->name }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500">No categories found. Seed them first.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700" type="submit">
                Save changes
            </button>
            <a href="{{ route('plumbers.index') }}"
               class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
