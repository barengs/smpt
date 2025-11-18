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
        Schema::table('position_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('hostel_id')->nullable()->after('staff_id');
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('hostel_id');

            $table->foreign('hostel_id')->references('id')->on('hostels')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');

            $table->index(['hostel_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position_assignments', function (Blueprint $table) {
            $table->dropForeign(['hostel_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropIndex(['hostel_id', 'academic_year_id']);
            $table->dropColumn(['hostel_id', 'academic_year_id']);
        });
    }
};
