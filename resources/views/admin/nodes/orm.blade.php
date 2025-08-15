@extends('layouts.app')
@section('title', ($node->exists?'Edit':'New').' Node — '.$flow->name)
@section('header')
  <h1 class="text-2xl font-bold text-gray-900">{{ $node->exists?'Edit':'New' }} Node — {{ $flow->name }}</h1>
@endsection
@section('content')
@if ($errors->any())
  <div class="mb-4 rounded bg-red-50 p-3 text-red-800 ring-1 ring-red-600/20">
    <ul class="list-disc pl-5">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif
<form method="POST" action="{{ $node->exists ? route('admin.flows.nodes.update', [$flow,$node]) : route('admin.flows.nodes.store',$flow) }}" class="space-y-4">
  @csrf
  @if($node->exists) @method('PUT') @endif

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">Code</label>
      <input name="code" value="{{ old('code',$node->code) }}" class="w-full border rounded p-2" required>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Type</label>
      <select name="type" class="w-full border rounded p-2">
        @foreach(['text','buttons','list','collect_text','dispatch'] as $t)
          <option value="{{ $t }}" @selected(old('type',$node->type)===$t)>{{ $t }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Sort</label>
      <input type="number" name="sort" value="{{ old('sort',$node->sort ?? 0) }}" class="w-full border rounded p-2" min="0">
    </div>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Title</label>
    <input name="title" value="{{ old('title',$node->title) }}" class="w-full border rounded p-2">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Body</label>
    <textarea name="body" rows="4" class="w-full border rounded p-2">{{ old('body',$node->body) }}</textarea>
    <p class="text-xs text-gray-500 mt-1">Supports placeholders like <code>{{'{{first_name}}'}}</code>, <code>{{'{{postal_code}}'}}</code>, <code>{{'{{city}}'}}</code>, <code>{{'{{status}}'}}</code>.</p>
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Footer</label>
    <input name="footer" value="{{ old('footer',$node->footer) }}" class="w-full border rounded p-2">
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Options JSON (for buttons/list)</label>
    <textarea name="options_json" rows="6" class="w-full border rounded p-2" placeholder='[{"id":"yes","text":"YES"},{"id":"no","text":"NO"}]'>{{ old('options_json', $node->options_json? json_encode($node->options_json, JSON_PRETTY_PRINT):'') }}</textarea>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Next Map JSON</label>
    <textarea name="next_map_json" rows="4" class="w-full border rounded p-2" placeholder='{"yes":"next_node_code","no":"goodbye","default":"fallback"}'>{{ old('next_map_json', $node->next_map_json? json_encode($node->next_map_json, JSON_PRETTY_PRINT):'') }}</textarea>
  </div>

  <div class="flex items-center gap-3">
    <button class="rounded bg-blue-600 text-white px-4 py-2">{{ $node->exists ? 'Save' : 'Create' }}</button>
    <a href="{{ route('admin.flows.nodes.index',$flow) }}" class="text-gray-700 hover:underline">Back to nodes</a>
  </div>
</form>
@endsection
