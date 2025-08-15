@extends('layouts.app')

@section('title', 'Clients')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Clients</h1>
            <p class="text-sm text-gray-600">All registered clients in the system.</p>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 ring-1 ring-green-600/20 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">WhatsApp</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postal / City</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($clients as $client)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $client->full_name ?? $client->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $client->email }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $client->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $client->whatsapp_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $client->postal_code ?? '—' }} {{ $client->city ? '• '.$client->city : '' }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('clients.show', $client) }}" class="text-blue-600 hover:underline">View</a>
                            <span class="text-gray-300 mx-1">|</span>
                            <a href="{{ route('clients.edit', $client) }}" class="text-cyan-600 hover:underline">Edit</a>
                            <span class="text-gray-300 mx-1">|</span>
                            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this client?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-sm text-gray-500" colspan="6">No clients found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $clients->links() }}
    </div>
@endsection
