<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enums in MySQL are tricky to update via Blueprint in older Laravel versions, 
        // so we use a raw statement for better compatibility and reliability.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE parent_profiles MODIFY COLUMN parent_as ENUM('ayah', 'ibu', 'wali') DEFAULT 'ayah'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE parent_profiles MODIFY COLUMN parent_as ENUM('ayah', 'ibu') DEFAULT 'ayah'");
        }
    }
};
