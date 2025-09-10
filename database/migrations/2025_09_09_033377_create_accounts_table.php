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
        Schema::create('accounts', function (Blueprint $table) {
            $table->string('account_number', 20)->primary();
            $table->foreignId('customer_id')->constrained('students', 'id')->onDelete('restrict');
            $table->foreignId('product_id')->constrained('products', 'id')->onDelete('restrict');
            $table->decimal('balance', 18, 2)->default(0);
            $table->enum('status', ['AKTIF', 'TUTUP', 'TERBLOKIR', 'TIDAK AKTIF', 'DIBEKUKAN'])->default('TIDAK AKTIF');
            $table->date('open_date');
            $table->date('close_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
