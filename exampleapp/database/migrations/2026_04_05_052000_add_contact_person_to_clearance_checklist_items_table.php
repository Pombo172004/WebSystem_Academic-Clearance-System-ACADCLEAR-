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
        Schema::table('clearance_checklist_items', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearance_checklist_items', function (Blueprint $table) {
            $table->dropColumn('contact_person');
        });
    }
};
