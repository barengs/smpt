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
        Schema::create('student_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_schedule_detail_id')->constrained('class_schedule_details')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->enum('semester', ['1', '2'])->default('1');
            
            // Sikap
            $table->enum('attitude_spiritual', ['A', 'B', 'C', 'D'])->nullable();
            $table->enum('attitude_social', ['A', 'B', 'C', 'D'])->nullable();
            $table->text('attitude_description')->nullable();
            
            // Computed
            $table->decimal('final_knowledge_score', 5, 2)->nullable();
            $table->decimal('final_skill_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            
            $table->timestamps();
            
            // Prevent duplicate assessment for same student, schedule, and semester
            $table->unique(['class_schedule_detail_id', 'student_id', 'semester'], 'student_assessment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessments');
    }
};
