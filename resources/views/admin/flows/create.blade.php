@extends('layouts.modern-dashboard')

@section('title', 'Create Flow')

@section('page-title', 'Create Flow')

@section('sidebar-nav')
    <div class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.whatsapp') }}" class="nav-link">
            <i class="fab fa-whatsapp"></i>
            <span>WhatsApp</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.flows.index') }}" class="nav-link active">
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
        <a href="{{ route('requests.index') }}" class="nav-link">
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
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark">
                            <i class="fas fa-plus me-2 text-success"></i>
                            Create New Flow
                        </h4>
                        <a href="{{ route('admin.flows.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Flows
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.flows.store') }}" class="needs-validation" novalidate>
                        @csrf
                        @include('admin.flows.partials.form', ['flow' => $flow])
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Create Flow
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
