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
                    <p class="text-muted mb-0">Manage all plumbers, their service areas, categories, and availability.</p>
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
                            <th>Contact</th>
                            <th>Service Areas</th>
                            <th>Tariff</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plumbers as $plumber)
                            @php
                                // User
                                $u = $plumber->user ?? null;
                                $name = $u->full_name ?? $u->name ?? '—';
                                $phone = $u->phone ?? '—';
                                $wa = $u->whatsapp_number ?? '—';

                                // Postal areas (string, array or relation)
                                $areas = $plumber->postal_codes ?? $plumber->municipalities ?? null;
                                if (is_array($areas)) {
                                    $areasText = implode(', ', $areas);
                                } elseif (is_string($areas)) {
                                    $areasText = $areas;
                                } else {
                                    // if you have a relation like $plumber->postalCodes, show first few
                                    $areasText = method_exists($plumber, 'postalCodes')
                                        ? $plumber->postalCodes->pluck('Postcode')->take(3)->implode(', ') .
                                          ($plumber->postalCodes->count() > 3 ? ' +' . ($plumber->postalCodes->count() - 3) . ' more' : '')
                                        : '—';
                                }

                                // Tariff
                                $tariff = $plumber->tariff ? '€' . number_format((float)$plumber->tariff, 2) : '—';

                                // Categories (JSON of names, or pivot)
                                $catsRaw = $plumber->service_categories ?? null;
                                if (is_string($catsRaw)) {
                                    $decoded = json_decode($catsRaw, true);
                                    $categories = is_array($decoded) ? implode(', ', $decoded) : $catsRaw;
                                } elseif (is_array($catsRaw)) {
                                    $categories = implode(', ', $catsRaw);
                                } elseif (method_exists($plumber, 'categories')) {
                                    $categories = $plumber->categories->pluck('name')->implode(', ');
                                } else {
                                    $categories = '—';
                                }

                                // Status badge
                                $status = $plumber->availability_status ?? 'unknown';
                                $statusLabel = $status === 'available' ? 'Available' :
                                               (in_array($status, ['busy','holiday']) ? 'Not working' : 'Unknown');
                            @endphp

                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $name }}</div>
                                </td>
                                <td>
                                    <div class="small">
                                        @if($phone !== '—')
                                            <div>
                                                <i class="fas fa-phone me-1 text-muted"></i>
                                                <a href="tel:{{ $phone }}" class="text-decoration-none">{{ $phone }}</a>
                                            </div>
                                        @endif
                                        @if($wa !== '—')
                                            <div>
                                                <i class="fab fa-whatsapp me-1 text-success"></i>
                                                <a href="https://wa.me/{{ $wa }}" target="_blank" class="text-decoration-none">{{ $wa }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-muted" style="max-width: 200px;">
                                        {{ $areasText }}
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $tariff }}</span>
                                </td>
                                <td>
                                    <div class="small text-muted" style="max-width: 150px;">
                                        {{ $categories }}
                                    </div>
                                </td>
                                <td>
                                    @if($status === 'available')
                                        <span class="badge bg-success">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            {{ $statusLabel }}
                                        </span>
                                    @elseif(in_array($status, ['busy','holiday']))
                                        <span class="badge bg-danger">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            {{ $statusLabel }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            {{ $statusLabel }}
                                        </span>
                                    @endif
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
