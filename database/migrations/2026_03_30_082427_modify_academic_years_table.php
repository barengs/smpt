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
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn(['type', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->enum('type', ['semester', 'triwulan'])->default('semester');
            $table->enum('periode', ['ganjil', 'genap', 'pendek', 'cawu 1', 'cawu 2', 'cawu 3'])->default('ganjil');
        });
    }
};
