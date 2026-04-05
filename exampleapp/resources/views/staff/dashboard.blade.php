@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Staff Dashboard</h1>
    <a href="{{ route('staff.clearances.index') }}" class="btn btn-primary">
        <i class="fas fa-list"></i> View All Clearances
    </a>
</div>

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Clearances</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('staff.clearances.index', ['status' => 'pending']) }}" class="small">
                    View Pending →
                </a>
            </div>
        </div>
    </div>

    <!-- Approved Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Approved</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['approved'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('staff.clearances.index', ['status' => 'approved']) }}" class="small">
                    View Approved →
                </a>
            </div>
        </div>
    </div>

    <!-- Rejected Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Rejected</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rejected'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('staff.clearances.index', ['status' => 'rejected']) }}" class="small">
                    View Rejected →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Clearances -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Clearance Requests</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Checklist Item</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentClearances as $clearance)
                            @php
                                $roleItem = $clearance->checklistItems->first();
                                $displayStatus = $roleItem->status ?? $clearance->status;
                                $badgeClass = [
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending' => 'warning'
                                ][$displayStatus] ?? 'secondary';
                            @endphp
                            <tr>
                                <td>{{ $clearance->student->name }}</td>
                                <td>
                                    {{ $roleItem->item_name ?? ($clearance->clearance_title ?? 'Assigned Item') }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $badgeClass }}">
                                        {{ ucfirst($displayStatus) }}
                                    </span>
                                </td>
                                <td>{{ $clearance->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('staff.clearances.index', ['search' => $clearance->student->name]) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No recent clearances</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Info -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Your Department Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Department:</strong> {{ auth()->user()->department->name ?? 'N/A' }}</p>
                        <p><strong>College:</strong> {{ auth()->user()->college->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Students in Department:</strong> 
                            {{ \App\Models\Clearance::where('department_id', auth()->user()->department_id)
                                ->distinct('student_id')->count('student_id') }}
                        </p>
                        <p><strong>Completion Rate:</strong> 
                            @php
                                $total = $stats['total'];
                                $approved = $stats['approved'];
                                $rate = $total > 0 ? round(($approved / $total) * 100, 2) : 0;
                            @endphp
                            {{ $rate }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection