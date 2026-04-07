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
        Schema::table('registrations', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'verified', 'rejected', 'accepted'])->default('draft')->change();
            $table->string('parent_id')->nullable()->change();
            $table->string('nis')->nullable()->change();
            $table->string('kk')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'verified', 'rejected', 'accepted'])->default('pending')->change();
            $table->string('parent_id')->change();
            $table->string('nis')->change();
            $table->string('kk')->change();
        });
    }
};
