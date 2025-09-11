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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->timestamp('registration_date')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected', 'accepted'])->default('pending');
            $table->string('parent_id');
            $table->string('nis')->unique();
            $table->string('period')->nullable();
            $table->string('nik')->unique()->nullable();
            $table->string('kk');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('address')->nullable();
            $table->string('born_in')->nullable();
            $table->date('born_at')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->nullable();
            $table->string('payment_amount')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('previous_school_address')->nullable();
            $table->string('certificate_number')->nullable();
            $table->unsignedBigInteger('education_level_id')->nullable();
            $table->string('previous_madrasah')->nullable();
            $table->string('previous_madrasah_address')->nullable();
            $table->string('certificate_madrasah')->nullable();
            $table->unsignedBigInteger('madrasah_level_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
