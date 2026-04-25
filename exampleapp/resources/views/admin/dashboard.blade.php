@extends('layouts.app')

@section('content')
@php
    $canManageColleges    = auth()->user()->hasPermission('tenant.colleges.manage');
    $canManageDepartments = auth()->user()->hasPermission('tenant.departments.manage');
    $canManageStudents    = auth()->user()->hasPermission('tenant.students.manage');
    $canManageStaff       = auth()->user()->hasPermission('tenant.staff.manage');
    $canViewReports       = auth()->user()->hasPermission('tenant.reports.view');
@endphp

<style>
    /* ── Rounded cards site-wide ── */
    .card { border-radius: 1rem !important; overflow: hidden; }
    .card-header { border-radius: 1rem 1rem 0 0 !important; }

    /* ── Clickable stat card ── */
    a.stat-card-link {
        display: block;
        text-decoration: none !important;
        color: inherit;
        transition: transform .18s ease, box-shadow .18s ease;
        border-radius: 1rem;
    }
    a.stat-card-link:hover {
        transform: translateY(-4px);
        box-shadow: 0 .75rem 1.75rem rgba(0,0,0,.12) !important;
    }
    a.stat-card-link:hover .card { box-shadow: none !important; }

    /* ── Unified accent button ── */
    .btn-accent {
        background-color: var(--tenant-primary, #5B88B2) !important;
        border-color:     var(--tenant-primary, #5B88B2) !important;
        color: #fff !important;
        border-radius: .6rem !important;
        transition: filter .15s ease;
    }
    .btn-accent:hover { filter: brightness(.88); color: #fff !important; }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
</div>

{{-- ── Statistics Cards (fully clickable) ── --}}
<div class="row">

    {{-- Colleges --}}
    <div class="col-xl-3 col-md-6 mb-4">
        @if($canManageColleges)
            <a href="{{ route('admin.colleges.index') }}" class="stat-card-link" title="Manage Colleges">
        @else
            <span class="stat-card-link" style="cursor:default;">
        @endif
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Colleges</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\College::count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-university fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        @if($canManageColleges)
            </a>
        @else
            </span>
        @endif
    </div>

    {{-- Departments --}}
    <div class="col-xl-3 col-md-6 mb-4">
        @if($canManageDepartments)
            <a href="{{ route('admin.departments.index') }}" class="stat-card-link" title="Manage Departments">
        @else
            <span class="stat-card-link" style="cursor:default;">
        @endif
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Departments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Department::count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        @if($canManageDepartments)
            </a>
        @else
            </span>
        @endif
    </div>

    {{-- Students --}}
    <div class="col-xl-3 col-md-6 mb-4">
        @if($canManageStudents)
            <a href="{{ route('admin.students.index') }}" class="stat-card-link" title="Manage Students">
        @else
            <span class="stat-card-link" style="cursor:default;">
        @endif
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\User::where('role', 'student')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        @if($canManageStudents)
            </a>
        @else
            </span>
        @endif
    </div>

    {{-- Staff --}}
    <div class="col-xl-3 col-md-6 mb-4">
        @if($canManageStaff)
            <a href="{{ route('admin.staff.index') }}" class="stat-card-link" title="Manage Staff">
        @else
            <span class="stat-card-link" style="cursor:default;">
        @endif
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Staff</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\User::where('role', 'staff')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        @if($canManageStaff)
            </a>
        @else
            </span>
        @endif
    </div>

</div>

{{-- ── Quick Actions (all accent color) ── --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-dark">Quick Actions</h6>
            </div>
            <div class="card-body d-flex flex-wrap gap-2" style="gap:.6rem;">
                @if($canManageColleges)
                    <a href="{{ route('admin.colleges.create') }}" class="btn btn-accent mb-2">
                        <i class="fas fa-plus mr-1"></i> Add College
                    </a>
                @endif
                @if($canManageDepartments)
                    <a href="{{ route('admin.departments.create') }}" class="btn btn-accent mb-2">
                        <i class="fas fa-plus mr-1"></i> Add Department
                    </a>
                @endif
                @if($canManageStudents)
                    <a href="{{ route('admin.students.create') }}" class="btn btn-accent mb-2">
                        <i class="fas fa-user-plus mr-1"></i> Add Student
                    </a>
                @endif
                @if($canManageStaff)
                    <a href="{{ route('admin.staff.create') }}" class="btn btn-accent mb-2">
                        <i class="fas fa-user-tie mr-1"></i> Add Staff
                    </a>
                @endif
                @if($canViewReports)
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-accent mb-2">
                        <i class="fas fa-chart-bar mr-1"></i> View Reports
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Recent Students ── --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-dark">Recently Added Students</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>College</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\User::where('role', 'student')->latest()->take(5)->get() as $student)
                            <tr>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->college->name ?? 'N/A' }}</td>
                                <td>{{ $student->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No students added yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection