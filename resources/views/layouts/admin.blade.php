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
        .sidebar-auth {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid #343a40;
            background: rgba(0, 0, 0, 0.2);
        }
        .sidebar .sidebar-login {
            display: block;
            text-align: center;
            text-decoration: none;
            color: #fff;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid #fd7e14;
            background: linear-gradient(135deg, #e67700 0%, #fd7e14 100%);
            transition: 0.25s;
            box-shadow: 0 4px 12px rgba(253, 126, 20, 0.3);
        }
        .sidebar .sidebar-login:hover,
        .sidebar .sidebar-login.is-active {
            color: #fff;
            border-color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(253, 126, 20, 0.45);
        }
        .sidebar .sidebar-logout {
            display: block;
            width: 100%;
            text-align: center;
            color: #f8d7da;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid #dc3545;
            background: transparent;
            transition: 0.25s;
        }
        .sidebar .sidebar-logout:hover {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
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
        .offcanvas-auth {
            padding: 16px;
            border-top: 1px solid #343a40;
            background: rgba(0, 0, 0, 0.2);
        }
        .offcanvas .offcanvas-login {
            display: block;
            text-align: center;
            text-decoration: none;
            color: #fff;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid #fd7e14;
            background: linear-gradient(135deg, #e67700 0%, #fd7e14 100%);
        }
        .offcanvas .offcanvas-login:hover { color: #fff; }
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
            <a href="/"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Wilayah</a>
            <a href="/rekapitulasi"><i class="fas fa-table-list me-2"></i> Rekapitulasi</a>
            <a href="/hasil-klaster"><i class="fas fa-robot me-2"></i> Hasil Klaster</a>
            <a href="/data-subsls"><i class="fas fa-table me-2"></i> Data Subsls</a>
            @guest
            <div class="offcanvas-auth">
                <a href="{{ route('login') }}" class="offcanvas-login">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </a>
            </div>
            @endguest
        </div>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
