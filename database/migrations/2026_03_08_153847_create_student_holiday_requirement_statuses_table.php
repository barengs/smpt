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
        Schema::create('student_holiday_requirement_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_holiday_check_id')->constrained('student_holiday_checks', 'id', 'shrs_check_id_fk')->onDelete('cascade');
            $table->foreignId('holiday_requirement_id')->constrained('holiday_requirements', 'id', 'shrs_req_id_fk')->onDelete('cascade');
            $table->boolean('is_met')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_holiday_requirement_statuses');
    }
};
