<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Drop sisa tabel pendidikan pesantren (tabel `educations` tetap dipertahankan untuk data awal & wali santri)
        Schema::dropIfExists('education_has_education_classes');
        Schema::dropIfExists('education_classes');
        Schema::dropIfExists('educational_institutions');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way cleanup: educational institutions & classes are managed in Tarbiyah
    }
};
