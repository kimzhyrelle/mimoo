<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->timestamp('sla_due_at')->nullable()->index()->after('delivered_at');
            $table->unsignedTinyInteger('scan_count')->default(1)->after('sla_due_at');
            $table->timestamp('last_scanned_at')->nullable()->after('scan_count');
        });
    }

    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropIndex(['sla_due_at']);
            $table->dropColumn(['sla_due_at', 'scan_count', 'last_scanned_at']);
        });
    }
};
