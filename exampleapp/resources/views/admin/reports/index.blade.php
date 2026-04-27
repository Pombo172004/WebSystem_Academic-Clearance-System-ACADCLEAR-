@extends('layouts.app')

@section('content')
@php
    $canExportReports = auth()->user()->hasPermission('tenant.reports.export');
    $statusChartPrimary = $tenantPrimaryColor ?? '#122C4F';
    $statusChartAccent = $tenantAccentColor ?? '#5B88B2';
    $statusChartDetail = (function ($hex) {
        if (!is_string($hex) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            return '#0E1326';
        }

        $hex = ltrim($hex, '#');
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $darken = static fn ($channel) => max(0, min(255, (int) round($channel * 0.45)));

        return sprintf('#%02X%02X%02X', $darken($red), $darken($green), $darken($blue));
    })($statusChartPrimary);
    $statusChartColors = [$statusChartPrimary, $statusChartAccent, $statusChartDetail];
    $collegeChartColor = $statusChartPrimary;
    $exportPdfColor = $statusChartAccent;
    $exportCsvColor = $statusChartPrimary;
@endphp
@push('styles')
<style>
    .reports-filter-form .form-label {
        display: block;
        margin-bottom: 0.5rem;
    }

    .reports-filter-form .form-control {
        min-height: calc(1.5em + 0.75rem + 2px);
        border-color: #d1d5db;
        background-color: #ffffff;
    }

    .reports-filter-form .reports-filter-action {
        min-height: calc(1.5em + 0.75rem + 2px);
    }

    .btn-export-pdf,
    .btn-export-csv {
        background-color: #ffffff !important;
        transition: background-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    .btn-export-pdf {
        border-color: {{ $exportPdfColor }} !important;
        color: {{ $exportPdfColor }} !important;
    }

    .btn-export-pdf:hover,
    .btn-export-pdf:focus {
        background-color: {{ $exportPdfColor }}20 !important;
        border-color: {{ $exportPdfColor }} !important;
        color: {{ $exportPdfColor }} !important;
        box-shadow: 0 0 0 0.2rem {{ $exportPdfColor }}20 !important;
    }

    .btn-export-csv {
        border-color: {{ $exportCsvColor }} !important;
        color: {{ $exportCsvColor }} !important;
    }

    .btn-export-csv:hover,
    .btn-export-csv:focus {
        background-color: {{ $exportCsvColor }}20 !important;
        border-color: {{ $exportCsvColor }} !important;
        color: {{ $exportCsvColor }} !important;
        box-shadow: 0 0 0 0.2rem {{ $exportCsvColor }}20 !important;
    }
</style>
@endpush
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Reports & Analytics</h1>
    <div>
        @if($canExportReports)
            <button type="button" class="btn btn-export-pdf" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button type="button" class="btn btn-export-csv" data-bs-toggle="modal" data-bs-target="#exportCsvModal">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
        @endif
    </div>
</div>

<!-- Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Reports</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end reports-filter-form">
            <div class="col-lg-4 col-md-6">
                <label for="college_id" class="form-label">College</label>
                <select name="college_id" id="college_id" class="form-control">
                    <option value="">All Colleges</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}" {{ $selectedCollege == $college->id ? 'selected' : '' }}>
                            {{ $college->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" name="date_from" id="date_from" class="form-control" 
                       value="{{ $dateFrom }}">
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" name="date_to" id="date_to" class="form-control" 
                       value="{{ $dateTo }}">
            </div>
            <div class="col-lg-2 col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 reports-filter-action">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                            Total Clearances</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_clearances'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Served -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                            Students Served</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['students_served'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Rate -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                            Completion Rate</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completion_rate'] }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
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
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Status Distribution Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Daily Trend Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daily Trend (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="trendChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- College Distribution Chart (if no college selected) -->
@if(!$selectedCollege && !empty($chartData['college']['labels']))
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Clearances by College</h6>
            </div>
            <div class="card-body">
                <canvas id="collegeChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Department Performance Table -->
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Department Performance</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>College</th>
                                <th>Department</th>
                                <th>Total</th>
                                <th>Approved</th>
                                <th>Pending</th>
                                <th>Rejected</th>
                                <th>Completion Rate</th>
                                <th>Avg Response Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departmentPerformance as $dept)
                            <tr>
                                <td>{{ $dept['college'] }}</td>
                                <td>{{ $dept['name'] }}</td>
                                <td>{{ $dept['total'] }}</td>
                                <td class="text-success">{{ $dept['approved'] }}</td>
                                <td class="text-warning">{{ $dept['pending'] }}</td>
                                <td class="text-danger">{{ $dept['rejected'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 mr-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: {{ $dept['rate'] }}%"></div>
                                        </div>
                                        <span>{{ $dept['rate'] }}%</span>
                                    </div>
                                </td>
                                <td>{{ $dept['avg_response_time'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export PDF Modal -->
@if($canExportReports)
<div class="modal fade" id="exportPdfModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export PDF Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to export reports as a PDF based on your current filters.</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><strong>College:</strong> {{ request('college_id') ? ($colleges->where('id', request('college_id'))->first()->name ?? 'Selected College') : 'All Colleges' }}</li>
                    <li class="list-group-item"><strong>Date From:</strong> {{ request('date_from') ?: 'Beginning' }}</li>
                    <li class="list-group-item"><strong>Date To:</strong> {{ request('date_to') ?: 'Today' }}</li>
                </ul>
                <p class="mb-0">Do you want to proceed with the export?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('admin.reports.export.pdf', request()->all()) }}" class="btn btn-export-pdf" onclick="document.getElementById('exportPdfModal').querySelector('.btn-close').click();">
                    <i class="fas fa-file-pdf"></i> Confirm Export PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Export CSV Modal -->
<div class="modal fade" id="exportCsvModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export CSV Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to export reports as a CSV based on your current filters.</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><strong>College:</strong> {{ request('college_id') ? ($colleges->where('id', request('college_id'))->first()->name ?? 'Selected College') : 'All Colleges' }}</li>
                    <li class="list-group-item"><strong>Date From:</strong> {{ request('date_from') ?: 'Beginning' }}</li>
                    <li class="list-group-item"><strong>Date To:</strong> {{ request('date_to') ?: 'Today' }}</li>
                </ul>
                <p class="mb-0">Do you want to proceed with the export?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('admin.reports.export.csv', request()->all()) }}" class="btn btn-export-csv" onclick="document.getElementById('exportCsvModal').querySelector('.btn-close').click();">
                    <i class="fas fa-file-csv"></i> Confirm Export CSV
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const statusChartColors = {!! json_encode($statusChartColors) !!};
    const collegeChartColor = {!! json_encode($collegeChartColor) !!};

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['status']['labels']) !!},
            datasets: [{
                data: {!! json_encode($chartData['status']['data']) !!},
                backgroundColor: statusChartColors,
                hoverBackgroundColor: statusChartColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Daily Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['trend']['labels']) !!},
            datasets: [{
                label: 'Clearances',
                data: {!! json_encode($chartData['trend']['data']) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    @if(!$selectedCollege && !empty($chartData['college']['labels']))
    // College Distribution Chart
    const collegeCtx = document.getElementById('collegeChart').getContext('2d');
    new Chart(collegeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['college']['labels']) !!},
            datasets: [{
                label: 'Clearances',
                data: {!! json_encode($chartData['college']['data']) !!},
                backgroundColor: collegeChartColor,
                borderColor: collegeChartColor,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
@endsection
