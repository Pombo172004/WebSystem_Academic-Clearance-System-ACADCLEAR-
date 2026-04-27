
@extends('super-admin.layouts.app')

@section('content')
@php
    $defaultMailer = config('mail.default', 'log');
    $defaultTransport = config("mail.mailers.{$defaultMailer}.transport", $defaultMailer);
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New University</h1>
    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

@if(in_array($defaultTransport, ['log', 'array'], true))
    <div class="alert alert-warning" role="alert">
        Email delivery is currently set to <strong>{{ $defaultTransport }}</strong>. New university credentials will not reach a real inbox until the <code>MAIL_*</code> settings are configured.
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">University Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.tenants.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tenant-name" class="form-label">University Name *</label>
                    <input type="text" id="tenant-name" name="name" class="form-control" value="{{ old('name') }}" required autocomplete="organization">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tenant-slug" class="form-label">Slug *</label>
                    <input type="text" id="tenant-slug" name="slug" class="form-control" value="{{ old('slug') }}"
                           placeholder="bukidnon-state-u" required
                           pattern="^[a-z0-9][a-z0-9\-]*[a-z0-9]$"
                           title="Lowercase letters, numbers and hyphens only (3-64 chars)"
                           autocomplete="off">
                    <small class="text-muted">Used in URL: https://slug.localhost:8000 - Only lowercase letters, numbers, and hyphens</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tenant-domain" class="form-label">Domain *</label>
                    <input type="text" id="tenant-domain" name="domain" class="form-control" value="{{ old('domain') }}"
                           placeholder="bukidnon.acadclear.com" required autocomplete="off">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tenant-database" class="form-label">Database Name *</label>
                    <input type="text" id="tenant-database" name="database" class="form-control" value="{{ old('database') }}"
                           placeholder="acadclear_bukidnon" required autocomplete="off">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tenant-plan" class="form-label">Initial Plan *</label>
                    <select id="tenant-plan" name="plan_id" class="form-select" required>
                        <option value="">Select Plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} - ₱{{ number_format($plan->price, 2) }}/month
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tenant-logo" class="form-label">University Logo</label>
                    <input type="file" id="tenant-logo" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">PNG, JPG, GIF, or WebP. This will appear in the tenant sidebar.</small>
                </div>
            </div>

            <hr>
            <h6 class="m-0 font-weight-bold text-primary mb-3">University Admin Account</h6>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tenant-admin-email" class="form-label">University Admin Email *</label>
                    <input type="email" id="tenant-admin-email" name="admin_email" class="form-control" value="{{ old('admin_email') }}"
                           placeholder="admin@university.edu" required autocomplete="email">
                    <small>This account will be created in the university tenant system. If email delivery is configured, credentials and subscription details will be emailed automatically.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tenant-admin-password" class="form-label">University Admin Password *</label>
                    <input type="password" id="tenant-admin-password" name="admin_password" class="form-control"
                           placeholder="Minimum 8 characters" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create University
            </button>
        </form>
    </div>
</div>
@endsection
