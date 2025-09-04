@extends('layouts.modern-dashboard')

@section('title', 'Request Details')

@section('page-title', 'Request Details')

@section('sidebar-nav')
    @if(Auth::user()->role === 'admin')
        @include('dashboards.partials.admin-sidebar')
    @elseif(Auth::user()->role === 'plumber')
        @include('dashboards.partials.plumber-sidebar')
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
                    <h3 class="card-title">Request #{{ $request->id }}</h3>
                    <div>
                        @php
                            $statusColors = [
                                'broadcasting' => 'info',
                                'active' => 'primary',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Request Details -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Request Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-3"><strong>Problem:</strong></div>
                                        <div class="col-sm-9">{{ $request->problem }}</div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3"><strong>Urgency:</strong></div>
                                        <div class="col-sm-9">
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
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3"><strong>Description:</strong></div>
                                        <div class="col-sm-9">{{ $request->description }}</div>
                                    </div>
                                    @if($request->has_images && $request->images)
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3"><strong>Images:</strong></div>
                                            <div class="col-sm-9">
                                                <div class="row">
                                                    @foreach($request->images as $image)
                                                        <div class="col-md-4 mb-2">
                                                            <img src="{{ $image['url'] ?? $image }}" class="img-thumbnail" style="max-height: 150px;">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-3"><strong>Name:</strong></div>
                                        <div class="col-sm-9">{{ $request->customer->full_name }}</div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3"><strong>Email:</strong></div>
                                        <div class="col-sm-9">{{ $request->customer->email }}</div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3"><strong>Phone:</strong></div>
                                        <div class="col-sm-9">{{ $request->customer->whatsapp_number ?? 'Not provided' }}</div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3"><strong>Address:</strong></div>
                                        <div class="col-sm-9">
                                            {{ $request->customer->address }}
                                            @if($request->customer->number)
                                                {{ $request->customer->number }}
                                            @endif
                                            <br>
                                            {{ $request->customer->postal_code }} {{ $request->customer->city }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Request Timeline -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Timeline</h5>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <h6>Request Created</h6>
                                                <p class="text-muted">{{ $request->created_at->format('M d, Y H:i') }}</p>
                                            </div>
                                        </div>
                                        
                                        @if($request->selected_plumber_id)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6>Plumber Assigned</h6>
                                                    <p class="text-muted">{{ $request->updated_at->format('M d, Y H:i') }}</p>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($request->completed_at)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6>Request Completed</h6>
                                                    <p class="text-muted">{{ $request->completed_at->format('M d, Y H:i') }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Assigned Plumber -->
                            @if($request->selectedPlumber)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Assigned Plumber</h5>
                                    </div>
                                    <div class="card-body">
                                        <h6>{{ $request->selectedPlumber->full_name }}</h6>
                                        @if($request->selectedPlumber->company_name)
                                            <p class="text-muted">{{ $request->selectedPlumber->company_name }}</p>
                                        @endif
                                        <p class="text-muted">{{ $request->selectedPlumber->email }}</p>
                                        @if($request->selectedPlumber->whatsapp_number)
                                            <p class="text-muted">{{ $request->selectedPlumber->whatsapp_number }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Actions</h5>
                                </div>
                                <div class="card-body">
                                    @if(Auth::user()->role === 'plumber' && $request->selected_plumber_id === Auth::id())
                                        @if(in_array($request->status, ['active', 'in_progress']))
                                            <form method="POST" action="{{ route('requests.complete', $request) }}" class="mb-3">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100" 
                                                        onclick="return confirm('Mark this request as completed?')">
                                                    <i class="fas fa-check"></i> Mark as Complete
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    @if(Auth::user()->role === 'admin')
                                        <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#statusModal">
                                            <i class="fas fa-edit"></i> Update Status
                                        </button>
                                    @endif

                                    <a href="{{ route('requests.index') }}" class="btn btn-secondary w-100 mt-2">
                                        <i class="fas fa-arrow-left"></i> Back to Requests
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin Modals -->
@if(Auth::user()->role === 'admin')
    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
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
@endif

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}
</style>
@endsection
