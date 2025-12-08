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
        // Jenis Izin (pulang, keluar pesantren, sakit, dll)
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Pulang, Keluar Pesantren, Sakit, dll
            $table->text('description')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->integer('max_duration_days')->nullable()->comment('Maksimal durasi dalam hari');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Pengajuan Izin Siswa
        Schema::create('student_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->date('start_date')->comment('Tanggal mulai izin');
            $table->date('end_date')->comment('Tanggal selesai izin');
            $table->integer('duration_days')->comment('Lama hari izin');
            $table->text('reason')->comment('Alasan izin');
            $table->string('destination')->nullable()->comment('Tujuan (alamat tujuan)');
            $table->string('contact_person')->nullable()->comment('Nama kontak yang bisa dihubungi');
            $table->string('contact_phone')->nullable()->comment('Nomor telepon kontak');
            $table->enum('status', [
                'pending',      // Menunggu persetujuan
                'approved',     // Disetujui
                'rejected',     // Ditolak
                'active',       // Sedang berlangsung (approved + sudah masuk tanggal)
                'completed',    // Selesai (sudah lapor kembali)
                'overdue',      // Terlambat lapor
                'cancelled'     // Dibatalkan
            ])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('Staff ID yang menyetujui');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable()->comment('Catatan persetujuan/penolakan');
            $table->date('expected_return_date')->nullable()->comment('Tanggal seharusnya kembali');
            $table->date('actual_return_date')->nullable()->comment('Tanggal aktual kembali');
            $table->boolean('has_penalty')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('staff')->onDelete('set null');

            $table->index(['student_id', 'status']);
            $table->index(['status', 'start_date', 'end_date']);
            $table->index(['academic_year_id', 'status']);
        });

        // Laporan Kembali dari Izin
        Schema::create('student_leave_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_leave_id');
            $table->date('report_date')->comment('Tanggal melaporkan kembali');
            $table->time('report_time')->nullable();
            $table->text('report_notes')->nullable()->comment('Catatan saat lapor kembali');
            $table->enum('condition', ['sehat', 'sakit', 'lainnya'])->default('sehat')->comment('Kondisi saat kembali');
            $table->boolean('is_late')->default(false)->comment('Apakah terlambat lapor');
            $table->integer('late_days')->default(0)->comment('Jumlah hari keterlambatan');
            $table->unsignedBigInteger('reported_to')->nullable()->comment('Staff ID yang menerima laporan');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable()->comment('Staff ID yang memverifikasi');
            $table->timestamps();

            $table->foreign('student_leave_id')->references('id')->on('student_leaves')->onDelete('cascade');
            $table->foreign('reported_to')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('staff')->onDelete('set null');

            $table->index('student_leave_id');
            $table->index('report_date');
        });

        // Penalti untuk keterlambatan laporan
        Schema::create('student_leave_penalties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_leave_id');
            $table->unsignedBigInteger('student_leave_report_id')->nullable();
            $table->enum('penalty_type', ['peringatan', 'sanksi', 'poin'])->default('peringatan');
            $table->text('description');
            $table->integer('point_value')->default(0)->comment('Poin penalti jika ada');
            $table->unsignedBigInteger('sanction_id')->nullable()->comment('Sanksi yang diberikan');
            $table->unsignedBigInteger('assigned_by')->nullable()->comment('Staff yang memberikan penalti');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->foreign('student_leave_id')->references('id')->on('student_leaves')->onDelete('cascade');
            $table->foreign('student_leave_report_id')->references('id')->on('student_leave_reports')->onDelete('cascade');
            $table->foreign('sanction_id')->references('id')->on('sanctions')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('staff')->onDelete('set null');

            $table->index('student_leave_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_leave_penalties');
        Schema::dropIfExists('student_leave_reports');
        Schema::dropIfExists('student_leaves');
        Schema::dropIfExists('leave_types');
    }
};
