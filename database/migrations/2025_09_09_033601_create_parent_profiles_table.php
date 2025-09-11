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
        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('kk');
            $table->string('nik')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('parent_as', ['ayah', 'ibu'])->default('ayah');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('card_address')->nullable();
            $table->string('domicile_address')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('education_id')->nullable();
            $table->text('photo')->nullable();
            $table->string('photo_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_profiles');
    }
};
