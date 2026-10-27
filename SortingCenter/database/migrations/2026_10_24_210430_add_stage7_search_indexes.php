<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->index('seller_name');
            $table->index('buyer_name');
            $table->index('status');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->index('name');
            $table->index('phone');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->index('read_at');
        });

        Schema::table('rider_applications', function (Blueprint $table) {
            $table->index(['status', 'kyc_status']);
        });
    }

    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropIndex(['seller_name']);
            $table->dropIndex(['buyer_name']);
            $table->dropIndex(['status']);
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['phone']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['read_at']);
        });

        Schema::table('rider_applications', function (Blueprint $table) {
            $table->dropIndex(['status', 'kyc_status']);
        });
    }
};
