<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tipe / Jenis Transaksi di Bank Santri
     */
    public function up(): void
    {
        Schema::create('transaction_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['transfer', 'payment', 'cash_operation', 'fee'])->default('payment');
            $table->boolean('is_debit')->default(false);
            $table->boolean('is_credit')->default(false);
            $table->string('default_debit_coa');
            $table->string('default_credit_coa');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_types');
    }
};
