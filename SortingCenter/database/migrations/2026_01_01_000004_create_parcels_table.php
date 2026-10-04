<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_no')->unique(); // e.g. "#1004"
            $table->string('address');
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rider_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'awaiting_sort',
                'sorted',
                'assigned',
                'out_for_delivery',
                'delivered',
                'failed',
            ])->default('awaiting_sort');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('sorted_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
