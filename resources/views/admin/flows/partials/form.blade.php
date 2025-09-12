@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h6 class="alert-heading mb-2">
      <i class="fas fa-exclamation-triangle me-1"></i>
      Please fix the following errors:
    </h6>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="row">
  <div class="col-md-6 mb-3">
    <label for="code" class="form-label">
      <i class="fas fa-code me-1 text-muted"></i>
      Code <span class="text-danger">*</span>
    </label>
    <input type="text" 
           class="form-control @error('code') is-invalid @enderror" 
           id="code" 
           name="code" 
           value="{{ old('code', $flow->code) }}" 
           placeholder="e.g., welcome_flow"
           required>
    @error('code')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  
  <div class="col-md-6 mb-3">
    <label for="name" class="form-label">
      <i class="fas fa-tag me-1 text-muted"></i>
      Name <span class="text-danger">*</span>
    </label>
    <input type="text" 
           class="form-control @error('name') is-invalid @enderror" 
           id="name" 
           name="name" 
           value="{{ old('name', $flow->name) }}" 
           placeholder="e.g., Welcome Flow"
           required>
    @error('name')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
    <label for="entry_keyword" class="form-label">
      <i class="fas fa-key me-1 text-muted"></i>
      Entry Keyword
    </label>
    <input type="text" 
           class="form-control @error('entry_keyword') is-invalid @enderror" 
           id="entry_keyword" 
           name="entry_keyword" 
           value="{{ old('entry_keyword', $flow->entry_keyword) }}" 
           placeholder="info / plumber / help">
    @error('entry_keyword')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Keyword users type to start this flow</div>
  </div>
  
  <div class="col-md-4 mb-3">
    <label for="target_role" class="form-label">
      <i class="fas fa-users me-1 text-muted"></i>
      Target Role
    </label>
    <select class="form-select @error('target_role') is-invalid @enderror" 
            id="target_role" 
            name="target_role">
      @foreach(['client' => 'Client', 'plumber' => 'Plumber', 'any' => 'Any User'] as $value => $label)
        <option value="{{ $value }}" @selected(old('target_role', $flow->target_role) == $value)>
          {{ $label }}
        </option>
      @endforeach
    </select>
    @error('target_role')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  
  <div class="col-md-4 mb-3">
    <label class="form-label">
      <i class="fas fa-toggle-on me-1 text-muted"></i>
      Status
    </label>
    <div class="form-check form-switch mt-2">
      <input class="form-check-input" 
             type="checkbox" 
             id="is_active" 
             name="is_active" 
             value="1" 
             @checked(old('is_active', $flow->is_active))>
      <label class="form-check-label" for="is_active">
        Active Flow
      </label>
    </div>
    <div class="form-text">Enable or disable this flow</div>
  </div>
</div>
