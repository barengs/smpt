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
        // Add new column first
        Schema::table('sanctions', function (Blueprint $table) {
            $table->foreignId('sanction_type_id')->nullable()->after('description')->constrained('sanction_types');
        });

        // Migrate data
        $types = ['peringatan', 'skorsing', 'pembinaan', 'denda', 'lainnya'];
        $typeMap = [];
        foreach ($types as $typeStr) {
            $id = DB::table('sanction_types')->insertGetId([
                'name' => ucfirst($typeStr),
                'description' => 'Jenis sanksi ' . $typeStr,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $typeMap[$typeStr] = $id;
        }

        $sanctions = DB::table('sanctions')->get();
        foreach ($sanctions as $sanction) {
            if (isset($typeMap[$sanction->type])) {
                DB::table('sanctions')->where('id', $sanction->id)->update([
                    'sanction_type_id' => $typeMap[$sanction->type]
                ]);
            }
        }

        // Drop old column
        Schema::table('sanctions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanctions', function (Blueprint $table) {
            $table->enum('type', ['peringatan', 'skorsing', 'pembinaan', 'denda', 'lainnya'])->default('peringatan');
        });

        $sanctions = DB::table('sanctions')->get();
        foreach ($sanctions as $sanction) {
            if ($sanction->sanction_type_id) {
                $type = DB::table('sanction_types')->where('id', $sanction->sanction_type_id)->first();
                if ($type) {
                    $typeName = strtolower($type->name);
                    $allowed = ['peringatan', 'skorsing', 'pembinaan', 'denda', 'lainnya'];
                    if (in_array($typeName, $allowed)) {
                        DB::table('sanctions')->where('id', $sanction->id)->update(['type' => $typeName]);
                    }
                }
            }
        }

        Schema::table('sanctions', function (Blueprint $table) {
            $table->dropForeign(['sanction_type_id']);
            $table->dropColumn('sanction_type_id');
        });
    }
};
