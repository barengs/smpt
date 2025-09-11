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
        Schema::create('institutional_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('customer_id')->constrained('students', 'id')->onDelete('restrict');
            $table->foreignUuid('inst_product_id')->constrained('institutional_products', 'id')->onDelete('restrict');
            $table->string('customer_ref_number')->comment('e.g., NIM');
            $table->decimal('amount', 18, 2);
            $table->enum('status', ['UNPAID', 'PAID', 'IN_INSTALLMENT', 'CANCELED']);
            $table->enum('chosen_scheme', ['FULL_PAYMENT', 'INSTALLMENT'])->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutional_bills');
    }
};
