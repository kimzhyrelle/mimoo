<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rider_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vehicle_type')->nullable();
            $table->string('plate_no')->nullable();
            $table->boolean('or_cr_verified')->default(false);
            $table->enum('license_status', ['verified', 'pending'])->default('pending');
            $table->date('applied_on');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_rider_id')->nullable()->constrained('riders')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_applications');
    }
};
