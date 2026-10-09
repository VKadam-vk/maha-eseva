<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Maha E-Seva ERP</title>
    <link rel="stylesheet" href="{{ asset('css/erp-custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('styles')
</head>
<body>
    <div class="erp-container">
        <!-- Sidebar -->
        <aside class="erp-sidebar">
            <div class="brand-header">
                <div class="brand-logo-icon">
                    <i class="fa-solid fa-landmark"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-title">Maha E-Seva</span>
                    <span class="brand-subtitle">ERP Enterprise</span>
                </div>
            </div>

            <nav class="erp-nav">
                <a href="{{ route('dashboard') }}" class="erp-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('fast_entry.index') }}" class="erp-nav-link {{ request()->routeIs('fast_entry.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Fast Daily Entry</span>
                </a>

                <div class="erp-nav-section-title">Operations</div>

                <a href="{{ route('customers.index') }}" class="erp-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Customers CRM</span>
                </a>

                <a href="{{ route('applications.index') }}" class="erp-nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Applications</span>
                </a>

                <a href="{{ route('services.index') }}" class="erp-nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Services Master</span>
                </a>

                <a href="{{ route('followups.index') }}" class="erp-nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Follow-ups</span>
                </a>

                <a href="{{ route('leads.index') }}" class="erp-nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-headset"></i>
                    <span>Enquiries / Leads</span>
                </a>

                <div class="erp-nav-section-title">Financials</div>

                <a href="{{ route('payments.index') }}" class="erp-nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                    <span>Collections & Receipts</span>
                </a>

                <a href="{{ route('invoices.index') }}" class="erp-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Invoices</span>
                </a>

                <div class="erp-nav-section-title">Administration</div>

                @if(Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner())
                    <a href="{{ route('branches.index') }}" class="erp-nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-code-branch"></i>
                        <span>Branches</span>
                    </a>
                @endif

                <a href="{{ route('employees.index') }}" class="erp-nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Staff & Operators</span>
                </a>

                <a href="{{ route('reports.index') }}" class="erp-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Reports Center</span>
                </a>

                <a href="{{ route('import_export.import.customers') }}" class="erp-nav-link {{ request()->routeIs('import_export.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Import & Export</span>
                </a>

                @if(Auth::user()->isSuperAdmin() || Auth::user()->isBusinessOwner())
                    <a href="{{ route('audit.index') }}" class="erp-nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Audit Security</span>
                    </a>

                    <a href="{{ route('settings.index') }}" class="erp-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Settings</span>
                    </a>
                @endif
            </nav>
        </aside>

        <!-- Main Wrapper -->
        <div class="erp-main-content">
            <!-- Topbar -->
            <header class="erp-topbar">
                <div class="erp-topbar-left">
                    <form action="{{ route('applications.index') }}" method="GET" class="erp-search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" placeholder="Search SR number, Customer name, Mobile or Ack..." value="{{ request('search') }}">
                    </form>
                </div>

                <div class="erp-topbar-right">
                    @if(Auth::user()->branch)
                        <span class="badge-branch">
                            <i class="fa-solid fa-building"></i>
                            {{ Auth::user()->branch->name }}
                        </span>
                    @elseif(Auth::user()->isBusinessOwner())
                        <span class="badge-branch">
                            <i class="fa-solid fa-crown"></i>
                            {{ Auth::user()->tenant->name ?? 'Headquarters' }}
                        </span>
                    @endif

                    <a href="{{ route('portal.track') }}" target="_blank" class="btn btn-secondary btn-sm" title="Open Public Customer Tracking Portal">
                        <i class="fa-solid fa-external-link"></i> Customer Tracker
                    </a>

                    <a href="{{ route('profile') }}" class="user-profile-pill">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 13px; font-weight: 700; line-height: 1.2;">{{ Auth::user()->name }}</span>
                            <span style="font-size: 10.5px; color: var(--text-muted);">{{ Auth::user()->roles->first()?->name ?? 'Operator' }}</span>
                        </div>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Secure Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Body -->
            <main class="erp-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
