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
        Schema::table('student_leave_reports', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('verified_by')->comment('Sudah diverifikasi?');
            $table->text('verification_notes')->nullable()->after('is_verified')->comment('Catatan verifikasi');
            $table->timestamp('submitted_at')->nullable()->after('verification_notes')->comment('Waktu laporan disubmit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_leave_reports', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'verification_notes', 'submitted_at']);
        });
    }
};
