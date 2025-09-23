<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1/dist/chartjs-plugin-annotation.min.js"
        type="module"></script>

    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #3730a3;
            --secondary-color: #6366f1;
            --accent-color: #0ea5e9;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-bg: #0f172a;
            --sidebar-bg: #1e293b;
            --text-light: #f8fafc;
            --text-muted: #cbd5e1;
            --border-color: #334155;
            --hover-bg: rgba(255, 255, 255, 0.1);
            --content-bg: #f8fafc;
            --card-bg: #ffffff;
            --shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 4px 8px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--content-bg);
            color: #334155;
            line-height: 1.4;
        }

        .sidebar {
            background: var(--sidebar-bg);
            color: var(--text-light);
            height: 100vh;
            width: 200px;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            box-shadow: var(--shadow);
            z-index: 1000;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .sidebar-header {
            padding: 1rem 0.75rem;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 0.5rem;
        }

        .admin-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            position: relative;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }

        .admin-avatar:hover {
            transform: scale(1.05);
        }

        .admin-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--text-light);
            text-decoration: none;
            transition: color 0.2s ease;
            line-height: 1.2;
        }

        .admin-name:hover {
            color: var(--accent-color);
        }

        .sidebar-nav {
            flex: 1;
            padding: 0 0.5rem;
        }

        .nav-section {
            margin-bottom: 1rem;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            padding: 0 0.5rem;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 0.375rem;
            font-weight: 400;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 2px;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.2s ease;
        }

        .nav-link:hover {
            color: var(--text-light);
            background: var(--hover-bg);
            transform: translateX(3px);
        }

        .nav-link.active {
            color: var(--text-light);
            background: rgba(79, 70, 229, 0.15);
            border-left: 2px solid var(--primary-color);
        }

        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-icon {
            width: 16px;
            text-align: center;
            font-size: 0.9rem;
        }

        .nav-text {
            flex: 1;
        }

        .sidebar-footer {
            padding: 0.75rem;
            border-top: 1px solid var(--border-color);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.5rem;
            background: none;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            color: var(--text-muted);
            font-family: inherit;
            font-weight: 400;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: var(--danger-color);
            color: var(--danger-color);
        }

        .main-content {
            margin-left: 200px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .top-navbar {
            background: var(--card-bg);
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 500;
            box-shadow: var(--shadow);
        }

        .navbar-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .navbar-title h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.4rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }

        .mobile-menu-btn:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand-logo {
            max-height: 32px;
            width: auto;
        }

        .content-wrapper {
            flex: 1;
            padding: 1.5rem;
        }

        /* Mobile Responsiveness */
        @media (max-width: 1024px) {
            .sidebar {
                width: 180px;
            }

            .main-content {
                margin-left: 180px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 200px;
            }

            .sidebar.mobile-active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .top-navbar {
                padding: 0.75rem 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .content-wrapper {
                padding: 1rem;
            }

            .navbar-title h2 {
                font-size: 1.1rem;
            }

            .navbar-right {
                width: 100%;
                justify-content: flex-end;
            }
        }

        @media (max-width: 480px) {
            .content-wrapper {
                padding: 0.75rem;
            }

            .top-navbar {
                padding: 0.5rem;
            }

            .sidebar {
                width: 100%;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Improved animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-wrapper>* {
            animation: slideIn 0.4s ease-out;
        }

        .status-indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--success-color);
            position: absolute;
            top: 6px;
            right: 6px;
        }

        .notification-badge {
            background: var(--danger-color);
            color: white;
            font-size: 0.7rem;
            padding: 1px 5px;
            border-radius: 8px;
            margin-left: auto;
            min-width: 16px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            @if (auth()->guard('admin')->check())
                @php
                    $admin = auth()->guard('admin')->user();
                @endphp
                <div class="admin-info">
                    <a class="admin-name" href="{{ route('admin_profile') }}">
                        <div class="admin-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span>{{ $admin ? $admin->name : 'Administrator' }}</span>
                    </a>
                    <div class="status-indicator"></div>
                </div>
            @elseif (auth()->guard('master')->check())
                @php
                    $master = auth()->guard('master')->user();
                @endphp
                <div class="admin-info">
                    <a class="admin-name" href="{{ route('master_profile') }}">
                        <div class="admin-avatar">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <span>{{ $master ? $master->name : 'Master User' }}</span>
                    </a>
                    <div class="status-indicator"></div>
                </div>
            @else
                <div class="admin-info">
                    <div class="admin-avatar">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <span class="admin-name">No Session</span>
                </div>
            @endif
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Dashboard</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard_page') ? 'active' : '' }}"
                            href="{{ route('dashboard_page') }}">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Akuntansi</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('account_page') ? 'active' : '' }}"
                            href="{{ route('account_page') }}">
                            <i class="nav-icon fas fa-code-branch"></i>
                            <span class="nav-text">Kode Perkiraan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('voucher_page') ? 'active' : '' }}"
                            href="{{ route('voucher_page') }}">
                            <i class="nav-icon fas fa-receipt"></i>
                            <span class="nav-text">Transaksi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('generalledger_page') ? 'active' : '' }}"
                            href="{{ route('generalledger_page') }}">
                            <i class="nav-icon fas fa-book-open"></i>
                            <span class="nav-text">Buku Besar</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Inventori</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('stock_page') ? 'active' : '' }}"
                            href="{{ route('stock_page') }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <span class="nav-text">Stock</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Subsidiary</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('subsidiary_piutang') ? 'active' : '' }}"
                            href="{{ route('subsidiary_piutang') }}">
                            <i class="nav-icon fas fa-hand-holding-usd"></i>
                            <span class="nav-text">Piutang</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('subsidiary_utang') ? 'active' : '' }}"
                            href="{{ route('subsidiary_utang') }}">
                            <i class="nav-icon fas fa-credit-card"></i>
                            <span class="nav-text">Utang</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Laporan</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('trialBalance_page') ? 'active' : '' }}"
                            href="{{ route('trialBalance_page') }}">
                            <i class="nav-icon fas fa-balance-scale"></i>
                            <span class="nav-text">Neraca Saldo</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('incomeStatement_page') ? 'active' : '' }}"
                            href="{{ route('incomeStatement_page') }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <span class="nav-text">Laba Rugi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('balanceSheet_page') ? 'active' : '' }}"
                            href="{{ route('balanceSheet_page') }}">
                            <i class="nav-icon fas fa-file-contract"></i>
                            <span class="nav-text">Neraca Keuangan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('zakat_page') ? 'active' : '' }}"
                            href="{{ route('zakat_page') }}">
                            <i class="nav-icon fas fa-mosque"></i>
                            <span class="nav-text">Zakat</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Pengaturan</div>
                <ul class="nav-list">
                    <li class="nav-item" style="display: none;">
                        <a class="nav-link {{ request()->routeIs('employee_page') ? 'active' : '' }}"
                            href="{{ route('employee_page') }}">
                            <i class="nav-icon fas fa-users"></i>
                            <span class="nav-text">Employee</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('company_page') ? 'active' : '' }}"
                            href="{{ route('company_page') }}">
                            <i class="nav-icon fas fa-building"></i>
                            <span class="nav-text">Perusahaan</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="navbar-title">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>@yield('title')</h2>
            </div>
            <div class="navbar-right">
                <img src="{{ asset('logo/LogoInniDigi.png') }}" alt="InniDigi Logo" class="brand-logo">
            </div>
        </header>

        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

    @stack('scripts')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Mobile menu toggle
            function toggleMobileMenu() {
                sidebar.classList.toggle('mobile-active');
                sidebarOverlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('mobile-active') ? 'hidden' : '';
            }

            // Event listeners
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleMobileMenu);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleMobileMenu);
            }

            // Close mobile menu on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-active');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Enhanced active link detection
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href');
                if (linkPath && (linkPath === currentPath || currentPath.startsWith(linkPath + '/'))) {
                    link.classList.add('active');
                }
            });

            // Smooth scroll behavior
            document.documentElement.style.scrollBehavior = 'smooth';

            // Add loading states for navigation
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.getAttribute('href') && this.getAttribute('href') !== '#') {
                        this.style.opacity = '0.7';
                        this.style.pointerEvents = 'none';
                        setTimeout(() => {
                            this.style.opacity = '';
                            this.style.pointerEvents = '';
                        }, 800);
                    }
                });
            });
        });
    </script>
</body>

</html>
