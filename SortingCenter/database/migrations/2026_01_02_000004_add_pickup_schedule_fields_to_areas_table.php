<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->string('pickup_window')->nullable()->after('code');  // e.g. "9:00 AM – 11:00 AM"
            $table->string('cutoff_time')->nullable()->after('pickup_window');
            $table->string('pickup_status')->nullable()->after('cutoff_time'); // e.g. "On schedule"
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn(['pickup_window', 'cutoff_time', 'pickup_status']);
        });
    }
};
