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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('id_title')->unique();
            $table->string('en_title')->unique()->nullable();
            $table->string('ar_title')->unique()->nullable();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('route')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('type', ['main', 'submenu', 'link', 'external'])->default('main');
            $table->enum('position', ['sidebar', 'header', 'footer'])->default('sidebar');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('order')->nullable();
            $table->timestamps();
            // Foreign key constraint for parent_id
            $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
        });

        // Create a pivot table for many-to-many relationship between menus and users
        Schema::create('menu_permissions', function (Blueprint $table) {
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['menu_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
