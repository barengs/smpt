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
        Schema::create('control_panels', function (Blueprint $table) {
            $table->id();
            $table->string('app_name');
            $table->string('app_version')->nullable();
            $table->string('app_description')->nullable();
            $table->string('app_logo')->nullable();
            $table->string('app_favicon')->nullable();
            $table->string('app_url')->nullable();
            $table->string('app_email')->nullable();
            $table->string('app_phone')->nullable();
            $table->string('app_address')->nullable();
            $table->enum('is_maintenance_mode', ['true', 'false'])->default('false');
            $table->text('maintenance_message')->nullable();
            $table->enum('app_theme', ['light', 'dark', 'system'])->default('system');
            $table->enum('app_language', ['indonesia', 'english', 'arabic'])->default('indonesia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_panels');
    }
};
