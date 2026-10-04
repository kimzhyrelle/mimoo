<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds all extra fields collected by the multi-step registration wizard.
     * The `status` column drives the admin approval workflow:
     *   - pending    → just registered, awaiting admin review
     *   - approved   → admin approved, account is active
     *   - disapproved → admin rejected the application
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Personal details
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('middle_initial', 1)->nullable()->after('last_name');
            $table->enum('sex', ['male', 'female'])->nullable()->after('middle_initial');
            $table->string('contact_number', 20)->nullable()->after('sex');
            $table->date('birthday')->nullable()->after('contact_number');

            // Account classification
            $table->enum('account_type', ['buyer', 'seller'])->default('buyer')->after('birthday');

            // Address
            $table->string('province')->nullable()->after('account_type');
            $table->string('municipality_city')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('municipality_city');
            $table->text('street_address')->nullable()->after('barangay');

            // Identity / documents (stored as file paths)
            $table->string('id_document')->nullable()->after('street_address');
            $table->string('business_name')->nullable()->after('id_document');
            $table->string('line_of_business')->nullable()->after('business_name');
            $table->string('business_permit')->nullable()->after('line_of_business');

            // Admin approval workflow
            $table->enum('status', ['pending', 'approved', 'disapproved'])->default('pending')->after('business_permit');
            $table->timestamp('approved_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'last_name', 'middle_initial', 'sex',
                'contact_number', 'birthday', 'account_type',
                'province', 'municipality_city', 'barangay', 'street_address',
                'id_document', 'business_name', 'line_of_business', 'business_permit',
                'status', 'approved_at',
            ]);
        });
    }
};
