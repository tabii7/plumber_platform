@extends('layouts.modern-dashboard')

@section('title', 'Plumbers')

@section('page-title', 'Plumbers')

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
        <a href="{{ route('admin.flows.index') }}" class="nav-link">
            <i class="fas fa-project-diagram"></i>
            <span>WhatsApp Flows</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumbers.index') }}" class="nav-link active">
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Plumbers</h4>
                    <p class="text-muted mb-0">Manage plumbers and basic account/contact details.</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary fs-6">{{ $plumbers->total() ?? 0 }} Total Plumbers</span>
                    <a href="{{ route('plumbers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Add Plumber
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>WhatsApp</th>
                            <th>Location</th>
                            <th>Work Radius</th>
                            <th>Subscription</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plumbers as $plumber)
                            @php
                                // Resolve user record (your fields live here)
                                $u = $plumber->user ?? $plumber;

                                // Basic fields
                                $name = $u->full_name ?? $u->name ?? '—';
                                $company = $u->company_name ?? '—';
                                $wa = $u->whatsapp_number ?? '—';
                                $radius = $u->werk_radius ?? $plumber->werk_radius ?? null;

                                // Location: prefer city, postal_code, country; fall back to address_json if present
                                $locParts = [];
                                if (!empty($u->city)) $locParts[] = $u->city;
                                if (!empty($u->postal_code)) $locParts[] = $u->postal_code;
                                if (!empty($u->country)) $locParts[] = $u->country;

                                if (empty($locParts) && !empty($u->address_json)) {
                                    $addr = is_string($u->address_json) ? json_decode($u->address_json, true) : $u->address_json;
                                    if (is_array($addr)) {
                                        foreach (['city','postal_code','country','formatted_address'] as $k) {
                                            if (!empty($addr[$k])) { $locParts[] = $addr[$k]; }
                                        }
                                    }
                                }
                                $locationText = !empty($locParts) ? implode(', ', $locParts) : '—';

                                // Subscription
                                $plan = $u->subscription_plan ?? null;
                                $subStatus = strtolower($u->subscription_status ?? '');
                                $statusLabel = $subStatus ? ucfirst($subStatus) : 'Unknown';
                                $badgeClass = match ($subStatus) {
                                    'active' => 'bg-success',
                                    'trial', 'past_due' => 'bg-warning',
                                    'canceled', 'cancelled', 'expired' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp

                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $name }}</div>
                                </td>

                                <td>
                                    <div class="small text-muted" style="max-width: 200px;">
                                        {{ $company }}
                                    </div>
                                </td>

                                <td>
                                    @if($wa && $wa !== '—')
                                        <div class="small">
                                            <i class="fab fa-whatsapp me-1 text-success"></i>
                                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', $wa) }}" target="_blank" class="text-decoration-none">
                                                {{ $wa }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="small text-muted" style="max-width: 220px;">
                                        {{ $locationText }}
                                    </div>
                                </td>

                                <td>
                                    <span class="small fw-medium">
                                        {{ $radius ? $radius . ' km' : '—' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge {{ $badgeClass }} align-self-start">
                                            {{ $statusLabel }}
                                        </span>
                                        @if($plan)
                                            <span class="small text-muted mt-1">Plan: {{ $plan }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('plumbers.show', $plumber) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="View Plumber">
                                            <i class="fas fa-eye me-1"></i>
                                            View
                                        </a>
                                        <a href="{{ route('plumbers.edit', $plumber) }}" 
                                           class="btn btn-outline-secondary btn-sm" 
                                           title="Edit Plumber">
                                            <i class="fas fa-edit me-1"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('plumbers.destroy', $plumber) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this plumber?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm" 
                                                    title="Delete Plumber">
                                                <i class="fas fa-trash me-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-user-tie fa-2x mb-2"></i>
                                        <p>No plumbers found.</p>
                                        <a href="{{ route('plumbers.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i>
                                            Add First Plumber
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(method_exists($plumbers, 'links'))
        <div class="d-flex justify-content-center mt-4">
            {{ $plumbers->links() }}
        </div>
    @endif
@endsection
