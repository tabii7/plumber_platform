@extends('layouts.modern-dashboard')

@section('title', 'Requests')

@section('page-title', 'Requests')

@section('sidebar-nav')
    @if(Auth::user()->role === 'admin')
        @include('dashboards.partials.admin-sidebar')
    @elseif(Auth::user()->role === 'plumber')
        <x-plumber-sidebar />
    @else
        @include('dashboards.partials.client-sidebar')
    @endif
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        @if(Auth::user()->role === 'admin')
                            All Requests
                        @elseif(Auth::user()->role === 'plumber')
                            My Assigned Requests
                        @else
                            My Requests
                        @endif
                    </h3>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Problem</th>
                                        <th>Urgency</th>
                                        @if(Auth::user()->role === 'admin')
                                            <th>Customer</th>
                                        @endif
                                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'client')
                                            <th>Assigned Plumber</th>
                                        @endif
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#{{ $request->id }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $request->problem }}</strong>
                                                @if($request->has_images)
                                                    <i class="fas fa-image text-info ms-1" title="Has images"></i>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $urgencyColors = [
                                                        'high' => 'danger',
                                                        'normal' => 'warning',
                                                        'low' => 'success'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $urgencyColors[$request->urgency] ?? 'secondary' }}">
                                                    {{ ucfirst($request->urgency) }}
                                                </span>
                                            </td>
                                            @if(Auth::user()->role === 'admin')
                                                <td>
                                                    {{ $request->customer->full_name ?? 'Unknown' }}
                                                    <br>
                                                    <small class="text-muted">{{ $request->customer->email ?? '' }}</small>
                                                </td>
                                            @endif
                                            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'client')
                                                <td>
                                                    @if($request->selectedPlumber)
                                                        {{ $request->selectedPlumber->full_name }}
                                                        <br>
                                                        <small class="text-muted">{{ $request->selectedPlumber->company_name ?? '' }}</small>
                                                    @else
                                                        <span class="text-muted">Not assigned</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'broadcasting' => 'info',
                                                        'active' => 'primary',
                                                        'in_progress' => 'warning',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $request->created_at->format('M d, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $request->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    
                                                    @if(Auth::user()->role === 'plumber' && $request->selected_plumber_id === Auth::id())
                                                        @if(in_array($request->status, ['active', 'in_progress']))
                                                            <form method="POST" action="{{ route('requests.complete', $request) }}" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success" 
                                                                        onclick="return confirm('Mark this request as completed?')">
                                                                    <i class="fas fa-check"></i> Complete
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                    
                                                    @if(Auth::user()->role === 'admin')
                                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#statusModal{{ $request->id }}">
                                                            <i class="fas fa-edit"></i> Update Status
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $requests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No requests found</h5>
                            <p class="text-muted">
                                @if(Auth::user()->role === 'client')
                                    You haven't created any requests yet.
                                @elseif(Auth::user()->role === 'plumber')
                                    No requests have been assigned to you yet.
                                @else
                                    No requests have been created yet.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin Modals -->
@if(Auth::user()->role === 'admin')
    @foreach($requests as $request)
        <!-- Update Status Modal -->
        <div class="modal fade" id="statusModal{{ $request->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Status - Request #{{ $request->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('requests.update-status', $request) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    @foreach(\App\Models\WaRequest::getAvailableStatuses() as $key => $label)
                                        <option value="{{ $key }}" {{ $request->status == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
