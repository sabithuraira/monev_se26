<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary bg-dark">
        <h5 class="fw-bold mb-0 text-white">
            <i class="fas fa-chart-line text-primary"></i> MONITORING SE
        </h5>
    </div>
    @auth
    <div class="p-3 border-bottom text-center" style="background-color: rgba(0,0,0,0.1);">
        <div class="fw-bold text-white mb-1"><i class="fas fa-user-circle fs-2 text-secondary mb-2"></i><br>{{ auth()->user()->name }}</div>
        <div class="badge bg-primary text-uppercase mb-2">Admin</div>
    </div>
    @endauth
    <div class="flex-grow-1">
        <a href="/" class="{{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard Wilayah
        </a>
        <a href="/rekapitulasi" class="{{ request()->is('rekapitulasi') ? 'active' : '' }}">
            <i class="fas fa-table-list me-2"></i> Rekapitulasi
        </a>
        <a href="/hasil-klaster" class="{{ request()->is('hasil-klaster') ? 'active' : '' }}">
            <i class="fas fa-robot me-2"></i> Hasil Klaster
        </a>
        <a href="/data-subsls" class="{{ request()->is('data-subsls') ? 'active' : '' }}">
            <i class="fas fa-table me-2"></i> Data Subsls
        </a>
    </div>
    <div class="sidebar-auth">
        @guest
        <a href="{{ route('login') }}" class="sidebar-login {{ request()->routeIs('login') ? 'is-active' : '' }}">
            <i class="fas fa-sign-in-alt me-2"></i> Login
        </a>
        @else
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="sidebar-logout">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </button>
        </form>
        @endguest
    </div>
</div>
