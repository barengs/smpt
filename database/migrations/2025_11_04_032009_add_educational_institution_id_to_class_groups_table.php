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
        Schema::table('class_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('educational_institution_id')->nullable()->after('id');
            $table->foreign('educational_institution_id')
                ->references('id')
                ->on('educational_institutions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropForeign(['educational_institution_id']);
            $table->dropColumn('educational_institution_id');
        });
    }
};
