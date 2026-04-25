<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the local_messages table for school-level chat.
     * - Students/Staff can message the School Admin (scope: tenant DB).
     * - School Admin can reply to Students/Staff.
     * - School Admin ↔ Central uses the existing API channel (TenantService).
     */
    public function up(): void
    {
        Schema::create('local_messages', function (Blueprint $table) {
            $table->id();

            // Sender user ID (from users table in tenant DB)
            $table->unsignedBigInteger('sender_id');

            // Receiver user ID (from users table in tenant DB)
            $table->unsignedBigInteger('receiver_id');

            // The message body
            $table->text('message');

            // Whether the receiver has read this message
            $table->boolean('is_read')->default(false);

            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            // Index for fetching conversations efficiently
            $table->index(['sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_messages');
    }
};
