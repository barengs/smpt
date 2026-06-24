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
        Schema::table('class_schedules', function (Blueprint $table) {
            // Kita drop kolom session yang lama
            if (Schema::hasColumn('class_schedules', 'session')) {
                $table->dropColumn('session');
            }
            
            // Tambahkan relasi ke lesson_sessions
            $table->foreignId('lesson_session_id')
                  ->nullable()
                  ->after('educational_institution_id')
                  ->constrained('lesson_sessions')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('class_schedules', 'lesson_session_id')) {
                $table->dropForeign(['lesson_session_id']);
                $table->dropColumn('lesson_session_id');
            }
            $table->string('session')->nullable()->comment('sisi, pagi, sore');
        });
    }
};
