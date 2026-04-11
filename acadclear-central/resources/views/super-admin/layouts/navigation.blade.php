<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    @php
        $pendingPlanRequests = \App\Models\PlanRequest::query()
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $notificationCount = \App\Models\PlanRequest::query()
            ->where('status', 'pending')
            ->count();

        $recentMails = \App\Models\PlanRequest::query()
            ->whereNotNull('email')
            ->latest()
            ->take(5)
            ->get();

        $mailCount = \App\Models\PlanRequest::query()
            ->whereNotNull('email')
            ->where('created_at', '>=', now()->subDay())
            ->count();
    @endphp

    <ul class="navbar-nav ml-auto">
        <li class="nav-item no-arrow mx-1 d-flex align-items-center">
            <button class="theme-toggle-btn" type="button" id="themeToggleBtn" title="Toggle dark mode" aria-label="Toggle dark mode">
                <i class="fas fa-moon" id="themeToggleIcon"></i>
            </button>
        </li>

        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                @if($notificationCount > 0)
                    <span class="badge badge-danger badge-counter">{{ $notificationCount > 99 ? '99+' : $notificationCount }}</span>
                @endif
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">Notifications</h6>
                @forelse($pendingPlanRequests as $request)
                    <a class="dropdown-item d-flex align-items-center" href="{{ route('super-admin.plan-requests.index') }}">
                        <div class="mr-3">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">{{ $request->created_at?->diffForHumans() ?? 'Just now' }}</div>
                            <span class="font-weight-bold">Pending request: {{ \Illuminate\Support\Str::limit($request->institution_name ?? $request->tenant_name ?? 'New Plan Request', 45) }}</span>
                        </div>
                    </a>
                @empty
                    <span class="dropdown-item text-center small text-gray-500">No new notifications</span>
                @endforelse
                <a class="dropdown-item text-center small text-gray-500" href="{{ route('super-admin.plan-requests.index') }}">View all notifications</a>
            </div>
        </li>

        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                @if($mailCount > 0)
                    <span class="badge badge-danger badge-counter">{{ $mailCount > 99 ? '99+' : $mailCount }}</span>
                @endif
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">Mail</h6>
                @forelse($recentMails as $mail)
                    <a class="dropdown-item d-flex align-items-center" href="{{ route('super-admin.plan-requests.index') }}">
                        <div class="font-weight-bold">
                            <div class="text-truncate">{{ \Illuminate\Support\Str::limit($mail->institution_name ?? $mail->tenant_name ?? 'Plan Request', 35) }}</div>
                            <div class="small text-gray-500">{{ $mail->email }} • {{ $mail->created_at?->diffForHumans() ?? 'Just now' }}</div>
                        </div>
                    </a>
                @empty
                    <span class="dropdown-item text-center small text-gray-500">No mail items found</span>
                @endforelse
                <a class="dropdown-item text-center small text-gray-500" href="{{ route('super-admin.plan-requests.index') }}">View all mail</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>
                <i class="fas fa-user-circle fa-2x text-gray-600"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>