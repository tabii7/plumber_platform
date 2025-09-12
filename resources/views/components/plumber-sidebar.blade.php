{{-- Standardized Plumber Sidebar Navigation --}}
<div class="nav-item">
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('plumber.coverage.index') }}" class="nav-link {{ request()->routeIs('plumber.coverage.*') ? 'active' : '' }}">
        <i class="fas fa-map-marker-alt"></i>
        <span>Coverage Areas</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('plumber.schedule.index') }}" class="nav-link {{ request()->routeIs('plumber.schedule.*') ? 'active' : '' }}">
        <i class="fas fa-clock"></i>
        <span>Schedule</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('plumber.categories.edit') }}" class="nav-link {{ request()->routeIs('plumber.categories.*') ? 'active' : '' }}">
        <i class="fas fa-tools"></i>
        <span>Service Categories</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('requests.index') }}" class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}">
        <i class="fas fa-list-alt"></i>
        <span>Service Requests</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('support') }}" class="nav-link {{ request()->routeIs('support') ? 'active' : '' }}">
        <i class="fas fa-headset"></i>
        <span>Support</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <i class="fas fa-user-cog"></i>
        <span>Profile</span>
    </a>
</div>
