@extends('layouts.modern-dashboard')

@section('title', 'WhatsApp Flows')

@section('page-title', 'WhatsApp Flows')

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
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">WhatsApp Flows</h4>
                    <p class="text-muted mb-0">Create and manage conversation flows dynamically.</p>
                </div>
                <a href="{{ route('admin.flows.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    New Flow
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
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
                            <th>Code</th>
                            <th>Entry Keyword</th>
                            <th>Target Role</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flows as $flow)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $flow->name }}</div>
                                </td>
                                <td>
                                    <code class="text-xs">{{ $flow->code }}</code>
                                </td>
                                <td>
                                    @if($flow->entry_keyword)
                                        <span class="badge bg-info">{{ $flow->entry_keyword }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($flow->target_role)
                                        <span class="badge bg-secondary">{{ $flow->target_role }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($flow->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.flows.nodes.index', $flow) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Manage Nodes">
                                            <i class="fas fa-sitemap me-1"></i>
                                            Nodes
                                        </a>
                                        <a href="{{ route('admin.flows.edit', $flow) }}" 
                                           class="btn btn-outline-secondary btn-sm" 
                                           title="Edit Flow">
                                            <i class="fas fa-edit me-1"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.flows.destroy', $flow) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this flow?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm" 
                                                    title="Delete Flow">
                                                <i class="fas fa-trash me-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-project-diagram fa-2x mb-2"></i>
                                        <p>No flows created yet.</p>
                                        <a href="{{ route('admin.flows.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i>
                                            Create First Flow
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

    @if($flows->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $flows->links() }}
        </div>
    @endif
@endsection
