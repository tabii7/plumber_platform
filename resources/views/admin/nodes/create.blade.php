@extends('layouts.app')

@section('title','Create Node')

@section('header')
  <h1 class="text-2xl font-bold text-gray-900">Create Node — {{ $flow->name }}</h1>
@endsection

@section('content')
  <form method="POST" action="{{ route('admin.flows.nodes.store', $flow) }}" class="space-y-4 text-gray-600">
    @csrf
    @include('admin.nodes.partials.form', ['node'=>$node])
    <button class="bg-cyan-600 text-white px-4 py-2 rounded-md hover:bg-cyan-700">Save</button>
  </form>
@endsection
