<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatch_manifests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('manifest');
            $table->timestamps();
        });

        Schema::create('parcel_weigh_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->foreignId('parcel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('weight_kg', 8, 2);
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['parcel_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_weigh_ins');
        Schema::dropIfExists('dispatch_manifests');
    }
};
