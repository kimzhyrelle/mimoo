<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds additional fields for seller registration:
     * - TIN (Tax Identification Number)
     * - Postal Code
     * - Business Type (sole_proprietor, partnership, corporation)
     * - Additional document uploads (DTI, BIR 2303, SEC Certificate)
     * - Privacy Policy acceptance flag
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add TIN field after contact_number
            $table->string('tin', 50)->nullable()->after('contact_number');
            
            // Add postal_code after barangay
            $table->string('postal_code', 10)->nullable()->after('barangay');
            
            // Add business_type after business_name
            $table->enum('business_type', ['sole_proprietor', 'partnership', 'corporation'])->nullable()->after('business_name');
            
            // Add document paths after business_permit
            $table->string('dti_registration')->nullable()->after('business_permit');
            $table->string('bir_2303')->nullable()->after('dti_registration');
            $table->string('sec_certificate')->nullable()->after('bir_2303');
            
            // Add privacy policy acceptance flag
            $table->boolean('privacy_policy_accepted')->default(false)->after('sec_certificate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tin',
                'postal_code',
                'business_type',
                'dti_registration',
                'bir_2303',
                'sec_certificate',
                'privacy_policy_accepted',
            ]);
        });
    }
};
