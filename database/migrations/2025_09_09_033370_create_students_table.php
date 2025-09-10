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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('parent_id');
            $table->string('nis')->unique();
            $table->string('period')->nullable();
            $table->string('nik')->unique()->nullable();
            $table->string('kk')->unique()->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('address')->nullable();
            $table->string('born_in')->nullable();
            $table->date('born_at')->nullable();
            $table->string('last_education')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('hostel_id')->nullable();
            $table->unsignedBigInteger('program_id');
            $table->enum('status', ['Tidak Aktif', 'Aktif', 'Tugas', 'Lulus', 'Dikeluarkan'])->default('Tidak Aktif')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
