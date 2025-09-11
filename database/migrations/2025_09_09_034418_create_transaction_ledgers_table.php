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
        Schema::create('transaction_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('transaction_id')->constrained('transactions', 'id')->onDelete('cascade');
            $table->string('coa_code', 20);
            $table->enum('entry_type', ['DEBIT', 'CREDIT']);
            $table->decimal('amount', 18, 2);
            $table->timestamp('entry_time')->useCurrent();

            $table->foreign('coa_code')->references('coa_code')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_ledgers');
    }
};
