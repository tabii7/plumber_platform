@extends('layouts.app')

@section('title', 'Client Details')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Client Details</h1>
        <p class="text-sm text-gray-600">{{ $client->full_name ?? $client->name }}</p>
    </div>
@endsection

@section('content')
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <dt class="text-xs text-gray-500">Email</dt>
            <dd class="text-sm text-gray-900 mt-1">{{ $client->email }}</dd>
        </div>
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <dt class="text-xs text-gray-500">Phone</dt>
            <dd class="text-sm text-gray-900 mt-1">{{ $client->phone ?? '—' }}</dd>
        </div>
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <dt class="text-xs text-gray-500">WhatsApp</dt>
            <dd class="text-sm text-gray-900 mt-1">{{ $client->whatsapp_number ?? '—' }}</dd>
        </div>
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
            <dt class="text-xs text-gray-500">Location</dt>
            <dd class="text-sm text-gray-900 mt-1">
                {{ $client->postal_code ?? '—' }} {{ $client->city ? '• '.$client->city : '' }}
            </dd>
        </div>
    </dl>

    <div class="mt-6 flex gap-2">
        <a href="{{ route('clients.edit', $client) }}"
           class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
            Edit
        </a>
        <a href="{{ route('clients.index') }}"
           class="inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Back to list
        </a>
    </div>
@endsection
