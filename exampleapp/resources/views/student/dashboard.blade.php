@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Clearance Progress</h1>
    <a href="{{ route('student.clearances.index') }}" class="btn btn-primary">
        <i class="fas fa-list"></i> View Details
    </a>
</div>

<!-- Progress Overview -->
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Overall Progress</h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Clearance Progress</span>
                                <span class="font-weight-bold">{{ $stats['approved'] }}/{{ $stats['total'] }} Departments ({{ $stats['progress'] }}%)</span>
                            </div>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: {{ $stats['progress'] }}%;" 
                                     aria-valuenow="{{ $stats['progress'] }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $stats['progress'] }}%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="mb-0">{{ $stats['approved'] }}/{{ $stats['total'] }}</h3>
                            <small class="text-muted">Departments Cleared</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Departments -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Departments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved -->
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
        </div>
    </div>

    <!-- Pending -->
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
        </div>
    </div>

    <!-- Rejected -->
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
        </div>
    </div>
</div>

<!-- Recent Clearances -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Updates</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clearances->take(5) as $clearance)
                            <tr>
                                <td>{{ $clearance->department->name }}</td>
                                <td>
                                    @php
                                        $badgeClass = [
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'pending' => 'warning'
                                        ][$clearance->status];
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">
                                        {{ ucfirst($clearance->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($clearance->remarks)
                                        <span title="{{ $clearance->remarks }}">
                                            {{ Str::limit($clearance->remarks, 30) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $clearance->updated_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No clearances found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Legend -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status Legend</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <span class="badge bg-success">Approved</span> - Clearance has been approved
                    </div>
                    <div class="col-md-4">
                        <span class="badge bg-warning">Pending</span> - Waiting for department review
                    </div>
                    <div class="col-md-4">
                        <span class="badge bg-danger">Rejected</span> - Check remarks for reason
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection