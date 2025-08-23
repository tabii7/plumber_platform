@extends('layouts.modern-dashboard')

@section('title', 'WhatsApp Management')

@section('page-title', 'WhatsApp Management')

@section('sidebar-nav')
    <div class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.whatsapp') }}" class="nav-link active">
            <i class="fab fa-whatsapp"></i>
            <span>WhatsApp</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.flows.index') }}" class="nav-link">
            <i class="fas fa-project-diagram"></i>
            <span>WhatsApp Flows</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumbers.index') }}" class="nav-link">
            <i class="fas fa-user-tie"></i>
            <span>Plumbers</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('clients.index') }}" class="nav-link">
            <i class="fas fa-users"></i>
            <span>Clients</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.requests.index') }}" class="nav-link">
            <i class="fas fa-tools"></i>
            <span>Service Requests</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('support') }}" class="nav-link">
            <i class="fas fa-headset"></i>
            <span>Support</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('profile.edit') }}" class="nav-link">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
@endsection

@section('content')
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

<div class="row">
    <!-- Left: Status/QR or Connected -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fab fa-whatsapp me-2"></i>
                    Connection Status
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted">Status:</span>
                    <span class="badge bg-info ms-2">
                        {{ json_encode($status, JSON_PRETTY_PRINT) }}
                    </span>
                </div>

                @if ($qr)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Scan this QR code:</strong><br>
                        WhatsApp → Linked devices → Link a device
                    </div>
                    <div class="text-center p-3 border border-dashed rounded">
                        <img src="{{ $qr }}" alt="WhatsApp QR Code" class="img-fluid" style="max-height: 280px;">
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Already connected (or QR not available yet).</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right: Test form -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-paper-plane me-2"></i>
                    Send Test Message
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.whatsapp.testSend') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="number" class="form-label">WhatsApp Number</label>
                        <input type="text" id="number" name="number" 
                               class="form-control" 
                               placeholder="e.g. 32470123456" required>
                        <div class="form-text">Enter the number without any special characters</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Type your test message" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>
                        Send Test Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Additional Information -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    WhatsApp Bot Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Connection Details</h6>
                        <ul class="list-unstyled">
                            <li><strong>Bot Status:</strong> 
                                <span class="badge bg-info">
                                    {{ $status['status'] ?? 'Unknown' }}
                                </span>
                            </li>
                            <li><strong>Last Updated:</strong> {{ now()->format('M d, Y H:i:s') }}</li>
                            <li><strong>Environment:</strong> {{ config('app.env') }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Back to Dashboard
                            </a>
                            <a href="{{ route('admin.flows.index') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-project-diagram me-2"></i>
                                Manage Flows
                            </a>
                            <a href="{{ route('support') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-headset me-2"></i>
                                Get Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
