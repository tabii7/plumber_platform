@extends('layouts.app')

@section('title', 'Edit Client')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Client</h1>
        <p class="text-sm text-gray-600">{{ $client->full_name ?? $client->name }}</p>
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

    <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700">Full name</label>
            <input type="text" name="full_name" value="{{ old('full_name', $client->full_name ?? $client->name) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600" required>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">WhatsApp number</label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $client->whatsapp_number) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Postal code</label>
                <input type="text" name="postal_code" value="{{ old('postal_code', $client->postal_code) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">City</label>
                <input type="text" name="city" value="{{ old('city', $client->city) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700" type="submit">
                Save changes
            </button>
            <a href="{{ route('clients.index') }}"
               class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
