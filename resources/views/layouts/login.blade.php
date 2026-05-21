<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Monitoring SE2026</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(145deg, #1a1d21 0%, #212529 45%, #3d280d 100%);
            padding: 24px;
        }
        .login-page {
            width: 100%;
            max-width: 420px;
        }
        .login-brand {
            text-align: center;
            color: #fff;
            margin-bottom: 28px;
        }
        .login-brand .brand-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            background: linear-gradient(135deg, #e67700, #fd7e14);
            box-shadow: 0 8px 24px rgba(253, 126, 20, 0.4);
        }
        .login-brand h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .login-brand p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.65);
            margin: 0;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }
        .login-card-header {
            background: linear-gradient(135deg, #e67700 0%, #fd7e14 100%);
            color: #fff;
            padding: 20px 28px;
            text-align: center;
        }
        .login-card-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 4px;
        }
        .login-card-header span {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        .login-card-body {
            padding: 28px;
        }
        .login-card-body .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #495057;
        }
        .login-card-body .form-control {
            border-radius: 8px;
            padding: 10px 14px;
        }
        .login-card-body .form-control:focus {
            border-color: #fd7e14;
            box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.3);
        }
        .btn-login-submit {
            background: linear-gradient(135deg, #e67700 0%, #fd7e14 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 12px;
            transition: 0.25s;
        }
        .btn-login-submit:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(253, 126, 20, 0.4);
        }
        .login-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.2s;
        }
        .login-back:hover {
            color: #fd7e14;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-brand">
            <div class="brand-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h1>MONITORING SE</h1>
            <p>Sensus Ekonomi 2026</p>
        </div>

        @yield('content')

        <a href="/" class="login-back">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke beranda
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
