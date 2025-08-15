@extends('layouts.app')

@section('title', 'Add Plumber')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Plumber</h1>
            <p class="text-sm text-gray-600">Create a new plumber account and set service areas & categories.</p>
        </div>
        <a href="{{ route('plumbers.index') }}"
           class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Back to list
        </a>
    </div>
@endsection

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-4 ring-1 ring-red-600/20 text-red-800">
            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('plumbers.store') }}" class="space-y-6 text-gray-600">
        @csrf

        {{-- User details --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900">Account</h2>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full name</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">WhatsApp number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Postal code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Country</label>
                    <input type="text" name="country" value="{{ old('country', 'Belgium') }}"
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
                    <input type="number" step="0.01" min="0" name="tariff" value="{{ old('tariff') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Availability</label>
                    <select name="availability_status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                        <option value="available" {{ old('availability_status') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="busy" {{ old('availability_status') === 'busy' ? 'selected' : '' }}>Busy (not working)</option>
                        <option value="holiday" {{ old('availability_status') === 'holiday' ? 'selected' : '' }}>On holiday (not working)</option>
                    </select>
                </div>
            </div>

            {{-- Municipalities (Hoofdgemeente) --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Municipalities (Hoofdgemeente)</label>
                <p class="text-xs text-gray-500">Selecting a municipality automatically covers all towns within it.</p>
                @if(isset($municipalities) && count($municipalities))
                    <select name="municipalities[]" multiple
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 min-h-[160px]">
                        @foreach($municipalities as $m)
                            <option value="{{ $m }}" @selected(collect(old('municipalities', []))->contains($m))>{{ $m }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="municipalities_csv" value="{{ old('municipalities_csv') }}"
                           placeholder="Type comma-separated municipalities e.g. Brugge, Oostkamp"
                           class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                    <p class="text-xs text-gray-500 mt-1">Tip: pass $municipalities to render a multi-select.</p>
                @endif
            </div>

            {{-- Categories --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Service Categories</label>
                <p class="text-xs text-gray-500">Select all categories this plumber can handle.</p>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @forelse($categories ?? [] as $cat)
   
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-600"
                                   @checked(in_array($cat->id, old('categories', [])))>
                            <span>{{ $cat->label }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500">No categories found. Seed them first.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700" type="submit">
                Save plumber
            </button>
            <a href="{{ route('plumbers.index') }}"
               class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
