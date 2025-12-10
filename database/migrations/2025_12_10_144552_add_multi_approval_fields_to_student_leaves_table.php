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
        Schema::table('student_leaves', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('id')->comment('User/Staff yang membuat izin');
            $table->boolean('requires_multi_approval')->default(true)->after('leave_type_id')->comment('Butuh persetujuan multi-level');
            $table->integer('approval_count')->default(0)->after('requires_multi_approval')->comment('Jumlah approval yang sudah didapat');
            $table->integer('required_approvals')->default(3)->after('approval_count')->comment('Jumlah approval yang dibutuhkan');
            $table->boolean('all_approved')->default(false)->after('required_approvals')->comment('Semua sudah approve');

            $table->foreign('created_by')->references('id')->on('staff')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_leaves', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'created_by',
                'requires_multi_approval',
                'approval_count',
                'required_approvals',
                'all_approved'
            ]);
        });
    }
};
