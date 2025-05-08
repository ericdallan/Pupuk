<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Tambahkan dayjs dan locale Indonesia -->
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/id.js"></script>
    <style>
        body {
            background-color: #f0f2f5;
            margin: 0;
        }

        .sidebar {
            background-color: #343a40;
            color: white;
            height: 100vh;
            /* Full viewport height */
            padding-top: 20px;
            width: 200px;
            position: fixed;
            left: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            /* Enable vertical scrolling for the entire sidebar */
        }

        .sidebar .admin-info {
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .admin-info i {
            font-size: 2em;
            margin-bottom: 5px;
        }

        .sidebar .nav {
            flex-grow: 1;
        }

        .sidebar ul {
            padding: 0;
            list-style: none;
            margin-bottom: 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: white;
            display: block;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #495057;
        }

        .content {
            padding: 20px;
            margin-left: 200px;
        }

        .content h2 {
            margin-bottom: 20px;
            color: #343a40;
        }

        .top-navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 25px;
            margin-bottom: 20px;
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-navbar .title h2 {
            margin-bottom: 0;
            font-size: 1.5rem;
        }

        .top-navbar .right-icons {
            display: flex;
            align-items: center;
        }

        .top-navbar .right-icons i,
        .top-navbar .right-icons img {
            margin-left: 15px;
            font-size: 18px;
            max-height: 30px;
        }

        /* Responsive Styles */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -200px;
                width: 200px;
                height: 100vh;
                /* Full height in mobile */
                z-index: 1000;
                transition: left 0.3s ease;
                flex-direction: column;
                overflow-y: auto;
                /* Maintain scrolling in mobile */
            }

            .sidebar.active {
                left: 0;
            }

            .content {
                margin-left: 0;
                transition: margin-left 0.3s ease;
            }

            .content.active {
                margin-left: 200px;
            }
        }

        .sidebar .logout-form {
            margin-top: auto;
            padding: 20px;
            width: 100%;
        }

        .sidebar .logout-form button {
            border: none;
            background: none;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            text-align: left;
            width: 100%;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .sidebar .logout-form button:hover {
            background-color: #495057;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <nav class="sidebar d-flex flex-column">
            @if (auth()->check())
            @php
            $admin = auth()->user();
            @endphp
            @if ($admin)
            <div class="text-center admin-info">
                <a class="nav-link {{ request()->routeIs('admin_profile') ? 'active' : '' }}" href="{{ route('admin_profile') }}">
                    <i class="fas fa-user-circle"></i>
                    <div>{{ $admin->name }}</div>
                </a>
            </div>
            @else
            <div class="text-center admin-info">
                <i class="fas fa-user-circle"></i>
                <div>No Admin Found</div>
            </div>
            @endif
            @else
            <div class="text-center admin-info">
                <i class="fas fa-user-circle"></i>
                <div>No User Session</div>
            </div>
            @endif
            <ul class="nav flex-column">
                <li class="nav-item" hidden>
                    <a class="nav-link {{ request()->routeIs('dashboard_page') ? 'active' : '' }}" href="{{ route('dashboard_page') }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('account_page') ? 'active' : '' }}" href="{{ route('account_page') }}">
                        <i class="fas fa-solid fa-book"></i> Kode Perkiraan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('voucher_page') ? 'active' : '' }}" href="{{ route('voucher_page') }}">
                        <i class="fas fa-file-invoice-dollar"></i> Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('stock_page') ? 'active' : '' }}" href="{{ route('stock_page') }}">
                        <i class="fas fa-file-invoice-dollar"></i> Stock
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('generalledger_page') ? 'active' : '' }}" href="{{ route('generalledger_page') }}">
                        <i class="fas fa-file-alt"></i> Buku Besar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('subsidiary_piutang') ? 'active' : '' }}" href="{{ route('subsidiary_piutang') }}">
                        <i class="fas fa-file"></i> Subsidiary Piutang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('subsidiary_utang') ? 'active' : '' }}" href="{{ route('subsidiary_utang') }}">
                        <i class="fas fa-file"></i> Subsidiary Utang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('trialBalance_page') ? 'active' : '' }}" href="{{ route('trialBalance_page') }}">
                        <i class="fas fa-file"></i> Neraca Saldo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('incomeStatement_page') ? 'active' : '' }}" href="{{ route('incomeStatement_page') }}">
                        <i class="fas fa-file-alt"></i> Laba Rugi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('balanceSheet_page') ? 'active' : '' }}" href="{{ route('balanceSheet_page') }}">
                        <i class="fas fa-file-alt"></i> Neraca Keuangan
                    </a>
                </li>
                <li class="nav-item" hidden>
                    <a class="nav-link {{ request()->routeIs('employee_page') ? 'active' : '' }}" href="{{ route('employee_page') }}">
                        <i class="fas fa-users"></i> Employee
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('company_page') ? 'active' : '' }}" href="{{ route('company_page') }}">
                        <i class="fas fa-building"></i> Perusahaan
                    </a>
                </li>
            </ul>
            <div class="logout-form">
                <form action="{{ route('logout') }}" method="GET">
                    @csrf
                    <button type="submit" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <main class="flex-grow-1 content">
            <div class="top-navbar">
                <div class="d-flex align-items-center">
                    <div class="title mr-2">
                        <h2>@yield('title')</h2>
                    </div>
                </div>
                <div class="right-icons">
                    <img src="{{ asset('logo/LogoInni.png') }}" alt="DeveloperLogo" style="max-width: 75px; max-height: 75px;">
                </div>
            </div>
            @yield('content')
            @stack('scripts')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.navbar-toggler');
            const content = document.querySelector('.content');

            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    content.classList.toggle('active');
                });
            }

            // Set active class for sidebar links based on current route
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(link => {
                if (link.getAttribute('href') === window.location.pathname || link.getAttribute('href') === window.location.route) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>

</html>