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
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year', 9)->unique();
            $table->enum('type', ['semester', 'triwulan'])->default('semester');
            $table->enum('periode', ['ganjil', 'genap', 'pendek', 'cawu 1', 'cawu 2', 'cawu 3'])->default('ganjil');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(false);
            $table->string('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
