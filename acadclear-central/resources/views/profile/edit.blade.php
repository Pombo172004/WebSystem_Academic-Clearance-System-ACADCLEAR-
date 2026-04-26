@extends('super-admin.layouts.app')

@section('content')
@push('styles')
<style>
    .profile-page .card {
        border-radius: 12px;
    }

    .profile-page .section-title {
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .profile-page .card-header {
        border-bottom: 1px solid rgba(120, 150, 190, 0.22);
    }

    .profile-page .text-subtle {
        color: #7b90ab;
    }

    .dark-mode .profile-page .text-subtle {
        color: #b8c9de;
    }

    .profile-page .danger-zone {
        border: 1px solid rgba(224, 93, 93, 0.35);
    }
</style>
@endpush

<div class="profile-page">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Profile Settings</h1>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Profile information updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Password updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            A verification link has been sent to your email address.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary section-title">Profile Information</h6>
        </div>
        <div class="card-body">
            <p class="text-subtle mb-4">Update your account's profile information and email address.</p>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $user->name) }}"
                        required
                        autofocus
                        autocomplete="name"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}"
                        required
                        autocomplete="username"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap" role="alert" style="gap: 0.75rem;">
                        <span>Your email address is unverified.</span>
                        <form method="POST" action="{{ route('verification.send') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark">Resend Verification Email</button>
                        </form>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary section-title">Update Password</h6>
        </div>
        <div class="card-body">
            <p class="text-subtle mb-4">Ensure your account uses a secure password.</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                        autocomplete="current-password"
                    >
                    @error('current_password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password"
                    >
                    @error('password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password"
                    >
                    @error('password_confirmation', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4 danger-zone">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger section-title">Delete Account</h6>
        </div>
        <div class="card-body">
            <p class="text-subtle mb-4">
                Once your account is deleted, all of its resources and data will be permanently removed.
                Please confirm carefully.
            </p>

            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                Delete Account
            </button>

            @if ($errors->userDeletion->has('password'))
                <div class="text-danger small mt-3">{{ $errors->userDeletion->first('password') }}</div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-3">
                            This action cannot be undone. Enter your password to confirm account deletion.
                        </p>

                        <label for="delete_password" class="form-label">Password</label>
                        <input
                            id="delete_password"
                            name="password"
                            type="password"
                            class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                            required
                        >
                        @if ($errors->userDeletion->has('password'))
                            <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    @if ($errors->userDeletion->isNotEmpty())
    document.addEventListener('DOMContentLoaded', function () {
        var modalElement = document.getElementById('deleteAccountModal');
        if (modalElement && window.bootstrap) {
            var deleteModal = new bootstrap.Modal(modalElement);
            deleteModal.show();
        }
    });
    @endif
</script>
@endpush
@endsection
