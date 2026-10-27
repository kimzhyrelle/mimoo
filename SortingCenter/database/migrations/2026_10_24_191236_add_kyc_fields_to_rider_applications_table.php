<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rider_applications', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('plate_no');
            $table->string('kyc_status', 20)->default('pending')->after('phone');
            $table->foreignId('verified_by')->nullable()->after('kyc_status')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejection_reason')->nullable()->after('verified_at');
            $table->json('checklist')->nullable()->after('rejection_reason');
            $table->date('or_cr_expires_on')->nullable()->after('checklist');
            $table->date('license_expires_on')->nullable()->after('or_cr_expires_on');
            $table->string('otp_hash')->nullable()->after('license_expires_on');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_hash');
            $table->unsignedTinyInteger('otp_attempts')->default(0)->after('otp_expires_at');
            $table->timestamp('phone_verified_at')->nullable()->after('otp_attempts');
            $table->index('kyc_status');
        });
    }

    public function down(): void
    {
        Schema::table('rider_applications', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropIndex(['kyc_status']);
            $table->dropColumn([
                'phone',
                'kyc_status',
                'verified_by',
                'verified_at',
                'rejection_reason',
                'checklist',
                'or_cr_expires_on',
                'license_expires_on',
                'otp_hash',
                'otp_expires_at',
                'otp_attempts',
                'phone_verified_at',
            ]);
        });
    }
};
