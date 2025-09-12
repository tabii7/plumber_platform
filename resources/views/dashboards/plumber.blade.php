@extends('layouts.modern-dashboard')

@section('title', 'Plumber Dashboard')

@section('page-title', 'Plumber Dashboard')

@section('sidebar-nav')
    <x-plumber-sidebar />
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
                    <!-- User's Own Address -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-home me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="mb-1">Your Address</h6>
                                        <p class="mb-0">
                                            @if(Auth::user()->address && Auth::user()->city)
                                                {{ Auth::user()->address }}{{ Auth::user()->number ? ', ' . Auth::user()->number : '' }}, {{ Auth::user()->postal_code }} {{ Auth::user()->city }}
                                            @else
                                                <span class="text-muted">Address not set - <a href="{{ route('profile.edit') }}">Update your profile</a></span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                                <small class="text-muted">
                                                    @if($coverage->coverage_type === 'municipality')
                                                        <i class="fas fa-home me-1"></i>Your municipality
                                                    @else
                                                        Entire municipality
                                                    @endif
                                                </small>
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
                        <!-- Default 5km Nearby Area Suggestion -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-lightbulb me-3" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <h6 class="mb-1">Suggested Coverage Area</h6>
                                            <p class="mb-2">Based on your address, we suggest covering a 5km radius around your location to maximize job opportunities.</p>
                                            @if(Auth::user()->city)
                                                <button id="autoAddNearbyBtn" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-magic me-2"></i>
                                                    Auto-Add 5km Nearby Areas
                                                </button>
                                            @else
                                                <small class="text-muted">Complete your address in <a href="{{ route('profile.edit') }}">profile settings</a> to get suggestions</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const autoAddBtn = document.getElementById('autoAddNearbyBtn');
    
    if (autoAddBtn) {
        autoAddBtn.addEventListener('click', async function() {
            if (autoAddBtn.disabled) return;
            
            try {
                autoAddBtn.disabled = true;
                autoAddBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
                
                const response = await fetch('{{ route("plumber.coverage.auto-nearby") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        ${result.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    // Insert at the top of the coverage card
                    const coverageCard = document.querySelector('.card .card-body');
                    if (coverageCard) {
                        coverageCard.insertBefore(alertDiv, coverageCard.firstChild);
                    }
                    
                    // Remove the suggestion section since coverage is now added
                    const suggestionSection = document.querySelector('.alert-warning');
                    if (suggestionSection) {
                        suggestionSection.remove();
                    }
                    
                    // Reload the page to show updated coverage
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                } else {
                    // Show error message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${result.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    const coverageCard = document.querySelector('.card .card-body');
                    if (coverageCard) {
                        coverageCard.insertBefore(alertDiv, coverageCard.firstChild);
                    }
                }
                
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while adding nearby areas. Please try again.');
            } finally {
                autoAddBtn.disabled = false;
                autoAddBtn.innerHTML = '<i class="fas fa-magic me-2"></i>Auto-Add 5km Nearby Areas';
            }
        });
    }
});
</script>
@endpush
