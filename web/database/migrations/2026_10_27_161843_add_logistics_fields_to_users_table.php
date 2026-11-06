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
        Schema::table('users', function (Blueprint $table) {
            // Logistics company fields
            $table->string('company_name')->nullable()->after('business_name');
            $table->string('sec_registration_no')->nullable()->after('company_name');
            $table->string('contact_first_name')->nullable()->after('first_name');
            $table->string('contact_last_name')->nullable()->after('last_name');
            $table->string('contact_position')->nullable()->after('contact_last_name');
            $table->json('service_areas')->nullable()->after('contact_position');
            $table->string('valid_id')->nullable()->after('id_document'); // Additional valid ID for logistics
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'sec_registration_no',
                'contact_first_name',
                'contact_last_name',
                'contact_position',
                'service_areas',
                'valid_id',
            ]);
        });
    }
};
