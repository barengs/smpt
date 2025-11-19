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
        // Kategori Pelanggaran
        Schema::create('violation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('severity_level')->default(1)->comment('1=Ringan, 2=Sedang, 3=Berat');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Daftar Pelanggaran
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('point')->default(0)->comment('Poin pelanggaran');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('violation_categories')->onDelete('cascade');
        });

        // Jenis Sanksi
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['peringatan', 'skorsing', 'pembinaan', 'denda', 'lainnya'])->default('peringatan');
            $table->integer('duration_days')->nullable()->comment('Durasi sanksi dalam hari');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Pencatatan Pelanggaran Siswa
        Schema::create('student_violations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('violation_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->date('violation_date');
            $table->time('violation_time')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('reported_by')->nullable()->comment('Staff ID yang melaporkan');
            $table->enum('status', ['pending', 'verified', 'processed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('violation_id')->references('id')->on('violations')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
            $table->foreign('reported_by')->references('id')->on('staff')->onDelete('set null');

            $table->index(['student_id', 'violation_date']);
            $table->index(['status', 'academic_year_id']);
        });

        // Sanksi yang Diberikan ke Siswa
        Schema::create('student_sanctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_violation_id');
            $table->unsignedBigInteger('sanction_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable()->comment('Staff ID yang memberikan sanksi');
            $table->timestamps();

            $table->foreign('student_violation_id')->references('id')->on('student_violations')->onDelete('cascade');
            $table->foreign('sanction_id')->references('id')->on('sanctions')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('staff')->onDelete('set null');

            $table->index(['student_violation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_sanctions');
        Schema::dropIfExists('student_violations');
        Schema::dropIfExists('sanctions');
        Schema::dropIfExists('violations');
        Schema::dropIfExists('violation_categories');
    }
};
