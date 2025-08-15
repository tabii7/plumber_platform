@extends('layouts.app')

@section('title','Create Flow')

@section('header')
  <h1 class="text-2xl font-bold text-gray-900">Create Flow</h1>
@endsection

@section('content')
  <form method="POST" action="{{ route('admin.flows.store') }}" class="space-y-4 text-gray-600">
    @csrf
    @include('admin.flows.partials.form', ['flow' => $flow])
    <button class="bg-cyan-600 text-white px-4 py-2 rounded-md hover:bg-cyan-700">Save</button>
  </form>
@endsection
