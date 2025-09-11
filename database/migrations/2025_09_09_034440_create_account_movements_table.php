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
        Schema::create('account_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('account_number', 20);
            $table->foreignUuid('transaction_id')->constrained('transactions', 'id')->onDelete('cascade');
            $table->timestamp('movement_time')->useCurrent();
            $table->text('description');
            $table->decimal('debit_amount', 18, 2)->default(0);
            $table->decimal('credit_amount', 18, 2)->default(0);
            $table->decimal('balance_after_movement', 18, 2);

            $table->foreign('account_number')->references('account_number')->on('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_movements');
    }
};
