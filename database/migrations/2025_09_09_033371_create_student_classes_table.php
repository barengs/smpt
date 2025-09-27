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
        Schema::create('student_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('education_id')
                ->constrained('educations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('class_id')
                ->constrained('classrooms')
                ->cascadeOnDelete();
            $table->foreignId('class_group_id')
                ->constrained('class_groups')
                ->cascadeOnDelete();
            $table->enum('approval_status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->string('approval_note')
                ->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unique(['student_id', 'academic_year_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_classes');
    }
};
