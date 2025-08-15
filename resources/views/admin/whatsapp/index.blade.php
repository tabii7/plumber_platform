@extends('layouts.app')

@section('title','WhatsApp Connection')

@section('header')
    <h1 class="text-2xl font-bold text-gray-900">WhatsApp Connection</h1>
@endsection

@section('content')
@if(session('success'))
    <div class="mb-4 rounded bg-green-50 p-3 text-green-800 ring-1 ring-green-600/20">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded bg-red-50 p-3 text-red-800 ring-1 ring-red-600/20">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="rounded-lg bg-white ring-1 ring-gray-200 p-5">
        <h3 class="font-semibold text-gray-900">Status</h3>
        <div class="mt-2 text-sm text-gray-700">
            <pre class="bg-gray-50 p-3 rounded">{{ json_encode($status, JSON_PRETTY_PRINT) }}</pre>
        </div>

        <div class="mt-4">
            @if ($qr)
                <p class="text-gray-700 mb-2">📲 Scan this QR with WhatsApp to connect:</p>
                <img src="{{ $qr }}" alt="QR" class="mx-auto border rounded shadow">
            @else
                <p class="text-green-700 font-medium">✅ Already connected (or QR not available yet).</p>
            @endif
        </div>
    </div>

    <div class="rounded-lg bg-white ring-1 ring-gray-200 p-5">
        <h3 class="font-semibold text-gray-900">Send Test Message</h3>
        <form method="POST" action="{{ route('admin.whatsapp.testSend') }}" class="mt-3 space-y-3">
            @csrf
            <input name="number" class="w-full border rounded p-2" placeholder="e.g. 32470123456" required>
            <textarea name="message" class="w-full border rounded p-2" rows="4" placeholder="Type a message..." required></textarea>
            <button class="inline-flex items-center rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Send</button>
        </form>
    </div>
</div>
@endsection
