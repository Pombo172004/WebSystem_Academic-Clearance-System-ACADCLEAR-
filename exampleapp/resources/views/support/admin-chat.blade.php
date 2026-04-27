@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Support Chat</h1>
</div>

{{-- TAB SWITCHER --}}
<ul class="nav nav-tabs mb-0" id="chatTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ request('tab') !== 'central' ? 'active' : '' }}"
           id="local-tab" data-toggle="tab" href="#local" role="tab">
            <i class="fas fa-users mr-1"></i> Student / Staff Inbox
            @php
                $totalLocalUnread = $threadUsers->sum('unread_count');
            @endphp
            @if($totalLocalUnread > 0)
                <span class="badge badge-danger ml-1">{{ $totalLocalUnread > 99 ? '99+' : $totalLocalUnread }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('tab') === 'central' ? 'active' : '' }}"
           id="central-tab" data-toggle="tab" href="#central" role="tab">
            <i class="fas fa-headset mr-1"></i> Central Support
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- ===== LOCAL INBOX TAB ===== --}}
    <div class="tab-pane fade {{ request('tab') !== 'central' ? 'show active' : '' }}"
         id="local" role="tabpanel">
        <div class="card shadow-sm" style="border-top-left-radius:0; border-top-right-radius:0;">
            <div class="card-body p-0">
                <div class="row no-gutters" style="min-height: 65vh;">

                    {{-- Thread list (left panel) --}}
                    <div class="col-md-4 border-right"
                         style="max-height: 65vh; overflow-y: auto; background:#f8fafc;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-muted text-uppercase small">
                                Conversations
                            </h6>
                        </div>

                        @forelse($threadUsers as $u)
                            @php
                                $isActive = optional($threadWith)->id === $u->id;
                            @endphp
                            <a href="{{ route('support.chat') }}?with={{ $u->id }}"
                               class="d-flex align-items-center p-3 border-bottom text-decoration-none
                                      {{ $isActive ? 'bg-primary text-white' : 'text-dark' }}"
                               style="{{ $isActive ? '' : 'background:#fff;' }}">
                                <div class="icon-circle mr-3 {{ $isActive ? 'bg-white' : 'bg-primary' }}"
                                     style="width:38px; height:38px; min-width:38px; border-radius:50%;
                                            display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-user {{ $isActive ? 'text-primary' : 'text-white' }}"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="font-weight-bold text-truncate" style="max-width:140px;">
                                        {{ $u->name }}
                                    </div>
                                    <small class="{{ $isActive ? 'text-white-50' : 'text-muted' }}">
                                        {{ ucfirst($u->role) }}
                                    </small>
                                </div>
                                @if($u->unread_count > 0)
                                    <span class="badge badge-danger ml-2">
                                        {{ $u->unread_count > 9 ? '9+' : $u->unread_count }}
                                    </span>
                                @endif
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted small">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No conversations yet.
                            </div>
                        @endforelse
                    </div>

                    {{-- Thread / Message panel (right panel) --}}
                    <div class="col-md-8 d-flex flex-column">

                        @if($threadWith)
                            <div class="p-3 border-bottom d-flex align-items-center">
                                <i class="fas fa-user-circle fa-lg text-primary mr-2"></i>
                                <div>
                                    <div class="font-weight-bold">{{ $threadWith->name }}</div>
                                    <small class="text-muted">{{ ucfirst($threadWith->role) }}</small>
                                </div>
                            </div>

                            <div id="localMessages" class="flex-grow-1 p-3"
                                 style="overflow-y:auto; background:#f8fafc; max-height:52vh;">
                                @forelse($threadMessages as $message)
                                    @php $mine = $message->sender_id === $currentUser->id; @endphp
                                    <div class="mb-3 {{ $mine ? 'text-right' : '' }}">
                                        <div class="small text-muted mb-1">
                                            {{ $mine ? 'You' : $message->sender->name }}
                                            <span class="ml-1" style="font-size:.75em;">
                                                {{ $message->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div class="d-inline-block px-3 py-2 rounded
                                            {{ $mine ? 'bg-primary text-white' : 'bg-white border' }}"
                                             style="max-width:85%;">
                                            {{ $message->message }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted text-center mt-4">
                                        <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                                        No messages yet in this conversation.
                                    </div>
                                @endforelse
                            </div>

                            <form id="localChatForm" class="d-flex p-3 border-top" autocomplete="off">
                                @csrf
                                <input id="localInput" name="message" type="text" class="form-control mr-2"
                                       maxlength="2000" placeholder="Reply to {{ $threadWith->name }}..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-1"></i> Send
                                </button>
                            </form>

                        @else
                            <div class="d-flex align-items-center justify-content-center flex-grow-1 text-muted">
                                <div class="text-center">
                                    <i class="fas fa-hand-point-left fa-2x mb-2 d-block"></i>
                                    Select a conversation from the left to get started.
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== CENTRAL SUPPORT TAB ===== --}}
    <div class="tab-pane fade {{ request('tab') === 'central' ? 'show active' : '' }}"
         id="central" role="tabpanel">
        <div class="card shadow-sm" style="border-top-left-radius:0; border-top-right-radius:0;">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-headset mr-1"></i> Chat with Central Support
                </h6>
                <span class="small text-muted">Tenant: {{ $tenantSlug }}</span>
            </div>
            <div class="card-body d-flex flex-column">

                <div id="centralMessages" class="border rounded p-3 mb-3"
                     style="height: 52vh; overflow-y: auto; background: #f8fafc;">
                    @forelse($centralMessages as $message)
                        @php $mine = ($message['sender_type'] ?? null) === 'tenant'; @endphp
                        <div class="mb-3 {{ $mine ? 'text-right' : '' }}">
                            <div class="small text-muted mb-1">
                                {{ $message['sender_name'] ?? ucfirst(str_replace('_', ' ', $message['sender_type'] ?? 'Central')) }}
                            </div>
                            <div class="d-inline-block px-3 py-2 rounded
                                {{ $mine ? 'bg-primary text-white' : 'bg-white border' }}"
                                 style="max-width: 85%;">
                                {{ $message['message'] ?? '' }}
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center mt-4">
                            <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                            No messages yet with Central Support.
                        </div>
                    @endforelse
                </div>

                <form id="centralChatForm" class="d-flex" autocomplete="off">
                    @csrf
                    <input id="centralInput" name="message" type="text" class="form-control mr-2"
                           maxlength="2000" placeholder="Message Central Support..." required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    var csrf         = '{{ csrf_token() }}';
    var messagesRoute = '{{ route('support.chat.messages') }}';
    var storeRoute    = '{{ route('support.chat.store') }}';

    // ── Helpers ──────────────────────────────────────────────────────────────
    function esc(t) {
        var d = document.createElement('div');
        d.textContent = t || '';
        return d.innerHTML;
    }
    function timeAgo(iso) {
        if (!iso) return '';
        var diff = Math.floor((Date.now() - new Date(iso)) / 1000);
        if (diff < 60)   return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }
    function scrollBottom(el) {
        if (el) el.scrollTop = el.scrollHeight;
    }

    // ── LOCAL THREAD ─────────────────────────────────────────────────────────
    @if($threadWith)
    (function () {
        var localMessages = document.getElementById('localMessages');
        var localForm     = document.getElementById('localChatForm');
        var localInput    = document.getElementById('localInput');
        var localSig      = '';
        var withId        = {{ $threadWith->id }};

        function renderLocal(messages) {
            if (!localMessages) return;
            var sig = messages.map(function (m) { return m.id + ':' + m.created_at; }).join('|');
            if (sig === localSig) return;
            localSig = sig;

            if (!messages.length) {
                localMessages.innerHTML = '<div class="text-muted text-center mt-4">' +
                    '<i class="fas fa-comments fa-2x mb-2 d-block"></i>' +
                    'No messages yet in this conversation.</div>';
                return;
            }

            localMessages.innerHTML = messages.map(function (m) {
                var mine = m.sender_type === 'mine';
                return '<div class="mb-3 ' + (mine ? 'text-right' : '') + '">' +
                    '<div class="small text-muted mb-1">' + esc(mine ? 'You' : m.sender_name) +
                    '<span class="ml-1" style="font-size:.75em;">' + timeAgo(m.created_at) + '</span></div>' +
                    '<div class="d-inline-block px-3 py-2 rounded ' +
                    (mine ? 'bg-primary text-white' : 'bg-white border') +
                    '" style="max-width:85%;">' + esc(m.message) + '</div></div>';
            }).join('');

            scrollBottom(localMessages);
        }

        function pollLocal() {
            fetch(messagesRoute + '?with=' + withId)
                .then(function (r) { return r.json(); })
                .then(function (d) { renderLocal(d.messages || []); })
                .catch(function () {});
        }

        if (localForm) {
            localForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!localInput) return;
                var msg = localInput.value.trim();
                if (!msg) return;

                var p = new URLSearchParams();
                p.append('_token', csrf);
                p.append('message', msg);
                p.append('channel', 'local');
                p.append('receiver_id', withId);

                fetch(storeRoute, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: p.toString(),
                })
                    .then(function () { localInput.value = ''; pollLocal(); })
                    .catch(function () {});
            });
        }

        scrollBottom(localMessages);
        pollLocal();
        setInterval(pollLocal, 4000);
    })();
    @endif

    // ── CENTRAL CHAT ─────────────────────────────────────────────────────────
    (function () {
        var centralMessages = document.getElementById('centralMessages');
        var centralForm     = document.getElementById('centralChatForm');
        var centralInput    = document.getElementById('centralInput');
        var centralSig      = '';

        function renderCentral(messages) {
            if (!centralMessages) return;
            var sig = messages.map(function (m) { return (m.id || '') + ':' + (m.created_at || ''); }).join('|');
            if (sig === centralSig) return;
            centralSig = sig;

            if (!messages.length) {
                centralMessages.innerHTML = '<div class="text-muted text-center mt-4">' +
                    '<i class="fas fa-comments fa-2x mb-2 d-block"></i>' +
                    'No messages yet with Central Support.</div>';
                return;
            }

            centralMessages.innerHTML = messages.map(function (m) {
                var mine = m.sender_type === 'tenant';
                return '<div class="mb-3 ' + (mine ? 'text-right' : '') + '">' +
                    '<div class="small text-muted mb-1">' +
                    esc(m.sender_name || (m.sender_type || '').replace('_', ' ')) + '</div>' +
                    '<div class="d-inline-block px-3 py-2 rounded ' +
                    (mine ? 'bg-primary text-white' : 'bg-white border') +
                    '" style="max-width:85%;">' + esc(m.message) + '</div></div>';
            }).join('');

            scrollBottom(centralMessages);
        }

        function pollCentral() {
            var centralPane = document.getElementById('central');
            if (!centralPane || !centralPane.classList.contains('active')) return;

            fetch(messagesRoute)
                .then(function (r) { return r.json(); })
                .then(function (d) { renderCentral(d.messages || []); })
                .catch(function () {});
        }

        if (centralForm) {
            centralForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!centralInput) return;
                var msg = centralInput.value.trim();
                if (!msg) return;

                var p = new URLSearchParams();
                p.append('_token', csrf);
                p.append('message', msg);
                p.append('channel', 'central');

                fetch(storeRoute, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: p.toString(),
                })
                    .then(function () { centralInput.value = ''; pollCentral(); })
                    .catch(function () {});
            });
        }

        scrollBottom(centralMessages);
        pollCentral();
        setInterval(pollCentral, 6000);
    })();
})();
</script>
@endpush
