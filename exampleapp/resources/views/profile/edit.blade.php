@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success">Profile updated successfully.</div>
    @endif
    @if (session('status') === 'password-updated')
        <div class="alert alert-success">Password updated successfully.</div>
    @endif
    @if (session('tenant_logo_status') === 'tenant-logo-updated')
        <div class="alert alert-success">Tenant sidebar logo updated successfully.</div>
    @endif
    @if (session('tenant_theme_status') === 'tenant-theme-updated')
        <div class="alert alert-success">Tenant color scheme updated successfully.</div>
    @endif

    <div class="row">
        <div class="col-lg-6 mb-4 d-flex flex-column">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Overview</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            @if($user->profile_photo_url)
                                <img src="{{ $user->profile_photo_url }}" alt="Profile Photo" class="rounded-circle" style="width:64px; height:64px; object-fit:cover;">
                            @else
                                <i class="fas fa-user-circle fa-3x text-gray-300"></i>
                            @endif
                        </div>
                        <div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $user->email }}</div>
                            <div class="mt-1">
                                <span class="badge badge-info text-uppercase">{{ $user->role ?? 'user' }}</span>
                            </div>
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-5 text-gray-600">Joined</dt>
                        <dd class="col-7 text-gray-800 mb-2">{{ optional($user->created_at)->format('M d, Y') }}</dd>

                        <dt class="col-5 text-gray-600">Last update</dt>
                        <dd class="col-7 text-gray-800 mb-0">{{ optional($user->updated_at)->diffForHumans() }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Profile</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        <div class="form-group">
                            <label for="profile_photo">Profile Picture</label>
                            <input
                                id="profile_photo"
                                name="profile_photo"
                                type="file"
                                class="form-control-file @error('profile_photo') is-invalid @enderror"
                                accept="image/png,image/jpeg,image/webp"
                            />
                            <small class="form-text text-muted">Allowed: JPG, PNG, WEBP (max 2MB)</small>
                            @error('profile_photo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required
                                autocomplete="name"
                            />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                required
                                autocomplete="username"
                            />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4 d-flex flex-column">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                class="form-control {{ $errors->updatePassword->has('current_password') ? 'is-invalid' : '' }}"
                                autocomplete="current-password"
                            />
                            @if ($errors->updatePassword->has('current_password'))
                                <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control {{ $errors->updatePassword->has('password') ? 'is-invalid' : '' }}"
                                autocomplete="new-password"
                            />
                            @if ($errors->updatePassword->has('password'))
                                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="form-control {{ $errors->updatePassword->has('password_confirmation') ? 'is-invalid' : '' }}"
                                autocomplete="new-password"
                            />
                            @if ($errors->updatePassword->has('password_confirmation'))
                                <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key mr-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($user->role === 'school_admin')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Tenant Branding</h6>
                    </div>
                    <div class="card-body">
                        <form id="brandingForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('patch')

                            <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
                            <input type="hidden" name="email" value="{{ old('email', $user->email) }}">

                            <div class="form-group">
                                <label for="tenant_logo">Sidebar Logo</label>
                                <input
                                    id="tenant_logo"
                                    name="tenant_logo"
                                    type="file"
                                    class="form-control-file @error('tenant_logo') is-invalid @enderror"
                                    accept="image/png,image/jpeg,image/webp,image/gif"
                                />
                                <small class="form-text text-muted">Allowed: JPG, PNG, WEBP, GIF (max 2MB)</small>
                                @error('tenant_logo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(!empty($tenantLocalLogoUrl))
                                <div class="mb-3 d-inline-block">
                                    <img src="{{ $tenantLocalLogoUrl }}" alt="Tenant logo" class="rounded-circle border" style="width: 64px; height: 64px; object-fit: cover; background-color: #ffffff; padding: 4px;">
                                </div>
                            @endif



                        @php
                            $savedPrimary = $tenantBranding['custom_primary'] ?? '#122C4F';
                            $savedAccent  = $tenantBranding['custom_accent']  ?? '#5B88B2';
                        @endphp

                        <!-- ====================================================
                             60-30-10 COLOR SCHEME
                             60% = sidebar background  (primary)
                             30% = buttons & icons     (accent)
                             10% = black (fixed)
                        ==================================================== -->
                        <div class="form-group">
                            <label class="font-weight-bold">Color Scheme <span class="badge badge-info">60-30-10 Rule</span></label>
                            <p class="text-muted small mb-3">
                                Choose two colors for your school's theme. The <strong>third color is fixed black</strong> for text and fine details.
                            </p>

                            <div class="row">
                                <!-- 60% Primary - Sidebar -->
                                <div class="col-md-6 mb-3">
                                    <label for="tenant_primary_color" class="d-flex align-items-center">
                                        <span class="badge badge-secondary mr-2">60%</span>
                                        Sidebar Background Color
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input
                                            type="color"
                                            id="tenant_primary_color"
                                            name="tenant_primary_color"
                                            value="{{ old('tenant_primary_color', $savedPrimary) }}"
                                            class="form-control p-1 mr-2 @error('tenant_primary_color') is-invalid @enderror"
                                            style="width:56px; height:40px; cursor:pointer;"
                                            title="Pick sidebar background color (60%)"
                                        />
                                        <span id="primary_hex_label" class="small text-muted font-weight-bold"
                                              style="font-family:monospace;">{{ old('tenant_primary_color', $savedPrimary) }}</span>
                                    </div>
                                    <small class="form-text text-muted">Fills <strong>60%</strong> of the UI — the sidebar.</small>
                                    @error('tenant_primary_color')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 30% Accent - Buttons & Icons -->
                                <div class="col-md-6 mb-3">
                                    <label for="tenant_accent_color" class="d-flex align-items-center">
                                        <span class="badge badge-secondary mr-2">30%</span>
                                        Buttons &amp; Icons Color
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input
                                            type="color"
                                            id="tenant_accent_color"
                                            name="tenant_accent_color"
                                            value="{{ old('tenant_accent_color', $savedAccent) }}"
                                            class="form-control p-1 mr-2 @error('tenant_accent_color') is-invalid @enderror"
                                            style="width:56px; height:40px; cursor:pointer;"
                                            title="Pick button and icon color (30%)"
                                        />
                                        <span id="accent_hex_label" class="small text-muted font-weight-bold"
                                              style="font-family:monospace;">{{ old('tenant_accent_color', $savedAccent) }}</span>
                                    </div>
                                    <small class="form-text text-muted">Fills <strong>30%</strong> of the UI — buttons, icons, highlights.</small>
                                    @error('tenant_accent_color')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 10% Fixed Black -->
                                <div class="col-md-12 mb-3">
                                    <label class="d-flex align-items-center">
                                        <span class="badge badge-dark mr-2">10%</span>
                                        Text &amp; Detail Color
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <div style="width:56px;height:40px;background:#000000;border-radius:4px;border:1px solid #dee2e6;" class="mr-2"></div>
                                        <span class="small text-muted font-weight-bold" style="font-family:monospace;">#000000</span>
                                        <span class="ml-2 badge badge-secondary">Fixed</span>
                                    </div>
                                    <small class="form-text text-muted">Always <strong>black</strong> for readability. Cannot be changed.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Live Preview -->
                        <div class="mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Live Preview</label>
                            <div id="color_preview_box"
                                 class="rounded border p-0 overflow-hidden"
                                 style="height:90px; display:flex;">
                                <!-- Sidebar strip (60%) -->
                                <div id="preview_sidebar"
                                     style="width:60%; background:{{ $savedPrimary }}; display:flex; align-items:center; justify-content:center;">
                                    <span style="color:#fff; font-size:0.7rem; font-weight:600; letter-spacing:0.05em;">SIDEBAR (60%)</span>
                                </div>
                                <!-- Accent strip (30%) -->
                                <div id="preview_accent"
                                     style="width:30%; background:{{ $savedAccent }}; display:flex; align-items:center; justify-content:center;">
                                    <span style="color:#fff; font-size:0.7rem; font-weight:600; letter-spacing:0.05em;">BTN (30%)</span>
                                </div>
                                <!-- Black strip (10%) -->
                                <div style="width:10%; background:#000000; display:flex; align-items:center; justify-content:center;">
                                    <span style="color:#fff; font-size:0.7rem; font-weight:600; writing-mode:vertical-lr;">10%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Save button triggers confirmation modal -->
                        <button type="button" class="btn btn-primary" id="brandingConfirmBtn">
                            <i class="fas fa-palette mr-1"></i> Save Branding
                        </button>

                        <!-- Confirmation Modal -->
                        <div class="modal fade" id="colorConfirmModal" tabindex="-1" role="dialog"
                             aria-labelledby="colorConfirmModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="colorConfirmModalLabel">
                                            <i class="fas fa-palette mr-1"></i> Confirm Color Changes
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to apply this color scheme to your school's app?</p>
                                        <div class="rounded border overflow-hidden mb-3" style="height:64px; display:flex;">
                                            <div id="modal_preview_sidebar"
                                                 style="width:60%; display:flex; align-items:center; justify-content:center;">
                                                <span style="color:#fff; font-size:0.72rem; font-weight:600;">SIDEBAR (60%)</span>
                                            </div>
                                            <div id="modal_preview_accent"
                                                 style="width:30%; display:flex; align-items:center; justify-content:center;">
                                                <span style="color:#fff; font-size:0.72rem; font-weight:600;">BTN (30%)</span>
                                            </div>
                                            <div style="width:10%; background:#000000; display:flex; align-items:center; justify-content:center;">
                                                <span style="color:#fff; font-size:0.72rem; font-weight:600;">10%</span>
                                            </div>
                                        </div>
                                        <p class="small text-muted">
                                            <strong>Sidebar:</strong>
                                            <span id="modal_primary_hex" style="font-family:monospace;"></span>
                                            &nbsp;|&nbsp;
                                            <strong>Buttons/Icons:</strong>
                                            <span id="modal_accent_hex" style="font-family:monospace;"></span>
                                            &nbsp;|&nbsp;
                                            <strong>Text:</strong>
                                            <span style="font-family:monospace;">#000000</span>
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                            <i class="fas fa-arrow-left mr-1"></i> Go Back
                                        </button>
                                        <button type="button" class="btn btn-primary" id="confirmSaveBrandingBtn">
                                            <i class="fas fa-check mr-1"></i> Yes, Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                        (function () {
                            var primaryInput = document.getElementById('tenant_primary_color');
                            var accentInput  = document.getElementById('tenant_accent_color');
                            var primaryLabel = document.getElementById('primary_hex_label');
                            var accentLabel  = document.getElementById('accent_hex_label');
                            var previewSidebar = document.getElementById('preview_sidebar');
                            var previewAccent  = document.getElementById('preview_accent');
                            var modalSidebar   = document.getElementById('modal_preview_sidebar');
                            var modalAccent    = document.getElementById('modal_preview_accent');
                            var modalPrimaryHex = document.getElementById('modal_primary_hex');
                            var modalAccentHex  = document.getElementById('modal_accent_hex');

                            function updatePreview() {
                                var p = primaryInput.value.toUpperCase();
                                var a = accentInput.value.toUpperCase();
                                primaryLabel.textContent = p;
                                accentLabel.textContent  = a;
                                previewSidebar.style.background = p;
                                previewAccent.style.background  = a;
                            }

                            primaryInput.addEventListener('input', updatePreview);
                            accentInput.addEventListener('input', updatePreview);

                            // Open confirmation modal
                            document.getElementById('brandingConfirmBtn').addEventListener('click', function () {
                                var p = primaryInput.value.toUpperCase();
                                var a = accentInput.value.toUpperCase();
                                modalSidebar.style.background = p;
                                modalAccent.style.background  = a;
                                modalPrimaryHex.textContent = p;
                                modalAccentHex.textContent  = a;
                                $('#colorConfirmModal').modal('show');
                            });

                            // Confirmed — actually submit
                            document.getElementById('confirmSaveBrandingBtn').addEventListener('click', function () {
                                $('#colorConfirmModal').modal('hide');
                                document.getElementById('brandingForm').submit();
                            });
                        })();
                        </script>

                        </form>{{-- /brandingForm --}}
                    </div>
            </div>
        </div>
    </div>
    @endif
@endsection
