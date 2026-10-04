<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->string('seller_name')->nullable()->after('address');
            $table->foreignId('dropped_off_by_rider_id')->nullable()->after('rider_id')->constrained('riders')->nullOnDelete();
            $table->enum('carrier', ['jnt', 'lbc', 'lalamove'])->default('jnt')->after('status');
            $table->boolean('scanned_undelivered')->default(false)->after('carrier');
            $table->string('eta_note')->nullable()->after('scanned_undelivered');
            $table->timestamp('buyer_confirmed_at')->nullable()->after('delivered_at');
        });

        // Add the "dropped_off" stage before "awaiting_sort" (the first station in
        // Incoming Parcels, before a parcel is scanned). Raw ALTER since Laravel's
        // schema builder can't append an enum value in place.
        DB::statement("ALTER TABLE parcels MODIFY status ENUM(
            'dropped_off','awaiting_sort','sorted','assigned','out_for_delivery','delivered','failed'
        ) NOT NULL DEFAULT 'dropped_off'");
    }

    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dropped_off_by_rider_id');
            $table->dropColumn(['seller_name', 'carrier', 'scanned_undelivered', 'eta_note', 'buyer_confirmed_at']);
        });

        DB::statement("ALTER TABLE parcels MODIFY status ENUM(
            'awaiting_sort','sorted','assigned','out_for_delivery','delivered','failed'
        ) NOT NULL DEFAULT 'awaiting_sort'");
    }
};
