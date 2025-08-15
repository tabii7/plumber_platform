@extends('layouts.app')

@section('title','Edit Node')

@section('header')
  <h1 class="text-2xl font-bold text-gray-900">Edit Node — {{ $flow->name }}</h1>
@endsection

@section('content')
  <form method="POST" action="{{ route('admin.flows.nodes.update', [$flow, $node]) }}" class="space-y-4 text-gray-600">
    @csrf @method('PUT')
    @include('admin.nodes.partials.form', ['node'=>$node])
    <button class="bg-cyan-600 text-white px-4 py-2 rounded-md hover:bg-cyan-700">Update</button>
  </form>
@endsection
