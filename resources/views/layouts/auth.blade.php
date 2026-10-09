<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') | Maha E-Seva ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/erp-custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --font-heading: 'Outfit', sans-serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
            --primary-gradient: linear-gradient(135deg, #ff7700 0%, #ea580c 50%, #c2410c 100%);
            --shadow-glow: 0 0 25px -5px rgba(249, 115, 22, 0.4);
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-sm: 6px;
            --surface-border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-body);
            color: var(--text-main);
            background: #0f172a;
            -webkit-font-smoothing: antialiased;
        }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        .auth-wrapper::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
        }
        .auth-wrapper::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.15) 0%, transparent 70%);
            bottom: -80px;
            left: -80px;
            border-radius: 50%;
        }
        .auth-box {
            background: #ffffff;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 460px;
            padding: 38px 34px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }
        .auth-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }
        .auth-brand-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            box-shadow: var(--shadow-glow);
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-label.required::after {
            content: ' *';
            color: #ef4444;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            color: #0f172a;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            border-radius: var(--radius-sm);
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-primary {
            background: var(--primary-gradient);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(234, 88, 12, 0.4);
        }
        .btn-secondary {
            background: #f8fafc;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }
        .alert {
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-box">
            @yield('content')
        </div>
    </div>
</body>
</html>
