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
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_assessment_id')->constrained('student_assessments')->onDelete('cascade');
            $table->string('component'); // e.g., 'tugas', 'uh', 'praktik'
            $table->integer('sequence')->default(1); // e.g., UH 1, UH 2
            $table->decimal('score', 5, 2);
            $table->date('date')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            
            $table->unique(['student_assessment_id', 'component', 'sequence'], 'assessment_score_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
    }
};
