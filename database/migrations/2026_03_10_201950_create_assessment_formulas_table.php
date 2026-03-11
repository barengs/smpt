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
        Schema::create('assessment_formulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_schedule_detail_id')->constrained('class_schedule_details')->onDelete('cascade');
            $table->string('name')->default('Standar K13');
            $table->enum('type', ['standar_k13', 'merdeka', 'custom'])->default('standar_k13');
            $table->json('knowledge_formula')->nullable();
            $table->json('skill_formula')->nullable();
            $table->decimal('attendance_weight', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_formulas');
    }
};
