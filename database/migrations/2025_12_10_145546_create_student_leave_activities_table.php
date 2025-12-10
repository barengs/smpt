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
        Schema::create('student_leave_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_leave_id');
            $table->enum('activity_type', [
                'created',              // Dokumen dibuat
                'submitted',            // Diajukan untuk approval
                'approved_by_role',     // Disetujui oleh role tertentu
                'rejected_by_role',     // Ditolak oleh role tertentu
                'fully_approved',       // Semua approval terkumpul
                'fully_rejected',       // Ditolak
                'report_submitted',     // Laporan kembali disubmit
                'report_verified',      // Laporan diverifikasi
                'penalty_assigned',     // Penalti diberikan
                'cancelled',            // Dibatalkan
                'updated'               // Diupdate
            ])->comment('Jenis aktivitas');
            $table->unsignedBigInteger('actor_id')->nullable()->comment('Staff/User yang melakukan aksi');
            $table->string('actor_type')->default('staff')->comment('Tipe actor: staff, system');
            $table->string('actor_role')->nullable()->comment('Role saat melakukan aksi (keamanan, kepala_asrama, wali_kelas)');
            $table->text('description')->nullable()->comment('Deskripsi aktivitas');
            $table->json('metadata')->nullable()->comment('Data tambahan (before, after, notes, dll)');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('student_leave_id')->references('id')->on('student_leaves')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('staff')->onDelete('set null');

            $table->index(['student_leave_id', 'activity_type']);
            $table->index(['student_leave_id', 'created_at']);
            $table->index('actor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_leave_activities');
    }
};
