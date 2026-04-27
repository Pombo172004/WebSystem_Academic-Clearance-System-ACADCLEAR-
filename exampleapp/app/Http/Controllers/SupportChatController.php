<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function __construct(private TenantService $tenantService)
    {
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX — render the appropriate chat view for the authenticated user
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();

        // School Admin: see their inbox (student/staff threads) + Central tab
        if ($user->role === 'school_admin') {
            return $this->adminInbox($request);
        }

        // Student or Staff: show local conversation with School Admin
        return $this->localChat($request);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOCAL CHAT — Student/Staff view (chat with their School Admin)
    // ─────────────────────────────────────────────────────────────────────────

    private function localChat(Request $request)
    {
        $user = $request->user();

        // Find the school admin for this tenant
        $admin = null;
        $messages = [];
        $tableError = false;

        try {
            $admin = User::where('role', 'school_admin')->first();

            if ($admin) {
                // Mark all messages from admin to this user as read
                Message::where('sender_id', $admin->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

                $messages = Message::conversationBetween($user->id, $admin->id);
            }
        } catch (\Exception $e) {
            \Log::error('Support chat local DB error: ' . $e->getMessage());
            // Table likely hasn't been migrated yet
            $tableError = true;
        }

        return view('support.chat', [
            'messages'     => $messages,
            'admin'        => $admin,
            'currentUser'  => $user,
            'chatMode'     => 'local',
            'tableError'   => $tableError,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN INBOX — School Admin view (threads from students/staff + Central)
    // ─────────────────────────────────────────────────────────────────────────

    private function adminInbox(Request $request)
    {
        $user  = $request->user();
        $tableError = false;

        $threadUsers    = collect();
        $threadWith     = null;
        $threadMessages = collect();

        try {
            // All users who have ever sent or received a local message with this admin
            $threadUserIds = Message::query()
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
                })
                ->selectRaw('IF(sender_id = ?, receiver_id, sender_id) AS other_user_id', [$user->id])
                ->distinct()
                ->pluck('other_user_id');

            $threadUsers = User::whereIn('id', $threadUserIds)
                ->whereIn('role', ['student', 'staff'])
                ->get()
                ->map(function ($u) use ($user) {
                    $u->unread_count = Message::unreadCountFor($user->id, $u->id);
                    return $u;
                });

            // Open a specific thread if ?with=<user_id>
            $withId = (int) $request->query('with', 0);
            if ($withId > 0) {
                $threadWith = User::whereIn('role', ['student', 'staff'])->find($withId);
                if ($threadWith) {
                    // Mark those messages as read
                    Message::where('sender_id', $threadWith->id)
                        ->where('receiver_id', $user->id)
                        ->where('is_read', false)
                        ->update(['is_read' => true]);

                    $threadMessages = Message::conversationBetween($user->id, $threadWith->id);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Support chat admin inbox DB error: ' . $e->getMessage());
            $tableError = true;
        }

        // Central Support tab payload
        $tenantSlug   = (string) ($request->attributes->get('tenant_slug') ?: $this->tenantService->getCurrentTenant());
        $chatPayload  = $this->tenantService->getSupportChatMessages($tenantSlug);

        return view('support.admin-chat', [
            'threadUsers'    => $threadUsers,
            'threadWith'     => $threadWith,
            'threadMessages' => $threadMessages,
            'currentUser'    => $user,
            'tenantSlug'     => $tenantSlug,
            'centralMessages'=> $chatPayload['messages'] ?? [],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MESSAGES — AJAX poll endpoint
    // ─────────────────────────────────────────────────────────────────────────

    public function messages(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'school_admin') {
            // Poll for a specific thread
            $withId = (int) $request->query('with', 0);
            if ($withId > 0) {
                $messages = Message::conversationBetween($user->id, $withId)
                    ->map(fn ($m) => $this->formatLocal($m, $user->id));

                return response()->json(['messages' => $messages]);
            }

            // Central poll
            $tenantSlug  = (string) ($request->attributes->get('tenant_slug') ?: $this->tenantService->getCurrentTenant());
            $chatPayload = $this->tenantService->getSupportChatMessages($tenantSlug);
            return response()->json([
                'messages'     => $chatPayload['messages'] ?? [],
                'conversation' => $chatPayload['conversation'] ?? null,
            ]);
        }

        // Student / Staff: local thread with admin
        $admin = User::where('role', 'school_admin')->first();
        if (!$admin) {
            return response()->json(['messages' => []]);
        }

        // Mark as read on poll
        Message::where('sender_id', $admin->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::conversationBetween($user->id, $admin->id)
            ->map(fn ($m) => $this->formatLocal($m, $user->id));

        return response()->json(['messages' => $messages]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE — send a message
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message'     => ['required', 'string', 'max:2000'],
            'receiver_id' => ['nullable', 'integer'],     // used by admin when replying locally
            'channel'     => ['nullable', 'string', 'in:local,central'],
        ]);

        $user    = $request->user();
        $message = $validated['message'];
        $channel = $validated['channel'] ?? 'local';

        // ── School Admin ──────────────────────────────────────────────────
        if ($user->role === 'school_admin') {

            // Admin → Central Support (existing API)
            if ($channel === 'central') {
                $tenantSlug = (string) ($request->attributes->get('tenant_slug') ?: $this->tenantService->getCurrentTenant());
                $response   = $this->tenantService->sendSupportChatMessage(
                    $tenantSlug,
                    $message,
                    $user->name,
                    $user->id,
                );
                return response()->json($response, 201);
            }

            // Admin → Student/Staff (local)
            $receiverId = (int) ($validated['receiver_id'] ?? 0);
            if (!$receiverId) {
                return response()->json(['success' => false, 'message' => 'No receiver specified.'], 422);
            }

            $receiver = User::whereIn('role', ['student', 'staff'])->find($receiverId);
            if (!$receiver) {
                return response()->json(['success' => false, 'message' => 'Receiver not found.'], 404);
            }

            $msg = Message::create([
                'sender_id'   => $user->id,
                'receiver_id' => $receiverId,
                'message'     => $message,
                'is_read'     => false,
            ]);

            return response()->json(['success' => true, 'message_id' => $msg->id], 201);
        }

        // ── Student / Staff → School Admin (local only) ───────────────────
        $admin = User::where('role', 'school_admin')->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'No school admin found.'], 503);
        }

        $msg = Message::create([
            'sender_id'   => $user->id,
            'receiver_id' => $admin->id,
            'message'     => $message,
            'is_read'     => false,
        ]);

        return response()->json(['success' => true, 'message_id' => $msg->id], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MARK READ — mark all messages from a sender as read (AJAX)
    // ─────────────────────────────────────────────────────────────────────────

    public function markRead(Request $request): JsonResponse
    {
        $user     = $request->user();
        $senderId = (int) $request->input('sender_id', 0);

        if (!$senderId) {
            return response()->json(['success' => false], 422);
        }

        Message::where('sender_id', $senderId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function formatLocal(Message $m, int $currentUserId): array
    {
        return [
            'id'          => $m->id,
            'message'     => $m->message,
            'sender_type' => $m->sender_id === $currentUserId ? 'mine' : 'theirs',
            'sender_name' => optional($m->sender)->name ?? 'User',
            'is_read'     => $m->is_read,
            'created_at'  => $m->created_at?->toIso8601String(),
        ];
    }
}
