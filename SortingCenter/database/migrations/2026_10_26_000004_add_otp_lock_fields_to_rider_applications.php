<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rider_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('rider_applications', 'otp_locked_until')) {
                $table->timestamp('otp_locked_until')->nullable()->after('otp_attempts');
            }
            if (! Schema::hasColumn('rider_applications', 'otp_sent_at')) {
                $table->timestamp('otp_sent_at')->nullable()->after('otp_locked_until');
            }
            if (! Schema::hasColumn('rider_applications', 'documents_uploaded')) {
                $table->boolean('documents_uploaded')->default(false)->after('checklist');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rider_applications', function (Blueprint $table) {
            foreach (['otp_locked_until', 'otp_sent_at', 'documents_uploaded'] as $column) {
                if (Schema::hasColumn('rider_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
