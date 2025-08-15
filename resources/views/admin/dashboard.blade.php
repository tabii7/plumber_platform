@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('header')
    <h1 class="text-black">Admin Dashboard</h1>
@endsection

@section('content')
    <div class="grid grid-cols-3 gap-4 text-black">
        <div class="bg-white p-4 shadow rounded">
            <h3>Total Clients: {{ $clients }}</h3>
        </div>
        <div class="bg-white p-4 shadow rounded">
            <h3>Total Plumbers: {{ $plumbers }}</h3>
        </div>
        <div class="bg-white p-4 shadow rounded">
            <h3>Total Requests: {{ $requests }}</h3>
        </div>
    </div>
@endsection
