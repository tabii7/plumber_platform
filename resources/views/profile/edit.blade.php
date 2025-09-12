@extends('layouts.modern-dashboard')

@section('title', 'Profile')

@section('page-title', 'Profile')

@section('sidebar-nav')
    @if(Auth::user()->role === 'admin')
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
            <a href="{{ route('profile.edit') }}" class="nav-link active">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    @elseif(Auth::user()->role === 'plumber')
        <x-plumber-sidebar />
    @else
        <div class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ route('support') }}" class="nav-link">
                <i class="fas fa-headset"></i>
                <span>Support</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ route('profile.edit') }}" class="nav-link active">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    @endif
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Profile Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Profile Information
                </h4>
                <p class="text-muted mb-0">Update your account's profile information and email address.</p>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Update Password -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-lock me-2"></i>
                    Update Password
                </h4>
                <p class="text-muted mb-0">Ensure your account is using a long, random password to stay secure.</p>
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Delete Account -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-trash-alt me-2"></i>
                    Delete Account
                </h4>
                <p class="text-muted mb-0">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
