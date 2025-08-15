@extends('layouts.app')

@section('header')
    <h1>New Plumbing Request</h1>
@endsection

@section('content')
    <form action="{{ route('requests.store') }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf
        <div class="mb-4">
            <label for="service_id" class="block font-medium text-gray-700">Select Service</label>
            <select name="service_id" id="service_id" class="w-full border rounded px-3 py-2">
                @foreach($services as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="description" class="block font-medium text-gray-700">Describe the issue</label>
            <textarea name="description" id="description" rows="4" class="w-full border rounded px-3 py-2"></textarea>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Submit Request
        </button>
    </form>
@endsection
