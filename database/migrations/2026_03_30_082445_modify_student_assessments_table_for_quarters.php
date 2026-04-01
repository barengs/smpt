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
        Schema::table('student_assessments', function (Blueprint $table) {
            $table->dropForeign(['class_schedule_detail_id']);
            $table->dropForeign(['student_id']);
            $table->dropUnique('student_assessment_unique');
            $table->dropColumn('semester');
            $table->foreignId('academic_quarter_id')->nullable()->after('academic_year_id')->constrained('academic_quarters')->onDelete('cascade');
            $table->unique(['class_schedule_detail_id', 'student_id', 'academic_quarter_id'], 'stu_assessment_quarter_unique');
            $table->foreign('class_schedule_detail_id')->references('id')->on('class_schedule_details')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_assessments', function (Blueprint $table) {
            $table->dropForeign(['class_schedule_detail_id']);
            $table->dropForeign(['student_id']);
            $table->dropUnique('stu_assessment_quarter_unique');
            $table->dropForeign(['academic_quarter_id']);
            $table->dropColumn('academic_quarter_id');
            $table->enum('semester', ['1', '2'])->default('1');
            $table->unique(['class_schedule_detail_id', 'student_id', 'semester'], 'student_assessment_unique');
            $table->foreign('class_schedule_detail_id')->references('id')->on('class_schedule_details')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }
};
