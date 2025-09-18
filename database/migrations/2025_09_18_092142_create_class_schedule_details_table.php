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
        Schema::create('class_schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_schedule_id')->constrained('class_schedules', 'id')->cascadeOnDelete();
            $table->foreignId('lesson_hour_id')->constrained('lesson_hours', 'id')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('staff', 'id')->cascadeOnDelete();
            $table->foreignId('study_id')->constrained('studies', 'id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_schedule_details');
    }
};
