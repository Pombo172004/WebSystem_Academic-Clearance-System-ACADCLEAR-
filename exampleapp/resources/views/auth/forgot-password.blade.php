@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 md:p-6" style="background-color: #ffffff;">
    <div class="w-full max-w-5xl min-h-[600px] flex flex-col md:flex-row rounded-[28px] overflow-hidden shadow-2xl bg-white">
        {{-- Left panel: Branding --}}
        <div class="md:w-[45%] flex flex-col justify-between p-8 md:p-10 text-white" style="background-color: #122C4F !important;">
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

            {{-- Illustration --}}
            <div class="flex-1 flex items-center justify-center my-8 md:my-10">
                <div class="relative w-full max-w-[220px]">
                    <div class="aspect-square rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-inner">
                        <svg class="w-24 h-24 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-lg font-bold">@</div>
                    <div class="absolute -bottom-1 -left-1 w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-lg font-medium leading-snug text-white/95 max-w-[280px]">
                    No worries! We'll send you a link to reset your password right away.
                </p>
                <a href="{{ route('login') }}" class="inline-block mt-4 text-sm font-medium text-blue-100 hover:text-white transition">&larr; Back to Login</a>
            </div>
        </div>

        {{-- Right panel: Form (black) --}}
        <div class="md:w-[55%] flex flex-col p-8 md:p-10" style="background-color: #000000 !important;">
            {{-- Nav --}}
            <nav class="flex items-center justify-end gap-6 text-sm font-medium mb-8">
                <a href="{{ route('landing.index') }}" class="text-white/80 hover:text-white transition">Plans</a>
                <a href="#" class="text-white/80 hover:text-white transition">About Us</a>
                <a href="#" class="text-white/80 hover:text-white transition">Contact</a>
                <a href="{{ route('login') }}" class="text-blue-300 hover:text-blue-200 transition">Log in</a>
            </nav>

            <div class="flex-1 flex flex-col justify-center max-w-sm mx-auto w-full">
                <h2 class="text-2xl font-bold text-white text-center mb-2">Forgot Password?</h2>
                <p class="text-sm text-white/60 text-center mb-6">Enter your email and we'll send you a reset link.</p>

                <x-auth-session-status class="mb-4 rounded-lg bg-emerald-500/20 text-emerald-200 px-4 py-3 text-sm" :status="session('status')" />
                <x-auth-validation-errors class="mb-4 rounded-lg bg-red-500/20 text-red-200 px-4 py-3 text-sm" :errors="$errors" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                    @csrf
                    <div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color: #122C4F;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <x-text-input
                                id="email"
                                class="block w-full rounded-xl border-0 py-3 pl-10 pr-4 transition"
                                style="background-color: #FBF9E4 !important; color: #122C4F !important;"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                placeholder="Email"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 rounded-xl font-semibold text-white transition hover:opacity-90"
                        style="background-color: #122C4F !important;"
                    >
                        Send Reset Link
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-white/80">
                    Remembered your password?
                    <a href="{{ route('login') }}" class="font-medium text-blue-300 hover:text-blue-200 transition">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
