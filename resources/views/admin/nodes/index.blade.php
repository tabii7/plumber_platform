@extends('layouts.modern-dashboard')

@section('title', 'Nodes: ' . $flow->name)

@section('page-title', 'Nodes — ' . $flow->name)

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
                    <h4 class="mb-1">Nodes — {{ $flow->name }}</h4>
                    <p class="text-muted mb-0">Define steps, options, and next-node routing for dynamic conversations.</p>
                </div>
                <div>
                    <a href="{{ route('admin.flows.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Flows
                    </a>
                    <a href="{{ route('admin.flows.nodes.create', $flow) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        New Node
                    </a>
                </div>
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
                            <th width="80">Sort</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Preview</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nodes as $n)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $n->sort }}</span>
                                </td>
                                <td>
                                    <code class="text-xs">{{ $n->code }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $n->type }}</span>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $n->title }}</div>
                                </td>
                                <td>
                                    <div class="bg-light p-2 rounded text-xs" style="max-width: 300px; overflow: hidden;">
                                        {{ Str::limit(($n->body ?? ''), 120) }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.flows.nodes.edit', [$flow, $n]) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Edit Node">
                                            <i class="fas fa-edit me-1"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.flows.nodes.destroy', [$flow, $n]) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this node?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm" 
                                                    title="Delete Node">
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
                                        <i class="fas fa-sitemap fa-2x mb-2"></i>
                                        <p>No nodes created yet for this flow.</p>
                                        <a href="{{ route('admin.flows.nodes.create', $flow) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i>
                                            Create First Node
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
@endsection
