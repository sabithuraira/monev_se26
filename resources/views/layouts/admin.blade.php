<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring SE2026</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body { background-color: #f8f9fa; margin: 0; display: flex; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            width: 250px;
            min-width: 250px;
            height: 100vh;
            background-color: #212529;
            color: white;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            border-bottom: 1px solid #343a40;
            transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #0d6efd;
            color: white;
            border-left: 5px solid #fff;
        }
        .content { width: 100%; padding: 30px; overflow-x: hidden; }
        .mobile-nav {
            display: none;
            background-color: #212529;
            padding: 12px 20px;
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 1050;
        }
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { display: none; }
            .mobile-nav { display: flex; justify-content: space-between; align-items: center; }
            .content { padding: 15px; }
        }
        .offcanvas { background-color: #212529; width: 280px !important; }
        .offcanvas a {
            color: #adb5bd;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            border-bottom: 1px solid #343a40;
        }
        .offcanvas a:hover { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>
    <div class="mobile-nav shadow-sm">
        <div class="d-flex align-items-center text-white">
            <i class="fas fa-chart-line me-2 text-primary"></i>
            <h6 class="mb-0 fw-bold">Monitoring Sensus Ekonomi</h6>
        </div>
        <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    @include('layouts.left_bar')

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
        <div class="offcanvas-header text-white bg-dark">
            <h5 class="offcanvas-title fw-bold">NAVIGASI MENU</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <a href="/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Wilayah</a>
            <a href="/rekapitulasi"><i class="fas fa-table-list me-2"></i> Rekapitulasi</a>
            <a href="/hasil-klaster"><i class="fas fa-robot me-2"></i> Hasil Klaster</a>
            <a href="/"><i class="fas fa-table me-2"></i> Data Subsls</a>
        </div>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
