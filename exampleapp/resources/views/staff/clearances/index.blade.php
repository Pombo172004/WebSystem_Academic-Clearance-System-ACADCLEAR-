

@extends('layouts.app')

@section('content')
@php
    $canCreateClearance = auth()->user()->hasPermission('tenant.clearances.create');
    $canExportClearance = auth()->user()->hasPermission('tenant.clearances.export');
    $canUpdateClearance = auth()->user()->hasPermission('tenant.clearances.update');
@endphp
@push('styles')
<style>
    .clearances-page-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .clearances-page-action {
        min-width: 12.5rem;
        min-height: calc(1.5em + 0.75rem + 2px);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff !important;
        transition: background-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    .btn-clearance-create {
        border-color: var(--tenant-accent, #5B88B2) !important;
        color: var(--tenant-accent, #5B88B2) !important;
    }

    .btn-clearance-create:hover,
    .btn-clearance-create:focus {
        background-color: var(--tenant-primary-soft, #5B88B220) !important;
        border-color: var(--tenant-accent, #5B88B2) !important;
        color: var(--tenant-accent, #5B88B2) !important;
        box-shadow: 0 0 0 0.2rem var(--tenant-primary-soft, #5B88B220) !important;
    }

    .btn-clearance-export {
        border-color: var(--tenant-sidebar-bg, #122C4F) !important;
        color: var(--tenant-sidebar-bg, #122C4F) !important;
    }

    .btn-clearance-export:hover,
    .btn-clearance-export:focus {
        background-color: rgba(18, 44, 79, 0.12) !important;
        border-color: var(--tenant-sidebar-bg, #122C4F) !important;
        color: var(--tenant-sidebar-bg, #122C4F) !important;
        box-shadow: 0 0 0 0.2rem rgba(18, 44, 79, 0.12) !important;
    }

    .clearances-filter-form .form-control {
        min-height: calc(1.5em + 0.75rem + 2px);
        border-color: #d1d5db;
        background-color: #ffffff;
    }

    .clearances-filter-form .clearances-filter-action {
        min-height: calc(1.5em + 0.75rem + 2px);
    }
</style>
@endpush
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Clearance Requests</h1>
    <div class="clearances-page-actions">
        @if($canCreateClearance)
            <a href="{{ route('admin.clearances.create') }}" class="btn clearances-page-action btn-clearance-create">
                <i class="fas fa-plus"></i> Add Clearance
            </a>
        @endif
        @if($canExportClearance)
            <button type="button" class="btn clearances-page-action btn-clearance-export" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-download"></i> Export
            </button>
        @endif
    </div>
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

<!-- Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Clearances</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.clearances.index') }}" class="row g-3 align-items-end clearances-filter-form">
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by student name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 clearances-filter-action">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.clearances.index') }}" class="btn btn-secondary w-100 clearances-filter-action">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
@if($canUpdateClearance && (request('status') == 'pending' || !request('status')))
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Bulk Actions</h6>
    </div>
    <div class="card-body">
        <button class="btn btn-success" onclick="bulkApprove()">
            <i class="fas fa-check-double"></i> Approve Selected
        </button>
        <span class="text-muted ml-3">Select multiple clearances using checkboxes</span>
    </div>
</div>
@endif

<!-- Clearances Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Clearance Requests</h6>
        <span class="badge bg-info">{{ $clearances->total() }} total</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        @if($canUpdateClearance && (request('status') == 'pending' || !request('status')))
                        <th width="20">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endif
                        <th>Student</th>
                        <th>Department</th>
                        <th>Clearance</th>
                        <th>Office / Instructor</th>
                        <th>Location</th>
                        <th>Checklist</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Request Date</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clearances as $clearance)
                    <tr>
                        @if($canUpdateClearance && (request('status') == 'pending' || !request('status')))
                        <td>
                            <input type="checkbox" class="clearance-checkbox" value="{{ $clearance->id }}">
                        </td>
                        @endif
                        <td>
                            <strong>{{ $clearance->student?->name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $clearance->student?->email ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $clearance->department?->name ?? 'N/A' }}</td>
                        <td>{{ $clearance->clearance_title ?? 'Department Clearance' }}</td>
                        <td>{{ $clearance->office_or_instructor ?? 'Assigned staff' }}</td>
                        <td>{{ $clearance->approval_location ?? 'Department Office' }}</td>
                        <td>
                            @if($clearance->checklistItems->isNotEmpty())
                                <ul class="list-unstyled mb-0">
                                    @foreach($clearance->checklistItems as $item)
                                        <li class="mb-2 border rounded p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong>{{ $item->item_name }}</strong>
                                                    @if($item->contact_person)
                                                        <div><small class="text-muted">{{ $item->contact_person }}</small></div>
                                                    @endif
                                                    @if($item->location)
                                                        <div><small class="text-muted">{{ $item->location }}</small></div>
                                                    @endif
                                                </div>
                                                <span class="badge bg-{{ $item->status === 'approved' ? 'status-60' : 'status-30' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </div>
                                            @if($canUpdateClearance)
                                                <form action="{{ route('admin.clearances.checklist.update', ['clearance' => $clearance->id, 'item' => $item->id]) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $item->status === 'approved' ? 'pending' : 'approved' }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-{{ $item->status === 'approved' ? 'warning' : 'success' }}">
                                                        {{ $item->status === 'approved' ? 'Mark Pending' : 'Mark Approved' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No checklist</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusClass = [
                                    'approved' => 'status-60',
                                    'rejected' => 'status-10',
                                    'pending' => 'status-30'
                                ][$clearance->status];
                            @endphp
                            <span class="badge bg-{{ $statusClass }} fs-6">
                                {{ ucfirst($clearance->status) }}
                            </span>
                        </td>
                        <td>
                            @if($clearance->remarks)
                                <span class="text-danger" title="{{ $clearance->remarks }}">
                                    <i class="fas fa-comment"></i> View
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $clearance->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($canUpdateClearance && $clearance->status == 'pending')
                                <button class="btn btn-success btn-sm approve-btn" 
                                        data-id="{{ $clearance->id }}"
                                        onclick="approveClearance({{ $clearance->id }})">
                                    <i class="fas fa-check"></i>
                                </button>
                                
                                <button class="btn btn-danger btn-sm reject-btn" 
                                        data-id="{{ $clearance->id }}"
                                        type="button"
                                        onclick="rejectClearance({{ $clearance->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Reject Modal for each clearance -->
                    @if($canUpdateClearance)
                    <div class="modal fade" id="rejectModal{{ $clearance->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.clearances.reject', $clearance) }}" 
                                      method="POST" id="rejectForm{{ $clearance->id }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Clearance</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Student:</strong> {{ $clearance->student->name }}</p>
                                        <div class="mb-3">
                                            <label for="remarks{{ $clearance->id }}" class="form-label">Remarks <span class="text-danger">*</span></label>
                                            <textarea name="remarks" 
                                                      id="remarks{{ $clearance->id }}" 
                                                      class="form-control" 
                                                      rows="3" 
                                                      required 
                                                      placeholder="Please specify reason for rejection"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">
                                            Reject Clearance
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="{{ $canUpdateClearance && (request('status') == 'pending' || !request('status')) ? '12' : '11' }}" 
                            class="text-center">
                            No clearances found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $clearances->links() }}
        </div>
    </div>
</div>

<!-- Export Preview Modal -->
@if($canExportClearance)
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to export clearance data based on your current filters.</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><strong>Status:</strong> {{ request('status') ? ucfirst(request('status')) : 'All' }}</li>
                    <li class="list-group-item"><strong>Search:</strong> {{ request('search') ? request('search') : 'None' }}</li>
                    <li class="list-group-item"><strong>Total Records:</strong> {{ $clearances->total() }}</li>
                </ul>
                <p class="mb-0">Do you want to proceed with the export?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('admin.clearances.export', request()->all()) }}" class="btn btn-success" onclick="document.getElementById('exportModal').querySelector('.btn-close').click();">
                    <i class="fas fa-download"></i> Confirm Export
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    @if($canUpdateClearance)
    // Select all checkboxes
    document.getElementById('selectAll')?.addEventListener('change', function() {
        var checkboxes = document.getElementsByClassName('clearance-checkbox');
        for(var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });

    // Approve single clearance
    function approveClearance(id) {
        if(confirm('Approve this clearance?')) {
            fetch('{{ url('/admin/clearances') }}/' + id + '/approve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    // Bulk approve
    function bulkApprove() {
        var checkboxes = document.getElementsByClassName('clearance-checkbox');
        var selectedIds = [];
        
        for(var i = 0; i < checkboxes.length; i++) {
            if(checkboxes[i].checked) {
                selectedIds.push(checkboxes[i].value);
            }
        }
        
        if(selectedIds.length === 0) {
            alert('Please select at least one clearance');
            return;
        }
        
        if(confirm('Approve ' + selectedIds.length + ' selected clearances?')) {
            fetch('{{ route("admin.clearances.bulk-approve") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({clearance_ids: selectedIds})
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    // Reject single clearance
    function rejectClearance(id) {
        var remarks = prompt('Please provide the reason for rejection:');

        if (remarks === null) {
            return;
        }

        remarks = remarks.trim();

        if (!remarks) {
            alert('Remarks are required to reject a clearance.');
            return;
        }

        fetch('{{ url('/admin/clearances') }}/' + id + '/reject', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ remarks: remarks })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unable to reject clearance'));
            }
        })
        .catch(() => {
            alert('Error: Unable to reject clearance');
        });
    }
    @endif
</script>
@endpush

@endsection
