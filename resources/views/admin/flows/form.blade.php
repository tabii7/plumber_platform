@extends('layouts.app')
@section('title', $flow->exists ? 'Edit Flow' : 'New Flow')
@section('header')
  <h1 class="text-2xl font-bold text-gray-900">{{ $flow->exists ? 'Edit Flow' : 'New Flow' }}</h1>
@endsection
@section('content')
@if ($errors->any())
  <div class="mb-4 rounded bg-red-50 p-3 text-red-800 ring-1 ring-red-600/20">
    <ul class="list-disc pl-5">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif
<form method="POST" action="{{ $flow->exists ? route('flows.update',$flow) : route('flows.store') }}" class="space-y-4 text-gray-600">
  @csrf
  @if($flow->exists) @method('PUT') @endif

  <div>
    <label class="block text-sm font-medium text-gray-700">Code</label>
    <input name="code" value="{{ old('code',$flow->code) }}" class="w-full border rounded p-2" required>
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Name</label>
    <input name="name" value="{{ old('name',$flow->name) }}" class="w-full border rounded p-2" required>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">Entry keyword</label>
      <input name="entry_keyword" value="{{ old('entry_keyword',$flow->entry_keyword) }}" class="w-full border rounded p-2" placeholder="info / plumber">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Target role</label>
      <select name="target_role" class="w-full border rounded p-2">
        @foreach(['client','plumber','any'] as $r)
          <option value="{{ $r }}" @selected(old('target_role',$flow->target_role)===$r)>{{ ucfirst($r) }}</option>
        @endforeach
      </select>
    </div>
    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active',$flow->is_active))>
      <span class="text-sm text-gray-700">Active</span>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <button class="rounded bg-blue-600 text-white px-4 py-2">{{ $flow->exists ? 'Save' : 'Create' }}</button>
    @if($flow->exists)
      <a href="{{ route('admin.flows.nodes.index',$flow) }}" class="text-blue-600 hover:underline">Manage Nodes →</a>
    @endif
  </div>
</form>
@endsection
