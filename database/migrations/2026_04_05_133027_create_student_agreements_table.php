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
        Schema::create('student_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('doc_number')->unique();
            
            // Step 1: Perjanjian Kontrak
            $table->string('contract_level')->nullable(); // ULA, WUSTHO, ULYA, TUGAS
            $table->boolean('contract_agreed')->default(false);
            $table->timestamp('contract_agreed_at')->nullable();
            
            // Step 2: Taat Undang-Undang
            $table->boolean('compliance_agreed')->default(false);
            $table->timestamp('compliance_agreed_at')->nullable();
            
            // Step 3: Tes Urin
            $table->boolean('urine_test_agreed')->default(false);
            $table->timestamp('urine_test_agreed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_agreements');
    }
};
