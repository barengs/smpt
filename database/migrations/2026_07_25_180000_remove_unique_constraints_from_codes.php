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
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropUnique('organizations_code_unique');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique('positions_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->unique('code');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->unique('code');
        });
    }
};
