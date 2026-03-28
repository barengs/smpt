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
        Schema::table('student_card_settings', function (Blueprint $table) {
            $table->string('guardian_front_template')->nullable()->after('back_template');
            $table->string('guardian_back_template')->nullable()->after('guardian_front_template');
            $table->string('kop_surat')->nullable()->after('guardian_back_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_card_settings', function (Blueprint $table) {
            $table->dropColumn(['guardian_front_template', 'guardian_back_template', 'kop_surat']);
        });
    }
};
