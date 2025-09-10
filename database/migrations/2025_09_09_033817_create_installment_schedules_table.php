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
        Schema::create('installment_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('account_number', 20);
            $table->foreignUuid('original_transaction_id')->constrained('transactions', 'id')->onDelete('restrict');
            $table->decimal('principal_amount', 18, 2);
            $table->decimal('total_amount_due', 18, 2);
            $table->decimal('monthly_payment', 18, 2);
            $table->integer('number_of_installments');
            $table->decimal('remaining_balance', 18, 2);
            $table->enum('status', ['ACTIVE', 'PAID_OFF', 'DEFAULT']);
            $table->date('start_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_schedules');
    }
};
