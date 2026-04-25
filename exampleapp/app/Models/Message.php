<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'local_messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * The user who sent this message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * The user who received this message.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get all messages in a conversation between two users (ordered oldest first).
     *
     * @param int $userA
     * @param int $userB
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function conversationBetween(int $userA, int $userB)
    {
        return static::query()
            ->where(function ($q) use ($userA, $userB) {
                $q->where('sender_id', $userA)->where('receiver_id', $userB);
            })
            ->orWhere(function ($q) use ($userA, $userB) {
                $q->where('sender_id', $userB)->where('receiver_id', $userA);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Count unread messages received by a given user from a given sender.
     * If $fromSenderId is null, counts all unread messages for the receiver.
     */
    public static function unreadCountFor(int $receiverId, ?int $fromSenderId = null): int
    {
        $query = static::query()
            ->where('receiver_id', $receiverId)
            ->where('is_read', false);

        if ($fromSenderId !== null) {
            $query->where('sender_id', $fromSenderId);
        }

        return $query->count();
    }
}
