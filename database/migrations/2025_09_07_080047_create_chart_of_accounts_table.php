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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->string('coa_code', 20)->primary();
            $table->string('account_name');
            $table->enum('account_type', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE']);
            $table->string('parent_coa_code', 20)->nullable();
            $table->enum('level', ['header', 'subheader', 'detail']);
            $table->boolean('is_postable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->foreign('parent_coa_code')
                ->references('coa_code')
                ->on('chart_of_accounts')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
