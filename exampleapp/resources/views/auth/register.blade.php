@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-200/80 p-4 md:p-6">
    <div class="w-full max-w-5xl min-h-[600px] flex flex-col md:flex-row rounded-[28px] overflow-hidden shadow-2xl bg-white">
        {{-- Left panel: Branding & marketing (vibrant blue) --}}
        <div class="md:w-[45%] bg-blue-500 flex flex-col justify-between p-8 md:p-10 text-white">
            <div>
                {{-- Logo + brand --}}
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-1.5">
                        <span class="w-9 h-9 rounded-lg bg-white/20 border-2 border-white/40 inline-block"></span>
                        <span class="w-9 h-9 rounded-lg bg-white/30 border-2 border-white/40 inline-block"></span>
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight">ACADCLEAR</span>
                        <span class="block text-xs font-medium text-blue-100 tracking-widest uppercase mt-0.5">Academic Clearance System</span>
                    </div>
                </div>
            </div>

            {{-- Illustration area (education / learning theme) --}}
            <div class="flex-1 flex items-center justify-center my-8 md:my-10">
                <div class="relative w-full max-w-[220px]">
                    <div class="aspect-square rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-inner">
                        <svg class="w-24 h-24 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-lg font-bold">@</div>
                    <div class="absolute -bottom-1 -left-1 w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-lg font-medium leading-snug text-white/95 max-w-[280px]">
                    Discover the new way to manage and organize your academic clearances.
                </p>
                <a href="#" class="inline-block mt-4 text-sm font-medium text-blue-100 hover:text-white transition">See more</a>
            </div>
        </div>

        {{-- Right panel: Form (dark navy) --}}
        <div class="md:w-[55%] bg-[#0f172a] flex flex-col p-8 md:p-10">
            {{-- Nav --}}
            <nav class="flex items-center justify-end gap-6 text-sm font-medium mb-8">
                <a href="#" class="text-white/80 hover:text-white transition">About Us</a>
                <a href="#" class="text-white/80 hover:text-white transition">Prices</a>
                <a href="#" class="text-white/80 hover:text-white transition">Contact</a>
                <a href="{{ route('login') }}" class="text-blue-300 hover:text-blue-200 transition">Log in</a>
            </nav>

            <div class="flex-1 flex flex-col justify-center max-w-sm mx-auto w-full">
                <h2 class="text-2xl font-bold text-white text-center mb-2">Student Registration</h2>
                <p class="text-sm text-blue-100 text-center mb-6">Create your student account and choose your college and department.</p>

                <x-auth-validation-errors class="mb-4 rounded-lg bg-red-500/20 text-red-200 px-4 py-3 text-sm" :errors="$errors" />

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-text-input
                            id="name"
                            class="block w-full rounded-xl border-0 bg-white text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-400 py-3 px-4 transition"
                            type="text"
                            name="name"
                            :value="old('name')"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <div>
                        <select
                            id="college_id"
                            name="college_id"
                            required
                            class="block w-full rounded-xl border-0 bg-white text-slate-800 focus:ring-2 focus:ring-blue-400 py-3 px-4 transition"
                        >
                            <option value="">Select college</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" @selected((string) old('college_id') === (string) $college->id)>
                                    {{ $college->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('college_id')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <div>
                        <select
                            id="department_id"
                            name="department_id"
                            required
                            class="block w-full rounded-xl border-0 bg-white text-slate-800 focus:ring-2 focus:ring-blue-400 py-3 px-4 transition"
                        >
                            <option value="">Select department</option>
                        </select>
                        <x-input-error :messages="$errors->get('department_id')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <div>
                        <x-text-input
                            id="email"
                            class="block w-full rounded-xl border-0 bg-white text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-400 py-3 px-4 transition"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autocomplete="username"
                            placeholder="Email"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <div>
                        <x-text-input
                            id="password"
                            class="block w-full rounded-xl border-0 bg-white text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-400 py-3 px-4 transition"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <div>
                        <x-text-input
                            id="password_confirmation"
                            class="block w-full rounded-xl border-0 bg-white text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-400 py-3 px-4 transition"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 rounded-xl font-semibold text-white bg-blue-500 hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-[#0f172a] transition"
                    >
                        {{ __('Register') }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-white/80">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-blue-300 hover:text-blue-200 transition">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>
@php
    $departmentsByCollege = $colleges->mapWithKeys(
        fn ($college) => [
            $college->id => $college->departments->map(
                fn ($department) => ['id' => $department->id, 'name' => $department->name]
            )->values(),
        ]
    );
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var collegeSelect = document.getElementById('college_id');
        var departmentSelect = document.getElementById('department_id');
        var departmentsByCollege = @json($departmentsByCollege);
        var oldDepartmentId = @json(old('department_id'));

        function renderDepartments(collegeId, selectedDepartmentId) {
            departmentSelect.innerHTML = '<option value="">Select department</option>';

            if (!collegeId || !departmentsByCollege[collegeId]) {
                departmentSelect.disabled = true;
                return;
            }

            departmentsByCollege[collegeId].forEach(function (department) {
                var option = document.createElement('option');
                option.value = department.id;
                option.textContent = department.name;

                if (String(selectedDepartmentId) === String(department.id)) {
                    option.selected = true;
                }

                departmentSelect.appendChild(option);
            });

            departmentSelect.disabled = false;
        }

        collegeSelect.addEventListener('change', function () {
            renderDepartments(this.value, null);
        });

        renderDepartments(collegeSelect.value, oldDepartmentId);
    });
</script>
@endsection
