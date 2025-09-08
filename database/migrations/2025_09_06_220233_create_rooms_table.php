<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ruang kamar santri
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama ruangan');
            $table->foreignId('hostel_id')->constrained('hostels')->onDelete('cascade')->comment('ID asrama terkait');
            $table->integer('capacity')->default(0)->comment('Kapasitas ruangan');
            $table->text('description')->nullable()->comment('Deskripsi ruangan');
            $table->boolean('is_active')->default(true)->comment('Status aktif ruangan');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
