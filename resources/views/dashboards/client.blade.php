@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-10">
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold text-gray-800">Client Dashboard</h1>
        <p class="mt-3 text-gray-600">Welkom, klant! Hier kun je loodgieters aanvragen en je profiel beheren.</p>
        
        <ul class="mt-5 space-y-3">
            <li><a href="{{ route('requests.create') }}" class="text-blue-600 hover:underline">Nieuwe aanvraag indienen</a></li>
            <li><a href="{{ route('requests.index') }}" class="text-blue-600 hover:underline">Bekijk je aanvragen</a></li>
        </ul>
    </div>
</div>
@endsection
