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
            $table->softDeletes()->after('updated_at');
            $table->timestamp('archived_at')->nullable()->after('deleted_at');
            $table->foreignId('archived_by')->nullable()->after('archived_at')->constrained('users')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE parcels MODIFY status VARCHAR(50) NOT NULL DEFAULT 'dropped_off'");
        }

        Schema::table('riders', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
            $table->timestamp('archived_at')->nullable()->after('deleted_at');
            $table->foreignId('archived_by')->nullable()->after('archived_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['archived_by', 'archived_at', 'deleted_at']);
        });

        Schema::table('parcels', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['archived_by', 'archived_at', 'deleted_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE parcels MODIFY status ENUM(
                'dropped_off','awaiting_sort','sorted','assigned','out_for_delivery','delivered','failed'
            ) NOT NULL DEFAULT 'dropped_off'");
        }
    }
};
