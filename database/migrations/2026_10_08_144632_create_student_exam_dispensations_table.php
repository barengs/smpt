<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_exam_dispensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_quarter_id')->nullable()->constrained('academic_quarters')->nullOnDelete();
            $table->date('commitment_date')->comment('Tanggal komitmen pelunasan oleh wali');
            $table->decimal('committed_amount', 15, 2)->default(0)->comment('Nominal yang dijanjikan');
            $table->text('reason')->comment('Alasan dispensasi / catatan komitmen');
            $table->string('status', 20)->default('active')->comment('active | revoked | fulfilled');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_exam_dispensations');
    }
};
