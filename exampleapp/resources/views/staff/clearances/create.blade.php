@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
	<div class="col-12 col-lg-8">
		<div class="d-flex align-items-start">
			<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 48px; height: 48px; flex: 0 0 48px;">
				<i class="fas fa-clipboard-list"></i>
			</div>
			<div>
				<h1 class="h3 mb-1 text-gray-800">Create Student Clearance</h1>
				<p class="mb-0 text-muted">Select the student, choose the offices or instructors, then submit the checklist for approval.</p>
			</div>
		</div>
	</div>
	<div class="col-12 col-lg-4 text-lg-right mt-3 mt-lg-0">
		<a href="{{ route('staff.clearances.index') }}" class="btn btn-outline-secondary">
			<i class="fas fa-arrow-left mr-1"></i> Back to Clearances
		</a>
	</div>
</div>

@if ($errors->any())
	<div class="alert alert-danger border-left-danger shadow-sm">
		<div class="font-weight-bold mb-1">Please fix the following:</div>
		<ul class="mb-0 pl-3">
			@foreach ($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
@endif

<div class="card shadow-sm mb-4">
	<div class="card-header py-3 d-flex align-items-center justify-content-between">
		<h6 class="m-0 font-weight-bold text-primary">Clearance Details</h6>
		<span class="badge badge-primary">Staff Workflow</span>
	</div>
	<div class="card-body">
		<form method="POST" action="{{ route('staff.clearances.store') }}">
			@csrf

			<div class="row">
				<div class="col-lg-6 mb-4">
					<label for="student_id" class="d-block font-weight-bold text-gray-700">Student <span class="text-danger">*</span></label>
					<select name="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
						<option value="">Select student</option>
						@foreach ($students as $student)
							<option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
								{{ $student->name }} ({{ $student->email }})
							</option>
						@endforeach
					</select>
					<small class="form-text text-muted">Choose the student who needs clearance processing.</small>
					@error('student_id')
						<div class="invalid-feedback d-block">{{ $message }}</div>
					@enderror
				</div>

				<div class="col-lg-6 mb-4">
					<label for="clearance_title" class="d-block font-weight-bold text-gray-700">Clearance Title <span class="text-danger">*</span></label>
					<input
						type="text"
						name="clearance_title"
						id="clearance_title"
						class="form-control @error('clearance_title') is-invalid @enderror"
						value="{{ old('clearance_title', 'Student Clearance') }}"
						maxlength="255"
						required
					>
					<small class="form-text text-muted">Use a title that describes the clearance request clearly.</small>
					@error('clearance_title')
						<div class="invalid-feedback d-block">{{ $message }}</div>
					@enderror
				</div>
			</div>

			<div class="mb-4">
				<div class="d-flex align-items-center justify-content-between mb-2">
					<label class="font-weight-bold text-gray-700 mb-0">Checklist Items (Office or Instructor) <span class="text-danger">*</span></label>
					<span class="badge badge-light border">Pick one or more</span>
				</div>
				@php
					$defaultItems = [
						'librarian|Library Clearance|Head Librarian|Library Office',
						'registrar|Registrar Clearance|Registrar Officer|Registrar Office',
						'cashier|Financial/Cashier Clearance|Cashier|Accounting Office',
						'guidance_counselor|Guidance Clearance|Guidance Counselor|Guidance Office',
						'student_affairs_officer|Student Affairs Clearance|Student Affairs Officer|OSA Office',
						'department_chair|Chairperson Approval|Department Chair|Department Office',
						'thesis_adviser|Thesis Adviser Clearance|Thesis Adviser|Faculty Room',
						'research_coordinator|Research Clearance|Research Coordinator|Research Office',
					];

					$selected = old('checklist_items', []);
				@endphp

				<div class="row">
					@foreach ($defaultItems as $value)
						@php
							$parts = explode('|', $value);
							$itemName = $parts[1] ?? '';
							$contact = $parts[2] ?? '';
							$location = $parts[3] ?? '';
						@endphp

						<div class="col-md-6 mb-3">
							<div class="custom-control custom-checkbox border rounded p-3 h-100 bg-white {{ in_array($value, $selected, true) ? 'border-primary' : '' }}">
								<input
									class="custom-control-input"
									type="checkbox"
									name="checklist_items[]"
									id="item_{{ $loop->index }}"
									value="{{ $value }}"
									{{ in_array($value, $selected, true) ? 'checked' : '' }}
								>
								<label class="custom-control-label w-100" for="item_{{ $loop->index }}">
									<div class="d-flex justify-content-between align-items-start pr-3">
										<div>
											<div class="font-weight-bold text-gray-800">{{ $itemName }}</div>
											<div class="small text-muted">{{ $contact }}</div>
											<div class="small text-muted">{{ $location }}</div>
										</div>
										<span class="badge badge-light border">Checklist</span>
									</div>
								</label>
							</div>
						</div>
					@endforeach
				</div>

				@error('checklist_items')
					<div class="text-danger small mt-1">{{ $message }}</div>
				@enderror
			</div>

			<div class="card border-left-info mb-4">
				<div class="card-body">
					<div class="d-flex align-items-center mb-3">
						<div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 36px; height: 36px; flex: 0 0 36px;">
							<i class="fas fa-plus"></i>
						</div>
						<div>
							<h6 class="mb-0 text-gray-800">Add Custom Checklist Item</h6>
							<small class="text-muted">Optional: use this for offices or instructors not listed above.</small>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 mb-3">
							<label for="custom_item_name" class="font-weight-bold text-gray-700">Item Name</label>
							<input
								type="text"
								name="custom_item_name"
								id="custom_item_name"
								class="form-control @error('custom_item_name') is-invalid @enderror"
								value="{{ old('custom_item_name') }}"
								maxlength="255"
								placeholder="e.g., Clinic"
							>
							@error('custom_item_name')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-4 mb-3">
							<label for="custom_item_contact" class="font-weight-bold text-gray-700">Contact Person</label>
							<input
								type="text"
								name="custom_item_contact"
								id="custom_item_contact"
								class="form-control @error('custom_item_contact') is-invalid @enderror"
								value="{{ old('custom_item_contact') }}"
								maxlength="255"
								placeholder="e.g., School Nurse"
							>
							@error('custom_item_contact')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-4 mb-3">
							<label for="custom_item_office_role" class="font-weight-bold text-gray-700">Office Role</label>
							<select
								name="custom_item_office_role"
								id="custom_item_office_role"
								class="form-control @error('custom_item_office_role') is-invalid @enderror"
							>
								<option value="">Auto-detect from item name</option>
								@foreach($officeRoles as $roleKey => $roleLabel)
									<option value="{{ $roleKey }}" {{ old('custom_item_office_role') === $roleKey ? 'selected' : '' }}>
										{{ $roleLabel }}
									</option>
								@endforeach
							</select>
							@error('custom_item_office_role')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-4 mb-3">
							<label for="custom_item_location" class="font-weight-bold text-gray-700">Location</label>
							<input
								type="text"
								name="custom_item_location"
								id="custom_item_location"
								class="form-control @error('custom_item_location') is-invalid @enderror"
								value="{{ old('custom_item_location') }}"
								maxlength="255"
								placeholder="e.g., Clinic Room"
							>
							@error('custom_item_location')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>
			</div>

			<div class="mb-4">
				<label for="remarks" class="font-weight-bold text-gray-700">Remarks <span class="text-muted font-weight-normal">(optional)</span></label>
				<textarea
					name="remarks"
					id="remarks"
					rows="4"
					maxlength="500"
					class="form-control @error('remarks') is-invalid @enderror"
					placeholder="Add notes, instructions, or special conditions for this clearance"
				>{{ old('remarks') }}</textarea>
				@error('remarks')
					<div class="invalid-feedback d-block">{{ $message }}</div>
				@enderror
			</div>

			<div class="d-flex justify-content-between align-items-center border-top pt-3">
				<div class="text-muted small">
					<i class="fas fa-info-circle mr-1"></i>
					Selected checklist items will appear on the student clearance sheet.
				</div>
				<div>
					<a href="{{ route('staff.clearances.index') }}" class="btn btn-light border mr-2">Cancel</a>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-save mr-1"></i> Save Clearance
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
@endsection
