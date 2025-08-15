@extends('layouts.app')

@section('title','Nodes: '.$flow->name)

@section('header')
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Nodes — {{ $flow->name }}</h1>
      <p class="text-sm text-gray-600">Define steps, options, and next-node routing.</p>
    </div>
    <a href="{{ route('admin.flows.nodes.create', $flow) }}" class="bg-cyan-600 text-white px-3 py-2 rounded-md hover:bg-cyan-700">New Node</a>
  </div>
@endsection

@section('content')
  @if(session('success'))
    <div class="mb-4 rounded-md bg-green-50 p-3 text-green-800">{{ session('success') }}</div>
  @endif

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Sort</th>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Code</th>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Type</th>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Title</th>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Preview</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white text-black">
        @forelse($nodes as $n)
          <tr>
            <td class="px-3 py-2 w-16">{{ $n->sort }}</td>
            <td class="px-3 py-2 font-mono text-xs">{{ $n->code }}</td>
            <td class="px-3 py-2">{{ $n->type }}</td>
            <td class="px-3 py-2">{{ $n->title }}</td>
            <td class="px-3 py-2">
              <pre class="text-xs bg-gray-50 p-2 rounded max-w-lg overflow-x-auto">{{ Str::limit(($n->body ?? ''), 120) }}</pre>
            </td>
            <td class="px-3 py-2 text-right space-x-2">
              <a href="{{ route('admin.flows.nodes.edit', [$flow, $n]) }}" class="text-gray-700 hover:underline">Edit</a>
              <form action="{{ route('admin.flows.nodes.destroy', [$flow, $n]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this node?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No nodes yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
