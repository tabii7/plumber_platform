@extends('layouts.app')

@section('title', 'Plumbers')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Plumbers</h1>
            <p class="text-sm text-gray-600">Manage all plumbers, their service areas, categories, and availability.</p>
        </div>
        <a href="{{ route('plumbers.create') }}"
           class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
            Add Plumber
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

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-gray-600">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone / WhatsApp</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postal Areas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tariff</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categories</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($plumbers as $plumber)
                    @php
                        // User
                        $u = $plumber->user ?? null;
                        $name = $u->full_name ?? $u->name ?? '—';
                        $phone = $u->phone ?? '—';
                        $wa = $u->whatsapp_number ?? '—';

                        // Postal areas (string, array or relation)
                        $areas = $plumber->postal_codes ?? $plumber->municipalities ?? null;
                        if (is_array($areas)) {
                            $areasText = implode(', ', $areas);
                        } elseif (is_string($areas)) {
                            $areasText = $areas;
                        } else {
                            // if you have a relation like $plumber->postalCodes, show first few
                            $areasText = method_exists($plumber, 'postalCodes')
                                ? $plumber->postalCodes->pluck('Postcode')->take(3)->implode(', ') .
                                  ($plumber->postalCodes->count() > 3 ? ' +' . ($plumber->postalCodes->count() - 3) . ' more' : '')
                                : '—';
                        }

                        // Tariff
                        $tariff = $plumber->tariff ? '€' . number_format((float)$plumber->tariff, 2) : '—';

                        // Categories (JSON of names, or pivot)
                        $catsRaw = $plumber->service_categories ?? null;
                        if (is_string($catsRaw)) {
                            $decoded = json_decode($catsRaw, true);
                            $categories = is_array($decoded) ? implode(', ', $decoded) : $catsRaw;
                        } elseif (is_array($catsRaw)) {
                            $categories = implode(', ', $catsRaw);
                        } elseif (method_exists($plumber, 'categories')) {
                            $categories = $plumber->categories->pluck('name')->implode(', ');
                        } else {
                            $categories = '—';
                        }

                        // Status badge
                        $status = $plumber->availability_status ?? 'unknown';
                        $badgeClasses = match($status) {
                            'available' => 'bg-green-100 text-green-800 ring-green-600/20',
                            'busy', 'holiday' => 'bg-red-100 text-red-800 ring-red-600/20',
                            default => 'bg-gray-100 text-gray-800 ring-gray-600/20'
                        };
                        $statusLabel = $status === 'available' ? 'Available' :
                                       (in_array($status, ['busy','holiday']) ? 'Not working' : 'Unknown');
                    @endphp

                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="text-gray-900">{{ $phone }}</div>
                            <div class="text-gray-500 text-xs">WA: {{ $wa }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $areasText }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $tariff }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $categories }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-semibold ring-1 ring-inset {{ $badgeClasses }}">
                                @if ($status === 'available')
                                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                @elseif (in_array($status, ['busy','holiday']))
                                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                @else
                                    <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                                @endif
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            <a href="{{ route('plumbers.show', $plumber) }}" class="text-blue-600 hover:underline">View</a>
                            <span class="text-gray-300 mx-1">|</span>
                            <a href="{{ route('plumbers.edit', $plumber) }}" class="text-cyan-600 hover:underline">Edit</a>
                            <span class="text-gray-300 mx-1">|</span>
                            <form action="{{ route('plumbers.destroy', $plumber) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this plumber?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-sm text-gray-500" colspan="7">No plumbers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($plumbers, 'links'))
        <div class="mt-4">
            {{ $plumbers->links() }}
        </div>
    @endif
@endsection
