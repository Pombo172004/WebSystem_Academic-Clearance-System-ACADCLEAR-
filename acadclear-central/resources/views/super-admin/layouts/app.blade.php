<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AcadClear') }} - Super Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ─── Typography ────────────────────────────────── */
        body, p, span, td, th, li, a, label, input, select, textarea, .card-body, .dropdown-item {
            font-family: 'Poppins', sans-serif !important;
        }

        h1, h2, h3, h4, h5, h6,
        .h1, .h2, .h3, .h4, .h5, .h6,
        .sidebar-brand-text,
        .card-header h6,
        .card-title {
            font-family: 'Montserrat', sans-serif !important;
            color: #1B1B1B !important;
        }

        /* ─── Card Headers (Stats) ──────────────────────── */
        .card .text-xs.font-weight-bold.text-uppercase.mb-1 {
            color: #122C4F !important;
        }

        .card .h5.mb-0.font-weight-bold {
            color: #5B88B2 !important;
        }

        /* ─── Main Text Color ───────────────────────────── */
        #content-wrapper {
            color: #0e1326;
        }

        #content-wrapper p, 
        #content-wrapper td, 
        #content-wrapper th, 
        #content-wrapper li, 
        #content-wrapper label {
            color: #0e1326;
        }

        /* ─── Main Background ───────────────────────────── */
        body#page-top {
            background-color: #FFFFFF !important;
        }

        #content-wrapper {
            background-color: #FFFFFF !important;
        }

        .container-fluid {
            background-color: #FFFFFF !important;
        }

        /* ─── Sidebar ───────────────────────────────────── */
        .sidebar {
            background-color: #122C4F !important;
        }

        /* Override the sb-admin-2 gradient */
        .bg-gradient-primary {
            background-color: #122C4F !important;
            background-image: none !important;
        }

        .sidebar .sidebar-brand {
            background: rgba(0,0,0,0.15) !important;
        }

        .sidebar .sidebar-brand-text,
        .sidebar .nav-link span,
        .sidebar .nav-link i,
        .sidebar .sidebar-heading,
        .sidebar hr.sidebar-divider {
            color: #FBF9E4 !important;
        }

        .sidebar .sidebar-heading {
            font-family: 'Montserrat', sans-serif !important;
            color: rgba(251,249,228,0.6) !important;
            font-size: 0.65rem;
            letter-spacing: 0.1em;
        }

        .sidebar hr.sidebar-divider {
            border-color: rgba(251,249,228,0.2) !important;
        }

        .sidebar .nav-item .nav-link {
            color: rgba(251,249,228,0.85) !important;
            transition: all 0.2s ease !important;
        }

        .sidebar .nav-item .nav-link:hover {
            color: #FBF9E4 !important;
            background: rgba(251,249,228,0.12) !important;
            border-radius: 6px;
        }

        .sidebar .nav-item.active .nav-link {
            color: #FBF9E4 !important;
            font-weight: 600;
        }

        .sidebar .nav-item.active .nav-link::before {
            border-right-color: #FBF9E4 !important;
        }

        /* ─── Topbar / Navbar ───────────────────────────── */
        .topbar {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e8e5cc !important;
        }

        /* ─── Cards ─────────────────────────────────────── */
        .card {
            background-color: #fdfcf8ff !important;
            border: none !important;
            box-shadow: 0 2px 12px rgba(18,44,79,0.08) !important;
            border-radius: 10px !important;
        }

        .card-header {
            background-color: #fdfcf8ff !important;
            border-bottom: 1px solid #e8e5cc !important;
            border-radius: 10px 10px 0 0 !important;
        }

        .card-header h6 {
            color: #1B1B1B !important;
        }

        /* ─── Tables ────────────────────────────────────── */
        .table, .table td, .table th {
            background-color: #fdfcf8ff !important;
            --bs-table-bg: #fdfcf8ff !important;
        }

        /* ─── Buttons ───────────────────────────────────── */
        .btn:not(.btn-link):not(.btn-close),
        button:not(.btn-link):not(.btn-close):not(.navbar-toggler) {
            background-color: #32435d !important;
            border-color: #32435d !important;
            color: #FFFFFF !important;
        }

        .btn:not(.btn-link):not(.btn-close):hover,
        button:not(.btn-link):not(.btn-close):not(.navbar-toggler):hover {
            background-color: #253246 !important;
            border-color: #253246 !important;
            color: #FFFFFF !important;
        }

        /* ─── Misc fixes ─────────────────────────────────── */
        .text-gray-800 {
            color: #1B1B1B !important;
        }

        .fa-check, .fa-times {
            color: #122C4F !important;
            font-weight: 900 !important;
            -webkit-text-stroke: 1px #122C4F;
            margin-right: 32px;
            margin-left: 2px;
        }

        .table .fa-check, .table .fa-times {
            margin-right: 0;
            margin-left: 0;
            font-size: 1.2rem;
        }

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
    @stack('scripts')
</body>
</html>