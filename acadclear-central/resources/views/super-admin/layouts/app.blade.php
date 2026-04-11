<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AcadClear') }} - Super Admin</title>
    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('acadclear-theme');
                if (savedTheme === 'dark') {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        @media (min-width: 768px) {
            #accordionSidebar {
                position: sticky;
                top: 0;
                height: 100vh;
                overflow-y: auto;
                flex-shrink: 0;
            }

            #content-wrapper {
                min-height: 100vh;
            }
        }

        .dark-mode body,
        .dark-mode {
            background-color: #111827;
            color: #e5e7eb;
        }

        .dark-mode #content,
        .dark-mode #content-wrapper,
        .dark-mode .container-fluid,
        .dark-mode .card,
        .dark-mode .card-header,
        .dark-mode .card-footer,
        .dark-mode .modal-content,
        .dark-mode .dropdown-menu,
        .dark-mode .list-group-item,
        .dark-mode .table,
        .dark-mode .table thead th,
        .dark-mode .table td,
        .dark-mode .table th,
        .dark-mode .form-control,
        .dark-mode .form-select,
        .dark-mode .input-group-text,
        .dark-mode .topbar,
        .dark-mode .sticky-footer {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }

        .dark-mode .bg-white,
        .dark-mode .bg-light,
        .dark-mode .table-light,
        .dark-mode .thead-light th,
        .dark-mode .table thead,
        .dark-mode .table tbody,
        .dark-mode .table tr,
        .dark-mode .border,
        .dark-mode .table-bordered,
        .dark-mode .table-bordered td,
        .dark-mode .table-bordered th {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }

        .dark-mode .text-gray-800,
        .dark-mode .text-gray-700,
        .dark-mode .text-gray-600,
        .dark-mode .text-dark,
        .dark-mode .card-header,
        .dark-mode .card-footer,
        .dark-mode .dropdown-item,
        .dark-mode .navbar-light .navbar-nav .nav-link,
        .dark-mode .small,
        .dark-mode label,
        .dark-mode .copyright,
        .dark-mode .modal-title,
        .dark-mode .modal-body {
            color: #e5e7eb !important;
        }

        .dark-mode .dropdown-item:hover,
        .dark-mode .dropdown-item:focus,
        .dark-mode .list-group-item:hover {
            background-color: #111827 !important;
        }

        .dark-mode .form-control::placeholder,
        .dark-mode .form-select::placeholder {
            color: #9ca3af;
        }

        .dark-mode .btn-light,
        .dark-mode .btn-outline-secondary,
        .dark-mode .btn-secondary {
            background-color: #374151;
            border-color: #4b5563;
            color: #e5e7eb;
        }

        .theme-toggle-btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            border-radius: 999px;
            width: 2.4rem;
            height: 2.4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .dark-mode .theme-toggle-btn {
            background: #111827;
            border-color: #4b5563;
            color: #fbbf24;
        }
    </style>
    @stack('styles')
</head>
<body id="page-top">
    <div id="wrapper">
        @include('super-admin.layouts.sidebar')
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('super-admin.layouts.navigation')
                
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>
    <script>
        (function () {
            var root = document.documentElement;
            var btn = document.getElementById('themeToggleBtn');
            var icon = document.getElementById('themeToggleIcon');

            function setIcon() {
                if (!icon) return;
                icon.className = root.classList.contains('dark-mode') ? 'fas fa-sun' : 'fas fa-moon';
            }

            setIcon();

            if (btn) {
                btn.addEventListener('click', function () {
                    root.classList.toggle('dark-mode');
                    try {
                        localStorage.setItem('acadclear-theme', root.classList.contains('dark-mode') ? 'dark' : 'light');
                    } catch (e) {}
                    setIcon();
                });
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>