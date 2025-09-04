<div class="nav-item">
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('admin.whatsapp') }}" class="nav-link {{ request()->routeIs('admin.whatsapp*') ? 'active' : '' }}">
        <i class="fab fa-whatsapp"></i>
        <span>WhatsApp</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('admin.flows.index') }}" class="nav-link {{ request()->routeIs('admin.flows.*') ? 'active' : '' }}">
        <i class="fas fa-project-diagram"></i>
        <span>WhatsApp Flows</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('plumbers.index') }}" class="nav-link {{ request()->routeIs('plumbers.*') ? 'active' : '' }}">
        <i class="fas fa-user-tie"></i>
        <span>Plumbers</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i>
        <span>Clients</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('requests.index') }}" class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}">
        <i class="fas fa-tools"></i>
        <span>All Requests</span>
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
