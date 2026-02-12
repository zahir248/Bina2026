<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - BINA</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Client Styles (for toast notifications) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #3b82f6;
            --sidebar-text: #e2e8f0;
            --sidebar-text-muted: #94a3b8;
            --header-bg: #ffffff;
            --border-color: #e2e8f0;
            --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --card-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 14px;
            line-height: 1.6;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 260px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: var(--sidebar-bg);
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: var(--sidebar-hover);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-logo-link {
            display: block;
            text-decoration: none;
            cursor: pointer;
            transition: opacity 0.2s ease;
            text-align: center;
            width: 100%;
        }

        .sidebar-logo-link:hover {
            opacity: 0.8;
        }

        .sidebar-logo-img {
            max-width: 100%;
            height: auto;
            max-height: 40px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .sidebar-nav {
            padding: 16px 0;
            flex: 1;
            overflow-y: auto;
        }
        
        .sidebar-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .nav-item {
            margin: 4px 12px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: var(--sidebar-text-muted);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: var(--sidebar-text);
        }

        .nav-link.active {
            background-color: var(--sidebar-active);
            color: white;
        }

        .nav-link i {
            width: 20px;
            font-size: 18px;
        }
        
        .nav-link.logout {
            color: #ef4444;
        }
        
        .nav-link.logout:hover {
            background-color: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
        }
        
        /* Dropdown Styles */
        .nav-link.dropdown-toggle {
            position: relative;
            cursor: pointer;
        }
        
        .nav-link.dropdown-toggle::after {
            content: '\f282';
            font-family: 'bootstrap-icons';
            margin-left: auto;
            transition: transform 0.2s ease;
            border: none;
            font-size: 12px;
        }
        
        .nav-item.show .nav-link.dropdown-toggle::after {
            transform: rotate(180deg);
        }
        
        .nav-submenu {
            list-style: none;
            padding: 0;
            margin: 4px 0 0 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .nav-item.show .nav-submenu {
            max-height: 500px;
        }
        
        .nav-submenu-item {
            margin: 2px 12px;
        }
        
        .nav-submenu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px 8px 48px;
            color: var(--sidebar-text-muted);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 400;
            font-size: 13px;
        }
        
        .nav-submenu-link:hover {
            background-color: var(--sidebar-hover);
            color: var(--sidebar-text);
        }
        
        .nav-submenu-link.active {
            background-color: rgba(59, 130, 246, 0.2);
            color: var(--sidebar-text);
        }
        
        .nav-submenu-link i {
            width: 16px;
            font-size: 14px;
        }

        /* Main Content Area */
        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 16px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Content Area */
        .admin-content {
            padding: 32px;
        }

        /* Cards */
        .admin-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .card-body {
            color: #64748b;
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            padding: 24px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow-lg);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-icon.blue {
            background-color: #dbeafe;
            color: #3b82f6;
        }

        .stat-icon.green {
            background-color: #d1fae5;
            color: #10b981;
        }

        .stat-icon.purple {
            background-color: #ede9fe;
            color: #8b5cf6;
        }

        .stat-icon.orange {
            background-color: #fed7aa;
            color: #f97316;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }

        /* Buttons */
        .btn-admin {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-admin-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-admin-primary:hover {
            background-color: #2563eb;
            color: white;
        }

        .btn-admin-secondary {
            background-color: #f1f5f9;
            color: #475569;
        }

        .btn-admin-secondary:hover {
            background-color: #e2e8f0;
            color: #334155;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-content {
                padding: 16px;
            }
        }

    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-wrapper">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-logo-link">
                    <img src="{{ asset('images/bina-logo.png') }}" alt="BINA Logo" class="sidebar-logo-img">
                </a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </div>
                <div class="nav-item dropdown {{ request()->routeIs('admin.events.categories*') || request()->routeIs('admin.events.index*') || request()->routeIs('admin.events.store') || request()->routeIs('admin.events.update') || request()->routeIs('admin.events.destroy') || request()->routeIs('admin.events.schedules*') || request()->routeIs('admin.events.personnel*') || request()->routeIs('admin.events.tickets*') ? 'show' : '' }}">
                    <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('admin.events.categories*') || request()->routeIs('admin.events.index*') || request()->routeIs('admin.events.store') || request()->routeIs('admin.events.update') || request()->routeIs('admin.events.destroy') || request()->routeIs('admin.events.schedules*') || request()->routeIs('admin.events.personnel*') || request()->routeIs('admin.events.tickets*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event"></i>
                        <span>Events</span>
                    </a>
                    <ul class="nav-submenu">
                        <li class="nav-submenu-item">
                            <a href="{{ route('admin.events.categories') }}" class="nav-submenu-link {{ request()->routeIs('admin.events.categories*') ? 'active' : '' }}">
                                <i class="bi bi-tag"></i>
                                <span>Category</span>
                            </a>
                        </li>
                        <li class="nav-submenu-item">
                            <a href="{{ route('admin.events.index') }}" class="nav-submenu-link {{ request()->routeIs('admin.events.index*') || request()->routeIs('admin.events.store') || request()->routeIs('admin.events.update') || request()->routeIs('admin.events.destroy') ? 'active' : '' }}">
                                <i class="bi bi-calendar-check"></i>
                                <span>Event</span>
                            </a>
                        </li>
                        <li class="nav-submenu-item">
                            <a href="{{ route('admin.events.schedules') }}" class="nav-submenu-link {{ request()->routeIs('admin.events.schedules*') ? 'active' : '' }}">
                                <i class="bi bi-clock"></i>
                                <span>Schedule</span>
                            </a>
                        </li>
                        <li class="nav-submenu-item">
                            <a href="{{ route('admin.events.personnel') }}" class="nav-submenu-link {{ request()->routeIs('admin.events.personnel*') ? 'active' : '' }}">
                                <i class="bi bi-person-badge"></i>
                                <span>Event Personnel</span>
                            </a>
                        </li>
                        <li class="nav-submenu-item">
                            <a href="{{ route('admin.events.tickets') }}" class="nav-submenu-link {{ request()->routeIs('admin.events.tickets*') ? 'active' : '' }}">
                                <i class="bi bi-ticket-perforated"></i>
                                <span>Ticket</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.promo-codes') }}" class="nav-link {{ request()->routeIs('admin.promo-codes*') ? 'active' : '' }}">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>Promo Code</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.affiliate-codes') }}" class="nav-link {{ request()->routeIs('admin.affiliate-codes*') ? 'active' : '' }}">
                        <i class="bi bi-link-45deg"></i>
                        <span>Affiliate Code</span>
                    </a>
                </div>
            </nav>
            <div class="sidebar-footer">
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-person"></i>
                        <span>Profile</span>
                    </a>
                </div>
                <div class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="nav-link logout" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Content -->
        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Handle sidebar dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggle = document.querySelector('.nav-link.dropdown-toggle');
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const navItem = this.closest('.nav-item');
                    navItem.classList.toggle('show');
                });
            }
        });
        
        // Toast notification handler - available on all pages
        document.addEventListener('DOMContentLoaded', function() {
            const errorToast = document.getElementById('error-toast');
            const messageToast = document.getElementById('message-toast');
            
            // Handle error toast
            if (errorToast) {
                setTimeout(() => {
                    errorToast.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    errorToast.classList.remove('show');
                }, 5000);
            }
            
            // Handle success/error message toast
            if (messageToast) {
                setTimeout(() => {
                    messageToast.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    messageToast.classList.remove('show');
                }, 3000);
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
