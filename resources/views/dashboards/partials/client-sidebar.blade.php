<div class="nav-item">
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('requests.index') }}" class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}">
        <i class="fas fa-list"></i>
        <span>My Requests</span>
    </a>
</div>

<div class="nav-item">
    <a href="{{ route('welcome') }}#pricing" class="nav-link">
        <i class="fas fa-credit-card"></i>
        <span>Subscribe</span>
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
        <i class="fas fa-user-cog"></i>
        <span>Profile</span>
    </a>
</div>
