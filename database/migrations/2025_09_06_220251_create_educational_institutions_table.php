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
        Schema::create('educational_institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_id')
                ->constrained('educations')
                ->onDelete('cascade');
            $table->foreignId('education_class_id')
                ->constrained('education_classes')
                ->onDelete('cascade');
            $table->string('registration_number')->unique()->nullable();
            $table->string('institution_name');
            $table->string('institution_address')->nullable();
            $table->string('institution_phone')->nullable();
            $table->string('institution_email')->nullable();
            $table->string('institution_website')->nullable();
            $table->string('institution_logo')->nullable();
            $table->string('institution_banner')->nullable();
            $table->enum('institution_status', ['active', 'inactive'])->default('active')->nullable();
            $table->string('institution_description');
            $table->foreignId('headmaster_id')
                ->constrained('staff', 'id')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_institutions');
    }
};
