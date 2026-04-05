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
        Schema::table('clearances', function (Blueprint $table) {
            $table->string('office_or_instructor')->nullable()->after('remarks');
            $table->string('approval_location')->nullable()->after('office_or_instructor');
            $table->string('clearance_title')->nullable()->after('approval_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearances', function (Blueprint $table) {
            $table->dropColumn(['office_or_instructor', 'approval_location', 'clearance_title']);
        });
    }
};
