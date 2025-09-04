@extends('layouts.modern-dashboard')

@section('title', 'Admin Dashboard')

@section('page-title', 'Admin Dashboard')

@section('sidebar-nav')
    <div class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.whatsapp') }}" class="nav-link {{ request()->routeIs('admin.whatsapp*') ? 'active' : '' }}">
            <i class="fab fa-whatsapp"></i>
            <span>WhatsApp</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.flows.index') }}" class="nav-link {{ request()->routeIs('admin.flows.*') ? 'active' : '' }}">
            <i class="fas fa-project-diagram"></i>
            <span>WhatsApp Flows</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumbers.index') }}" class="nav-link {{ request()->routeIs('plumbers.*') ? 'active' : '' }}">
            <i class="fas fa-user-tie"></i>
            <span>Plumbers</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Clients</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('requests.index') }}" class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}">
            <i class="fas fa-tools"></i>
            <span>All Requests</span>
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
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">Welcome back, {{ Auth::user()->full_name ?? Auth::user()->email }}!</h4>
                            <p class="text-muted mb-0">Monitor and manage the plumber platform from your admin dashboard.</p>
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
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $totalUsers ?? 0 }}</div>
                        <div class="stats-label">Total Users</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card success">
                <div class="d-flex align-items-center">
                    <div class="stats-icon me-3">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $totalPlumbers ?? 0 }}</div>
                        <div class="stats-label">Plumbers</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card info">
                <div class="d-flex align-items-center">
                    <div class="stats-icon me-3">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $totalClients ?? 0 }}</div>
                        <div class="stats-label">Clients</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card warning">
                <div class="d-flex align-items-center">
                    <div class="stats-icon me-3">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $activeSubscriptions ?? 0 }}</div>
                        <div class="stats-label">Active Subscriptions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Overview -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Platform Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="stats-icon me-3" style="width: 40px; height: 40px; font-size: 1rem; background: var(--primary-color);">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Service Requests</h6>
                                    <p class="text-muted mb-0">{{ $totalRequests ?? 0 }} total</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="stats-icon me-3" style="width: 40px; height: 40px; font-size: 1rem; background: var(--success-color);">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Completed Jobs</h6>
                                    <p class="text-muted mb-0">{{ $completedJobs ?? 0 }} total</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="stats-icon me-3" style="width: 40px; height: 40px; font-size: 1rem; background: var(--info-color);">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Average Rating</h6>
                                    <p class="text-muted mb-0">{{ number_format($averageRating ?? 0, 1) }} / 5.0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="stats-icon me-3" style="width: 40px; height: 40px; font-size: 1rem; background: var(--warning-color);">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Coverage Areas</h6>
                                    <p class="text-muted mb-0">{{ $totalCoverageAreas ?? 0 }} total</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i>
                        Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @if(isset($recentActivity) && count($recentActivity) > 0)
                            @foreach($recentActivity->take(5) as $activity)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-icon me-3">
                                            <i class="fas fa-circle text-primary" style="font-size: 0.5rem;"></i>
                                        </div>
                                        <div>
                                            <p class="mb-1 small">{{ $activity->description ?? 'Activity' }}</p>
                                            <small class="text-muted">{{ $activity->created_at?->diffForHumans() ?? 'Recently' }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted small">No recent activity</p>
                            </div>
                        @endif
                    </div>
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
                        <a href="{{ route('admin.whatsapp') }}" class="btn btn-outline-success">
                            <i class="fab fa-whatsapp me-2"></i>
                            WhatsApp Management
                        </a>
                        <a href="{{ route('admin.flows.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-project-diagram me-2"></i>
                            Manage Flows
                        </a>
                        <a href="{{ route('plumbers.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-user-tie me-2"></i>
                            Manage Plumbers
                        </a>
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-users me-2"></i>
                            Manage Clients
                        </a>
                        <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-tools me-2"></i>
                            All Requests
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
                        System Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted mb-1">Platform Version</p>
                            <p class="fw-semibold">v1.0.0</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">Last Updated</p>
                            <p class="fw-semibold">{{ now()->format('M d, Y') }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">Database Status</p>
                            <p class="fw-semibold text-success">Connected</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">WhatsApp Bot</p>
                            <p class="fw-semibold text-success">Active</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">Payment Gateway</p>
                            <p class="fw-semibold text-success">Mollie</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">Environment</p>
                            <p class="fw-semibold">{{ config('app.env') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
    }
    
    .timeline-item {
        position: relative;
    }
    
    .timeline-icon {
        position: relative;
        top: 0.25rem;
    }
</style>
@endpush
