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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('partner_code')->unique(); // Unique code for the partner
            $table->string('name');
            $table->string('contact_email')->unique();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable(); // Path to the partner's logo image
            $table->string('website')->nullable(); // Partner's website URL
            $table->string('contact_person')->nullable(); // Name of the contact person at the partner organization
            $table->string('contact_person_position')->nullable(); // Position of the contact person
            $table->string('tax_id')->nullable(); // Tax identification number for the partner
            $table->string('bank_account_number')->nullable(); // Bank account number for transactions
            $table->string('bank_name')->nullable(); // Name of the bank associated with the account
            $table->string('bank_branch')->nullable(); // Branch of the bank
            $table->string('bank_account_name')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active'); // e.g., active, inactive, suspended
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
