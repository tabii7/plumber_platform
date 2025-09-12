@extends('layouts.modern-dashboard')

@section('title', 'Support')

@section('page-title', 'Support')

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
            <a href="{{ route('support') }}" class="nav-link active">
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
            <a href="{{ route('support') }}" class="nav-link active">
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
    @endif
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-headset me-2"></i>
                    Need Help?
                </h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    If you're experiencing issues with our WhatsApp service or need assistance, please contact us through any of the following channels:
                </p>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-start border-4 border-primary">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    Email Support
                                </h5>
                                <p class="card-text">Get help via email</p>
                                <a href="mailto:support@loodgieter.app" class="btn btn-primary">
                                    <i class="fas fa-envelope me-2"></i>
                                    support@loodgieter.app
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card border-start border-4 border-success">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fab fa-whatsapp text-success me-2"></i>
                                    WhatsApp Support
                                </h5>
                                <p class="card-text">Chat with us on WhatsApp</p>
                                <a href="https://wa.me/32123456789" target="_blank" class="btn btn-success">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    +32 490 46 80 09
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-start border-4 border-info">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    Support Hours
                                </h5>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</li>
                                    <li><strong>Saturday:</strong> 10:00 AM - 4:00 PM</li>
                                    <li><strong>Sunday:</strong> Closed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card border-start border-4 border-warning">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-question-circle text-warning me-2"></i>
                                    Response Time
                                </h5>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Email:</strong> Within 24 hours</li>
                                    <li><strong>WhatsApp:</strong> Within 2 hours</li>
                                    <li><strong>Urgent Issues:</strong> Immediate response</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-start border-4 border-danger">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            Common Issues & Solutions
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Can't send messages?</strong> Try restarting WhatsApp
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Not receiving responses?</strong> Check your internet connection
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Wrong address?</strong> Update your profile in the dashboard
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Payment issues?</strong> Contact our billing team
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
