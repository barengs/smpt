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
        Schema::create('institutional_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('partner_id')->constrained('partners', 'id')->onDelete('restrict');
            $table->string('product_code', 50)->unique();
            $table->string('product_name');
            $table->decimal('fixed_amount', 18, 2);
            $table->json('available_schemes')->comment("['FULL_PAYMENT', 'INSTALLMENT']");
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutional_products');
    }
};
