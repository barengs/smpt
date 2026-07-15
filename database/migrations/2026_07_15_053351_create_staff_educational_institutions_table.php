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
        Schema::create('staff_educational_institutions', function (Blueprint $table) {
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('educational_institution_id')->constrained('educational_institutions', 'id', 'fk_staff_edu_inst_id')->cascadeOnDelete();
            $table->unique(['staff_id', 'educational_institution_id'], 'staff_edu_inst_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_educational_institutions');
    }
};
