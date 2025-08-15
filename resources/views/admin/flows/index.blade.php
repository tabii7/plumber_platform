@extends('layouts.app')

@section('title','WhatsApp Flows')

@section('header')
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">WhatsApp Flows</h1>
      <p class="text-sm text-gray-600">Create and manage conversation flows.</p>
    </div>
    <a href="{{ route('admin.flows.create') }}" class="bg-cyan-600 text-white px-3 py-2 rounded-md hover:bg-cyan-700">New Flow</a>
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
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Name</th>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Code</th>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Entry Keyword</th>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Target Role</th>
          <th class="px-3 py-2 text-left font-semibold text-gray-700">Active</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white text-gray-600">
        @forelse($flows as $flow)
          <tr>
            <td class="px-3 py-2">{{ $flow->name }}</td>
            <td class="px-3 py-2 font-mono text-xs">{{ $flow->code }}</td>
            <td class="px-3 py-2">{{ $flow->entry_keyword ?: '—' }}</td>
            <td class="px-3 py-2">{{ $flow->target_role ?: '—' }}</td>
            <td class="px-3 py-2">
              @if($flow->is_active)
                <span class="text-green-700 bg-green-100 px-2 py-0.5 rounded text-xs">active</span>
              @else
                <span class="text-gray-600 bg-gray-100 px-2 py-0.5 rounded text-xs">off</span>
              @endif
            </td>
            <td class="px-3 py-2 text-right space-x-2">
              <a href="{{ route('admin.flows.nodes.index', $flow) }}" class="text-blue-600 hover:underline">Nodes</a>
              <a href="{{ route('admin.flows.edit', $flow) }}" class="text-gray-700 hover:underline">Edit</a>
              <form action="{{ route('admin.flows.destroy', $flow) }}" method="POST" class="inline" onsubmit="return confirm('Delete this flow?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No flows yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $flows->links() }}</div>
@endsection
