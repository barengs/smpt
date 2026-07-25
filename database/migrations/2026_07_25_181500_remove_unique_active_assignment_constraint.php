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
        Schema::table('position_assignments', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['staff_id']);
            
            // Now we can drop the unique constraint safely
            $table->dropUnique('unique_active_assignment_per_staff');
            
            // Re-add foreign key constraint (Laravel will automatically create a standard index on staff_id)
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position_assignments', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->unique(['staff_id', 'is_active'], 'unique_active_assignment_per_staff');
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }
};
