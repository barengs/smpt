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
        Schema::table('meeting_schedules', function (Blueprint $table) {
            // Check if the column with typo exists before renaming
            if (Schema::hasColumn('meeting_schedules', 'class_shedule_detail_id')) {
                // Rename the column with typo to correct name
                $table->renameColumn('class_shedule_detail_id', 'class_schedule_detail_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_schedules', function (Blueprint $table) {
            // Check if the correct column exists before renaming back
            if (Schema::hasColumn('meeting_schedules', 'class_schedule_detail_id')) {
                // Revert the column name back to the typo
                $table->renameColumn('class_schedule_detail_id', 'class_shedule_detail_id');
            }
        });
    }
};
