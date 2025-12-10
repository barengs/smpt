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
        Schema::create('student_leave_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_leave_id');
            $table->enum('approver_role', ['keamanan', 'kepala_asrama', 'wali_kelas'])->comment('Role yang memberikan approval');
            $table->unsignedBigInteger('approver_id')->comment('Staff ID yang approve');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable()->comment('Catatan approval/rejection');
            $table->timestamp('reviewed_at')->nullable()->comment('Waktu di-review');
            $table->integer('approval_order')->default(1)->comment('Urutan approval (1,2,3)');
            $table->timestamps();

            $table->foreign('student_leave_id')->references('id')->on('student_leaves')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('staff')->onDelete('cascade');

            $table->index(['student_leave_id', 'approver_role']);
            $table->index(['student_leave_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_leave_approvals');
    }
};
