@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Support Chat</h1>
    @if($admin)
        <span class="small text-muted"><i class="fas fa-user-shield mr-1"></i>Chatting with: <strong>{{ $admin->name }}</strong> (School Admin)</span>
    @else
        <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>No school admin assigned yet</span>
    @endif
</div>

<div class="card shadow-sm">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-headset mr-1"></i> School Support Chat
        </h6>
        @if($admin)
            <span class="small text-muted">Messages go directly to your School Admin</span>
        @endif
    </div>
    <div class="card-body d-flex flex-column">

        @if(!empty($tableError))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Chat setup required.</strong>
                The support chat table hasn't been created yet. Please run:
                <code>php artisan migrate</code> (with <code>DB_DATABASE=maica_university</code>).
            </div>
        @elseif(!$admin)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle mr-1"></i>
                There is currently no School Admin assigned. Please contact your institution.
            </div>
        @else
            <div id="chatMessages" class="border rounded p-3 mb-3"
                 style="height: 52vh; overflow-y: auto; background: #f8fafc;">
                @forelse($messages as $message)
                    @php $mine = $message->sender_id === $currentUser->id; @endphp
                    <div class="mb-3 {{ $mine ? 'text-right' : '' }}">
                        <div class="small text-muted mb-1">
                            {{ $mine ? 'You' : $message->sender->name }}
                            <span class="ml-1" style="font-size:0.75em;">
                                {{ $message->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="d-inline-block px-3 py-2 rounded
                            {{ $mine ? 'bg-primary text-white' : 'bg-white border' }}"
                             style="max-width: 85%;">
                            {{ $message->message }}
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center mt-4">
                        <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                        No messages yet. Start the conversation with your School Admin.
                    </div>
                @endforelse
            </div>

            <form id="chatForm" class="d-flex" autocomplete="off">
                @csrf
                <input id="chatInput" name="message" type="text" class="form-control mr-2"
                       maxlength="2000" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane mr-1"></i> Send
                </button>
            </form>
        @endif

    </div>
</div>

@if($admin && empty($tableError))
@push('scripts')
<script>
(function () {
    var messagesEl = document.getElementById('chatMessages');
    var formEl     = document.getElementById('chatForm');
    var inputEl    = document.getElementById('chatInput');
    var lastSig    = '';
    var currentUserId = {{ $currentUser->id }};

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text || '';
        return d.innerHTML;
    }

    function timeAgo(iso) {
        if (!iso) return '';
        var diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
        if (diff < 60)  return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function render(messages) {
        var sig = messages.map(function (m) { return m.id + ':' + m.created_at; }).join('|');
        if (sig === lastSig) return;
        lastSig = sig;

        if (!messages.length) {
            messagesEl.innerHTML = '<div class="text-muted text-center mt-4">' +
                '<i class="fas fa-comments fa-2x mb-2 d-block"></i>' +
                'No messages yet. Start the conversation with your School Admin.</div>';
            return;
        }

        messagesEl.innerHTML = messages.map(function (m) {
            var mine = m.sender_type === 'mine';
            return '<div class="mb-3 ' + (mine ? 'text-right' : '') + '">' +
                '<div class="small text-muted mb-1">' +
                escapeHtml(mine ? 'You' : m.sender_name) +
                '<span class="ml-1" style="font-size:.75em;">' + timeAgo(m.created_at) + '</span>' +
                '</div>' +
                '<div class="d-inline-block px-3 py-2 rounded ' +
                (mine ? 'bg-primary text-white' : 'bg-white border') +
                '" style="max-width:85%;">' +
                escapeHtml(m.message) +
                '</div></div>';
        }).join('');

        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function loadMessages() {
        fetch('{{ route('support.chat.messages') }}')
            .then(function (r) { return r.json(); })
            .then(function (data) { render(data.messages || []); })
            .catch(function () {});
    }

    if (!formEl || !messagesEl || !inputEl) return;

    formEl.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = inputEl.value.trim();
        if (!msg) return;

        var payload = new URLSearchParams();
        payload.append('_token', '{{ csrf_token() }}');
        payload.append('message', msg);
        payload.append('channel', 'local');

        fetch('{{ route('support.chat.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload.toString(),
        })
            .then(function (r) { return r.json(); })
            .then(function () { inputEl.value = ''; loadMessages(); })
            .catch(function () {});
    });

    loadMessages();
    messagesEl.scrollTop = messagesEl.scrollHeight;
    setInterval(loadMessages, 4000);
})();
</script>
@endpush
@endif
@endsection
