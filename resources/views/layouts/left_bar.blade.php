<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary bg-dark">
        <h5 class="fw-bold mb-0 text-white">
            <i class="fas fa-chart-line text-primary"></i> MONITORING SE
        </h5>
    </div>
    <div class="p-3 border-bottom text-center" style="background-color: rgba(0,0,0,0.1);">
        <div class="fw-bold text-white mb-1"><i class="fas fa-user-circle fs-2 text-secondary mb-2"></i><br>Pengguna</div>
        <div class="badge bg-primary text-uppercase mb-2">Admin</div>
    </div>
    <div class="flex-grow-1">
        <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard Wilayah
        </a>
        <a href="/rekapitulasi" class="{{ request()->is('rekapitulasi') ? 'active' : '' }}">
            <i class="fas fa-table-list me-2"></i> Rekapitulasi
        </a>
        <a href="/hasil-klaster" class="{{ request()->is('hasil-klaster') ? 'active' : '' }}">
            <i class="fas fa-robot me-2"></i> Hasil Klaster
        </a>
        <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
            <i class="fas fa-table me-2"></i> Data Subsls
        </a>
    </div>
</div>
