@extends('layouts.modern-dashboard')

@section('title', 'Plumber Dashboard')

@section('page-title', 'Plumber Dashboard')

@section('sidebar-nav')
    <div class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumber.coverage.index') }}" class="nav-link {{ request()->routeIs('plumber.coverage.*') ? 'active' : '' }}">
            <i class="fas fa-map-marker-alt"></i>
            <span>Coverage Areas</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumber.schedule.index') }}" class="nav-link {{ request()->routeIs('plumber.schedule.*') ? 'active' : '' }}">
            <i class="fas fa-clock"></i>
            <span>Schedule</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('requests.index') }}" class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}">
            <i class="fas fa-tools"></i>
            <span>My Requests</span>
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
            <i class="fas fa-user-cog"></i>
            <span>Profile</span>
        </a>
    </div>
@endsection

@section('content')
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">Welcome back, {{ Auth::user()->full_name ?? Auth::user()->email }}!</h4>
                            <p class="text-muted mb-0">Manage your plumber services, coverage areas, and job requests from your dashboard.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.5rem; margin: 0 auto;">
                                {{ strtoupper(substr(Auth::user()->full_name ?? Auth::user()->email, 0, 1)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon me-3">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <div class="stats-number">0</div>
                        <div class="stats-label">Active Jobs</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card success">
                <div class="d-flex align-items-center">
                    <div class="stats-icon me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stats-number">0</div>
                        <div class="stats-label">Completed Jobs</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card info">
                <div class="d-flex align-items-center">
                    <div class="stats-icon me-3">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <div class="stats-number">0.0</div>
                        <div class="stats-label">Average Rating</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card warning">
                <div class="d-flex align-items-center">
                    <div class="stats-icon me-3">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ Auth::user()->coverages->count() }}</div>
                        <div class="stats-label">Coverage Areas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Coverage Areas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Coverage Areas
                    </h5>
                    <a href="{{ route('plumber.coverage.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>
                        Manage Coverage
                    </a>
                </div>
                <div class="card-body">
                    @if(Auth::user()->coverages->count() > 0)
                        <div class="row">
                            @foreach(Auth::user()->coverages->take(6) as $coverage)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="stats-icon me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $coverage->hoofdgemeente }}</h6>
                                            @if($coverage->coverage_type === 'city')
                                                <small class="text-muted">{{ $coverage->city }}</small>
                                            @else
                                                <small class="text-muted">Entire municipality</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if(Auth::user()->coverages->count() > 6)
                            <div class="text-center mt-3">
                                <a href="{{ route('plumber.coverage.index') }}" class="btn btn-outline-primary">
                                    View All {{ Auth::user()->coverages->count() }} Areas
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No coverage areas set</h6>
                            <p class="text-muted mb-3">Add coverage areas to receive job requests in your area.</p>
                            <a href="{{ route('plumber.coverage.index') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Add Coverage Areas
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('plumber.coverage.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Manage Coverage Areas
                        </a>

                        <a href="{{ route('support') }}" class="btn btn-outline-info">
                            <i class="fas fa-headset me-2"></i>
                            Contact Support
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user-cog me-2"></i>
                            Update Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted mb-1">Name</p>
                            <p class="fw-semibold">{{ Auth::user()->full_name ?? 'Not set' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">Email</p>
                            <p class="fw-semibold">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">City</p>
                            <p class="fw-semibold">{{ Auth::user()->city ?? 'Not set' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">WhatsApp</p>
                            <p class="fw-semibold">{{ Auth::user()->whatsapp_number ?? 'Not set' }}</p>
                        </div>
                        @if(Auth::user()->company_name)
                            <div class="col-6">
                                <p class="text-muted mb-1">Company</p>
                                <p class="fw-semibold">{{ Auth::user()->company_name }}</p>
                            </div>
                        @endif
                        @if(Auth::user()->btw_number)
                            <div class="col-6">
                                <p class="text-muted mb-1">VAT Number</p>
                                <p class="fw-semibold">{{ Auth::user()->btw_number }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
