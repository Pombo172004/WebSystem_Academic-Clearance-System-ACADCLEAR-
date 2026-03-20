

@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Clearance Status</h1>
</div>

<!-- Progress Overview -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Overall Progress</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Clearance Progress</span>
                        <span class="font-weight-bold">
                            {{ $stats['approved'] }}/{{ $stats['total'] }} Departments ({{ $stats['progress'] }}%)
                        </span>
                    </div>
                    <div class="progress" style="height: 25px;">
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

                <!-- Summary Stats -->
                <div class="row text-center mt-4">
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-success text-white">
                            <h3>{{ $stats['approved'] }}</h3>
                            <small>Approved</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-warning text-white">
                            <h3>{{ $stats['pending'] }}</h3>
                            <small>Pending</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-danger text-white">
                            <h3>{{ $stats['rejected'] }}</h3>
                            <small>Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Departments Clearance Status -->
<div class="row">
    @forelse($clearances as $clearance)
    <div class="col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">{{ $clearance->department->name }}</h6>
                @php
                    $statusClass = [
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning'
                    ][$clearance->status];
                    $statusIcon = [
                        'approved' => 'check-circle',
                        'rejected' => 'times-circle',
                        'pending' => 'clock'
                    ][$clearance->status];
                @endphp
                <span class="badge bg-{{ $statusClass }} fs-6">
                    <i class="fas fa-{{ $statusIcon }}"></i> {{ ucfirst($clearance->status) }}
                </span>
            </div>
            <div class="card-body">
                @if($clearance->status == 'rejected' && $clearance->remarks)
                    <div class="alert alert-danger mb-0">
                        <strong><i class="fas fa-exclamation-triangle"></i> Remarks:</strong><br>
                        {{ $clearance->remarks }}
                    </div>
                @elseif($clearance->status == 'approved')
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> This department has approved your clearance.
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-hourglass-half"></i> Pending review by the department.
                    </div>
                @endif
            </div>
            <div class="card-footer text-muted">
                <small>
                    <i class="fas fa-calendar"></i> Requested: {{ $clearance->created_at->format('M d, Y') }}
                    <br>
                    <i class="fas fa-clock"></i> Last updated: {{ $clearance->updated_at->diffForHumans() }}
                </small>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No clearances found. Please contact the admin.
        </div>
    </div>
    @endforelse
</div>
@endsection