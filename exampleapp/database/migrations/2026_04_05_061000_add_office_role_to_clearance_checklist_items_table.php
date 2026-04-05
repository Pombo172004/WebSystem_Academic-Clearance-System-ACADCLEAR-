<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clearance_checklist_items', function (Blueprint $table) {
            $table->string('office_role')->nullable()->after('item_name')->index();
        });

        DB::table('clearance_checklist_items')
            ->select(['id', 'item_name'])
            ->orderBy('id')
            ->chunkById(200, function ($items): void {
                foreach ($items as $item) {
                    $role = $this->guessOfficeRole($item->item_name);

                    if ($role !== null) {
                        DB::table('clearance_checklist_items')
                            ->where('id', $item->id)
                            ->update(['office_role' => $role]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearance_checklist_items', function (Blueprint $table) {
            $table->dropIndex(['office_role']);
            $table->dropColumn('office_role');
        });
    }

    private function guessOfficeRole(?string $itemName): ?string
    {
        $value = strtolower((string) $itemName);

        if (str_contains($value, 'library') || str_contains($value, 'librarian')) {
            return 'librarian';
        }

        if (str_contains($value, 'registrar')) {
            return 'registrar';
        }

        if (str_contains($value, 'cashier') || str_contains($value, 'account')) {
            return 'cashier';
        }

        if (str_contains($value, 'guidance')) {
            return 'guidance_counselor';
        }

        if (str_contains($value, 'chair')) {
            return 'department_chair';
        }

        if (str_contains($value, 'research')) {
            return 'research_coordinator';
        }

        if (str_contains($value, 'thesis') || str_contains($value, 'adviser') || str_contains($value, 'advisor')) {
            return 'thesis_adviser';
        }

        if (str_contains($value, 'student affairs') || str_contains($value, 'osa')) {
            return 'student_affairs_officer';
        }

        return null;
    }
};
