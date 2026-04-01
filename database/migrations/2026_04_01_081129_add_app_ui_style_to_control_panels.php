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
        Schema::table('control_panels', function (Blueprint $table) {
            $table->enum('app_ui_style', ['modern', 'classic'])->default('modern')->after('app_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_panels', function (Blueprint $table) {
            $table->dropColumn('app_ui_style');
        });
    }
};
