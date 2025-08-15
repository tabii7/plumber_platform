@extends('layouts.app')

@section('title', 'WhatsApp Connection')

@section('header')
    <h1 class="text-2xl font-bold text-gray-900">WhatsApp Connection</h1>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Left: Status/QR or Connected --}}
    <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6">
        <p class="text-sm text-gray-600">Status: <strong>{{ $status }}</strong></p>

        @if (! $connected)
            @if ($qr)
                <p class="mt-3 text-gray-700">
                    📲 Scan this QR (WhatsApp → Linked devices → Link a device):
                </p>
                <div class="mt-4 inline-flex rounded-md border border-dashed border-gray-300 p-3">
                    <img src="{{ $qr }}" alt="WhatsApp QR Code" class="max-h-[280px] rounded">
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">
                    Waiting for QR… keep this page open and click <strong>Refresh</strong> after a few seconds.
                </p>
            @endif
            <div class="mt-4">
                <a href="{{ route('admin.whatsapp') }}"
                   class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
                    Refresh
                </a>
            </div>
        @else
            <div class="mt-4 rounded-md bg-green-50 p-3 ring-1 ring-green-600/20 text-green-800">
                ✅ WhatsApp is connected.
            </div>
        @endif

        <p class="mt-3 text-xs text-gray-500">Bot URL: <code>{{ $bot }}</code></p>
    </div>

    {{-- Right: Test form only when connected --}}
    <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-900">Send Test Message</h2>
        <p class="mt-1 text-sm text-gray-600">Quickly verify your bot can send messages.</p>

        @if ($connected)
            <form method="POST" action="{{ url('/api/whatsapp/send') }}" class="mt-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                    <input type="text" name="number" placeholder="e.g. 32470123456"
                        class="w-full border rounded px-3 py-2 text-gray-900" required>
                </div>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea name="message" rows="3" placeholder="Type your test message"
                        class="w-full border rounded px-3 py-2 text-gray-900" required></textarea>
                </div>
                <button type="submit"
                        class="mt-4 inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                    Send Test Message
                </button>
            </form>
        @else
            <p class="mt-4 text-xs text-gray-500">
                Connect first to enable sending test messages.
            </p>
        @endif
    </div>
</div>
@endsection
