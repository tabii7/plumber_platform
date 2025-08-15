@extends('layouts.app')

@section('title', 'Service Categories')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Service Categories</h1>
            <p class="text-sm text-gray-600">Select all categories you can handle. You’ll only receive jobs from these categories.</p>
        </div>
        <a href="{{ route('plumber.dashboard') }}"
           class="inline-flex items-center rounded-md bg-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-900">
            Back to Dashboard
        </a>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 ring-1 ring-green-600/20 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('plumber.categories.update') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($categories as $group => $items)
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-900">{{ $group }}</h2>
                    <div class="mt-3 space-y-2">
                        @foreach ($items as $cat)
                            <label class="flex items-start gap-3">
                                <input type="checkbox"
                                       name="categories[]"
                                       value="{{ $cat->id }}"
                                       class="mt-1 h-4 w-4 rounded border-gray-300 text-cyan-600 focus:ring-cyan-600"
                                       {{ in_array($cat->id, $selected, true) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-800">{{ $cat->label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700">
                Save Categories
            </button>
        </div>
    </form>
@endsection
