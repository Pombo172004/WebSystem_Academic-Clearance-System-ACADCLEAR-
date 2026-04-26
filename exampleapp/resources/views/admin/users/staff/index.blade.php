

@extends('layouts.app')

@section('content')
@php
    $canManageStaff = auth()->user()->hasPermission('tenant.staff.manage');
@endphp
@push('styles')
<style>
    .staff-page .section-card .card-header {
        background: #f8f9fc;
    }

    .staff-page .staff-table thead th {
        white-space: nowrap;
    }

    .staff-page .staff-table td {
        vertical-align: middle;
    }

    .staff-page .actions-cell {
        white-space: nowrap;
        min-width: 96px;
    }

    .staff-page .actions-cell .btn {
        margin-right: 0.25rem;
    }

    .staff-page .actions-cell .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush
<div class="staff-page">
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Staff Members</h1>
    @if($canManageStaff)
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Staff
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@php
    $groupOrder = ['office', 'academic', 'hybrid'];
    $groupLabels = [
        'office' => 'Office-based Staff',
        'academic' => 'Academic Staff',
        'hybrid' => 'Hybrid Assignments',
    ];

    $groupedStaff = $staff->getCollection()->groupBy(function ($member) {
        return \App\Models\User::staffAssignmentScope($member->office_role, $member->college_id, $member->department_id);
    });
@endphp

@if($staff->isEmpty())
    <div class="card shadow mb-4">
        <div class="card-body text-center">No staff found.</div>
    </div>
@else
    @foreach($groupOrder as $scope)
        @continue(!$groupedStaff->has($scope))

        <div class="card shadow mb-4 section-card">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ $groupLabels[$scope] }}</h6>
                <span class="badge badge-light border">{{ $groupedStaff->get($scope)->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered staff-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>College</th>
                                <th>Department</th>
                                <th>Office Role</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedStaff->get($scope) as $member)
                                @php
                                    $assignmentScope = \App\Models\User::staffAssignmentScope($member->office_role, $member->college_id, $member->department_id);
                                @endphp
                                <tr>
                                    <td>{{ $member->id }}</td>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->college->name ?? ($assignmentScope === 'office' ? 'Office-wide' : 'Not set') }}</td>
                                    <td>{{ $member->department->name ?? ($assignmentScope === 'office' ? 'Office-wide' : 'Not set') }}</td>
                                    <td>{{ $member->office_role_label ?? 'Not set' }}</td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                    <td class="actions-cell">
                                        @if($canManageStaff)
                                            <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" class="d-inline-block mb-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this staff member?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">No actions</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-center">
        {{ $staff->links() }}
    </div>
@endif
</div>
@endsection