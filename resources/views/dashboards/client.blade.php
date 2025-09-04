@extends('layouts.modern-dashboard')

@section('title', 'Client Dashboard')

@section('page-title', 'Client Dashboard')

@section('sidebar-nav')
    <div class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('requests.index') }}" class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}">
            <i class="fas fa-list"></i>
            <span>My Requests</span>
        </a>
    </div>
    
    
    <div class="nav-item">
        <a href="{{ route('welcome') }}#pricing" class="nav-link">
            <i class="fas fa-credit-card"></i>
            <span>Subscribe</span>
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
                            <p class="text-muted mb-0">Manage your plumber service requests and subscriptions from your dashboard.</p>
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
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <div class="stats-number">0</div>
                        <div class="stats-label">Active Requests</div>
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
                        <div class="stats-number">0</div>
                        <div class="stats-label">Reviews Given</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card warning">
                <div class="d-flex align-items-center">
                    <div class="stats-icon me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="stats-number">0</div>
                        <div class="stats-label">Pending Jobs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Subscription Status
                    </h5>
                </div>
                <div class="card-body">
                    @if(Auth::user()->subscription_status === 'active' && Auth::user()->subscription_ends_at && Auth::user()->subscription_ends_at->isFuture())
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-success mb-1">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Active Subscription
                                </h6>
                                <p class="text-muted mb-0">
                                    Plan: <strong>{{ Auth::user()->subscription_plan ?? 'Premium' }}</strong>
                                </p>
                                @if(Auth::user()->subscription_ends_at)
                                    <p class="text-muted mb-0">
                                        Expires: <strong>{{ Auth::user()->subscription_ends_at->format('M d, Y') }}</strong>
                                    </p>
                                @endif
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-success fs-6">Active</span>
                            </div>
                        </div>
                    @else
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-warning mb-1">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No Active Subscription
                                </h6>
                                <p class="text-muted mb-0">
                                    Subscribe to a plan to access plumber services.
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="{{ route('welcome') }}#pricing" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Subscribe Now
                                </a>
                            </div>
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
                        <a href="{{ route('welcome') }}#pricing" class="btn btn-outline-primary">
                            <i class="fas fa-credit-card me-2"></i>
                            Manage Subscription
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
