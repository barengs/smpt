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
        Schema::create('student_card_settings', function (Blueprint $table) {
            $table->id();
            $table->string('front_template')->nullable();
            $table->string('back_template')->nullable();
            $table->string('stamp')->nullable();
            $table->string('signature')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_card_settings');
    }
};
