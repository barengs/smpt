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
            $table->foreignId('classroom_id')
                ->constrained('classrooms')
                ->onDelete('cascade'); // kelas
            $table->foreignId('class_group_id')
                ->constrained('class_groups')
                ->onDelete('cascade'); // rombel
            $table->string('day')
                ->nullable()
                ->comment('senin, selasa, rabu, dll'); // hari
            $table->foreignId('lesson_hour_id')->constrained('lesson_hours', 'id')->cascadeOnDelete(); // jam pelajaran / jadwal
            $table->foreignId('teacher_id')->constrained('staff', 'id')->cascadeOnDelete(); // guru
            $table->foreignId('study_id')->constrained('studies', 'id')->cascadeOnDelete(); // mata pelajaran
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
