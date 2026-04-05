<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clearance_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clearance_id')->constrained('clearances')->onDelete('cascade');
            $table->string('item_name');
            $table->string('location')->nullable();
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_checklist_items');
    }
};
