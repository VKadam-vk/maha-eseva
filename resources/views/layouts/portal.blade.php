<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Application Tracker') | Maha E-Seva Portal</title>
    <link rel="stylesheet" href="{{ asset('css/erp-custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .portal-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 24px 0;
            border-bottom: 3px solid #ea580c;
        }
        .portal-nav-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .portal-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }
        .portal-content {
            max-width: 1000px;
            margin: 36px auto;
            padding: 0 20px;
            flex-grow: 1;
        }
        .portal-footer {
            background-color: #0f172a;
            color: #94a3b8;
            padding: 24px 0;
            text-align: center;
            font-size: 13px;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
    @stack('styles')
</head>
<body style="min-height: 100vh; display: flex; flex-direction: column; background-color: #f8fafc;">
    <header class="portal-header">
        <div class="portal-nav-container">
            <a href="{{ route('portal.track') }}" class="portal-brand">
                <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff;">
                    <i class="fa-solid fa-landmark"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; color: #ffffff; line-height: 1.1;">Maha E-Seva</h2>
                    <span style="font-size: 11px; color: #fb923c; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Public Citizen Service Portal</span>
                </div>
            </a>

            <div>
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm" style="background: rgba(255, 255, 255, 0.1); color: #fff; border-color: rgba(255, 255, 255, 0.2);">
                    <i class="fa-solid fa-lock"></i> Operator Sign In
                </a>
            </div>
        </div>
    </header>

    <main class="portal-content">
        @yield('content')
    </main>

    <footer class="portal-footer">
        <p>© {{ date('Y') }} Maha E-Seva Digital Citizen Portal. All Rights Reserved. Govt of Maharashtra Integrated Services.</p>
    </footer>

    @stack('scripts')
</body>
</html>
