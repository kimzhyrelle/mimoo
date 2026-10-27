<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->index(['status', 'received_at']);
            $table->index(['status', 'assigned_at']);
            $table->index(['status', 'area_id']);
            $table->index(['status', 'rider_id', 'sla_due_at']);
            $table->index(['rider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropIndex(['status', 'received_at']);
            $table->dropIndex(['status', 'assigned_at']);
            $table->dropIndex(['status', 'area_id']);
            $table->dropIndex(['status', 'rider_id', 'sla_due_at']);
            $table->dropIndex(['rider_id', 'status']);
        });
    }
};
