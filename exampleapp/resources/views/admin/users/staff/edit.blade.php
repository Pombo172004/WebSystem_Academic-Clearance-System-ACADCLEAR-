

@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Staff</h1>
    <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Staff Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.staff.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="college_id" class="form-label">College <span class="text-danger">*</span></label>
                        <select class="form-control @error('college_id') is-invalid @enderror" 
                                id="college_id" 
                                name="college_id" 
                                required>
                            <option value="">Select College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" 
                                    {{ old('college_id', $user->college_id) == $college->id ? 'selected' : '' }}>
                                    {{ $college->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('college_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                        <select class="form-control @error('department_id') is-invalid @enderror" 
                                id="department_id" 
                                name="department_id" 
                                required>
                            <option value="">Select Department</option>
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="office_role" class="form-label">Office Role <span class="text-danger">*</span></label>
                        <select class="form-control @error('office_role') is-invalid @enderror"
                                id="office_role"
                                name="office_role"
                                required>
                            <option value="">Select Office Role</option>
                            @foreach($officeRoles as $roleKey => $roleLabel)
                                <option value="{{ $roleKey }}" {{ old('office_role', $user->office_role) === $roleKey ? 'selected' : '' }}>
                                    {{ $roleLabel }}
                                </option>
                            @endforeach
                        </select>
                        @error('office_role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Staff
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('college_id').addEventListener('change', function() {
        var collegeId = this.value;
        var departmentSelect = document.getElementById('department_id');
        var currentDepartment = {{ $user->department_id }};
        
        departmentSelect.innerHTML = '<option value="">Loading...</option>';
        
        fetch('/admin/get-departments/' + collegeId)
            .then(response => response.json())
            .then(data => {
                departmentSelect.innerHTML = '<option value="">Select Department</option>';
                data.forEach(department => {
                    var selected = (department.id == currentDepartment) ? 'selected' : '';
                    departmentSelect.innerHTML += `<option value="${department.id}" ${selected}>${department.name}</option>`;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
            });
    });

    // Trigger change event on page load to load departments
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('college_id').value) {
            var event = new Event('change');
            document.getElementById('college_id').dispatchEvent(event);
        }
    });
</script>
@endpush