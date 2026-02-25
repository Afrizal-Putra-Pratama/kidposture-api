<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - Posturely</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-favicon-posturely.svg') }}">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-layout" id="adminLayout">
        
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <button class="floating-toggle-btn" id="openSidebarBtn" title="Open Menu">
            <i class="bi bi-list"></i>
        </button>
        
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar__header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-logo">
                    <img src="{{ asset('images/logo-posturely.svg') }}" alt="Posturely Logo" style="height: 38px; width: auto; max-width: 150px; object-fit: contain;">
                </a>
                
                <button class="sidebar-toggle-btn" id="closeSidebarBtn" title="Close Menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <nav class="admin-nav">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav__link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                <a href="{{ route('admin.articles.index') }}" class="admin-nav__link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Articles
                </a>

                <a href="{{ route('admin.categories.index') }}" class="admin-nav__link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Categories
                </a>

                <a href="{{ route('admin.physiotherapists.index') }}" class="admin-nav__link {{ request()->routeIs('admin.physiotherapists.*') ? 'active' : '' }}">
                    <i class="bi bi-hospital"></i> Physiotherapists
                </a>

                <a href="{{ route('admin.users.index') }}" class="admin-nav__link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>

                <div class="admin-nav__divider"></div>

                <form action="{{ route('logout') }}" method="POST" class="admin-nav__form">
                    @csrf
                    <button type="submit" class="admin-nav__link admin-nav__link--danger" style="width: 100%; text-align: left;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </nav>
        </aside>

        <div class="admin-main">
            
            <header class="admin-topbar">
                <div class="admin-topbar__spacer"></div>
                
                <div class="user-menu">
                    <button class="user-menu__toggle">
                        <i class="bi bi-person-circle"></i> 
                        <span class="user-name-text">{{ auth()->user()->name ?? 'Admin User' }}</span>
                        <i class="bi bi-chevron-down ms-1" style="font-size: 0.8rem;"></i>
                    </button>
                    <div class="user-menu__dropdown">
                        <a href="#" class="user-menu__item">Profile</a>
                        <div class="user-menu__divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="user-menu__item user-menu__item--danger">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="admin-content">
                @if(session('success'))
                    <div class="alert alert--success">
                        <i class="bi bi-check-circle-fill alert__icon"></i>
                        <span class="alert__text">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert--danger">
                        <i class="bi bi-exclamation-triangle-fill alert__icon"></i>
                        <span class="alert__text">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const layout = document.getElementById('adminLayout');
            const closeBtn = document.getElementById('closeSidebarBtn'); 
            const openBtn = document.getElementById('openSidebarBtn');   
            const overlay = document.getElementById('sidebarOverlay');

            // Fungsi Inti Toggle
            function toggleSidebar() {
                if (window.innerWidth <= 1024) {
                    layout.classList.toggle('mobile-active');
                } else {
                    layout.classList.toggle('desktop-collapsed');
                }
            }

            // Saat Tombol (Di Dalam Sidebar) Diklik
            closeBtn.addEventListener('click', toggleSidebar);
            
            // Saat Tombol Floating (Di Luar Sidebar) Diklik
            openBtn.addEventListener('click', toggleSidebar);

            // Saat Overlay Gelap (Di Mobile) Diklik
            overlay.addEventListener('click', function() {
                layout.classList.remove('mobile-active');
            });

            // Handle Resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    layout.classList.remove('mobile-active');
                } else {
                    layout.classList.remove('desktop-collapsed');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>