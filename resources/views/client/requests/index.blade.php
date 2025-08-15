@extends('layouts.app')

@section('header')
    <h1>My Requests</h1>
@endsection

@section('content')
    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('requests.create') }}" class="bg-blue-600 text-white px-3 py-2 rounded">New Request</a>

    <table class="min-w-full mt-4 bg-white border">
        <thead>
            <tr>
                <th class="px-4 py-2 border">Service</th>
                <th class="px-4 py-2 border">Description</th>
                <th class="px-4 py-2 border">Status</th>
                <th class="px-4 py-2 border">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $req)
                <tr>
                    <td class="px-4 py-2 border">{{ $services[$req->service_id] ?? 'Unknown' }}</td>
                    <td class="px-4 py-2 border">{{ $req->description }}</td>
                    <td class="px-4 py-2 border">{{ ucfirst($req->status) }}</td>
                    <td class="px-4 py-2 border">{{ $req->created_at->format('d-m-Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
