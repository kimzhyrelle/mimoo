<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rider_verification_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->nullable()->constrained('riders')->nullOnDelete();
            $table->foreignId('rider_application_id')->nullable()->constrained('rider_applications')->nullOnDelete();
            $table->string('action', 50);
            $table->text('reason')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_verification_audits');
    }
};
