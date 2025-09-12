@extends('layouts.modern-dashboard')

@section('title', 'Service Categories')

@section('page-title', 'Service Categories')

@section('sidebar-nav')
    <x-plumber-sidebar />
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>
                        Service Categories
                    </h5>
                    <p class="text-muted mb-0 mt-2">Select all categories you can handle. You'll only receive jobs from these categories.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('plumber.categories.update') }}">
                        @csrf

                        <div class="row">
                            @foreach ($categories as $group => $items)
                                <div class="col-lg-6 mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-semibold text-primary">
                                                <i class="fas fa-folder me-2"></i>
                                                {{ $group }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach ($items as $cat)
                                                    <div class="col-12 mb-2">
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                   name="categories[]"
                                                                   value="{{ $cat->id }}"
                                                                   class="form-check-input"
                                                                   id="category_{{ $cat->id }}"
                                                                   {{ in_array($cat->id, $selected, true) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="category_{{ $cat->id }}">
                                                                {{ $cat->label }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('plumber.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Save Categories
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
