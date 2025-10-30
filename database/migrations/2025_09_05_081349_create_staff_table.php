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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['Pria', 'Wanita', 'L', 'P'])->default('Pria');
            $table->string('nik')->nullable();
            $table->string('nip')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('photo')->nullable();
            $table->enum('marital_status', ['Belum Menikah', 'Menikah', 'Duda', 'Janda'])->default('Belum Menikah');
            $table->unsignedBigInteger('job_id')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
