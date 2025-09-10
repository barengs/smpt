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
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('plan_id')->constrained('installment_plans', 'id')->onDelete('cascade');
            $table->date('due_date');
            $table->decimal('amount_due', 18, 2);
            $table->enum('status', ['PENDING', 'PAID', 'OVERDUE']);
            $table->date('payment_date')->nullable();
            $table->foreignUuid('payment_transaction_id')->nullable()->constrained('transactions', 'id')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
};
