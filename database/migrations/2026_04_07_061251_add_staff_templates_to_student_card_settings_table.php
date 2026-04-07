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
            $table->string('staff_front_template')->nullable()->after('guardian_back_template');
            $table->string('staff_back_template')->nullable()->after('staff_front_template');
            $table->unsignedBigInteger('authorized_official_id')->nullable()->after('staff_back_template');
            $table->foreign('authorized_official_id')->references('id')->on('staff')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_card_settings', function (Blueprint $table) {
            $table->dropForeign(['authorized_official_id']);
            $table->dropColumn(['staff_front_template', 'staff_back_template', 'authorized_official_id']);
        });
    }
};
