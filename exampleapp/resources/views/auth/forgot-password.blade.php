@extends('layouts.auth')

@section('content')
<div class="min-h-screen bg-white flex flex-col">
    {{-- Header: brand top left --}}
    <header class="p-6">
        <a href="{{ route('login') }}" class="text-lg font-semibold text-blue-600 hover:text-blue-700">ACADCLEAR</a>
    </header>

    {{-- Main: illustration + form side by side --}}
    <main class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12 max-w-2xl w-full">
            {{-- Left: illustration --}}
            <div class="flex-shrink-0 w-48 h-48 md:w-56 md:h-56">
                <div class="relative w-full h-full">
                    <svg viewBox="0 0 200 200" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                        {{-- Background gears (light grey) --}}
                        <circle cx="50" cy="45" r="18" stroke="#e2e8f0" stroke-width="2" fill="white"/>
                        <circle cx="150" cy="120" r="22" stroke="#e2e8f0" stroke-width="2" fill="white"/>
                        {{-- Envelope --}}
                        <g transform="translate(125, 35)">
                            <rect width="40" height="28" rx="2" fill="#3b82f6" opacity="0.9"/>
                            <path d="M2 6 L20 18 L38 6" stroke="white" stroke-width="2" fill="none"/>
                        </g>
                        {{-- Browser/document window --}}
                        <rect x="30" y="75" width="100" height="70" rx="4" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1.5"/>
                        <rect x="36" y="88" width="88" height="12" rx="2" fill="#fbbf24"/>
                        <rect x="36" y="105" width="60" height="8" rx="1" fill="#e2e8f0"/>
                        {{-- Padlock --}}
                        <g transform="translate(75, 95)">
                            <rect x="8" y="22" width="34" height="28" rx="3" fill="#3b82f6"/>
                            <path d="M18 22 L18 14 A10 10 0 0 1 32 14 L32 22" stroke="#3b82f6" stroke-width="4" fill="none"/>
                            <rect x="18" y="32" width="14" height="8" rx="1" fill="white"/>
                        </g>
                        {{-- Key --}}
                        <circle cx="155" cy="155" r="12" fill="#3b82f6"/>
                        <rect x="155" y="155" width="20" height="6" fill="#3b82f6"/>
                        <circle cx="172" cy="158" r="5" fill="white"/>
                    </svg>
                </div>
            </div>

            {{-- Right: form --}}
            <div class="flex-1 w-full max-w-[320px]">
                <h1 class="text-2xl font-bold text-slate-800">Forgot Password</h1>
                <p class="mt-1 text-sm text-slate-500">Enter your email and we'll send you a link to reset your password.</p>

                <x-auth-session-status class="mt-4 rounded-lg bg-emerald-50 text-emerald-700 px-3 py-2 text-sm" :status="session('status')" />
                <x-auth-validation-errors class="mt-4 rounded-lg bg-red-50 text-red-600 px-3 py-2 text-sm" :errors="$errors" />

                <form method="POST" action="{{ route('password.email') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <x-text-input
                                id="email"
                                class="block w-full rounded-lg border border-slate-300 bg-white text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 pl-10 py-2.5 text-sm transition"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                placeholder="Email"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-red-600 text-sm" />
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2.5 px-4 rounded-lg text-sm font-semibold text-white bg-blue-500 hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition"
                    >
                        Submit
                    </button>
                </form>

                <a href="{{ route('login') }}" class="inline-block mt-4 text-sm font-medium text-slate-700 hover:text-slate-900 transition">
                    &larr; Back to Login
                </a>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="p-6 text-left">
        <p class="text-xs text-slate-400">Created with ❤️ by AcadClear</p>
    </footer>
</div>
@endsection
