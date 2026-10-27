<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('otp_hash')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_locked_until')->nullable();
            $table->timestamp('otp_sent_at')->nullable();
            $table->decimal('cod_balance', 12, 2)->default(0);
            $table->boolean('or_cr_verified')->default(false);
            $table->boolean('documents_uploaded')->default(false);
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
        });

        Schema::table('parcels', function (Blueprint $table) {
            $table->unsignedTinyInteger('delivery_attempts')->default(0);
            $table->string('failure_reason')->nullable();
            $table->json('failure_reasons')->nullable();
            $table->string('delivery_otp', 6)->nullable();
            $table->timestamp('delivery_otp_verified_at')->nullable();
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->decimal('cod_collected', 12, 2)->default(0);
            $table->decimal('cod_remitted', 12, 2)->default(0);
            $table->boolean('is_prepaid')->default(false);
            $table->unsignedBigInteger('shipping_zone_id')->nullable()->index();
            $table->unsignedBigInteger('shipping_rate_id')->nullable()->index();
            $table->decimal('chargeable_weight', 8, 2)->nullable();
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->timestamp('lost_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->timestamp('damaged_at')->nullable();
            $table->string('damage_photo_path')->nullable();
            $table->text('damaged_reason')->nullable();
            $table->string('return_reason')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('seller_received_at')->nullable();
            $table->timestamp('stock_restored_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('seller_earnings', 12, 2)->default(0);
            $table->decimal('pickup_fee', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE parcels MODIFY status ENUM('dropped_off','awaiting_sort','received','sorted','assigned','picked_up','at_hub','out_for_delivery','delivered','failed','failed_delivery','return_to_sender','returned','returned_to_sender','lost','damaged','ready_for_pickup') NOT NULL DEFAULT 'awaiting_sort'");
            DB::statement("ALTER TABLE riders MODIFY status ENUM('available','busy','offline','suspended') NOT NULL DEFAULT 'offline'");
        }

        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->foreignId('parcel_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('actor_role', 30)->default('logistics');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
            $table->index(['parcel_id', 'created_at']);
        });

        Schema::create('shipment_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->foreignId('parcel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->string('type', 50);
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['role', 'read_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('subject_type', 120);
            $table->unsignedBigInteger('subject_id');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->string('name');
            $table->string('region')->nullable();
            $table->json('areas')->nullable();
            $table->boolean('is_serviceable')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('base_fee', 12, 2);
            $table->decimal('per_kg_fee', 12, 2)->default(0);
            $table->decimal('min_weight_kg', 8, 2)->default(0);
            $table->decimal('max_weight_kg', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('effective_from')->nullable();
            $table->timestamps();
        });

        Schema::create('cod_remittances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('collected', 12, 2)->default(0);
            $table->decimal('difference', 12, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('reference')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('issue_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1)->index();
            $table->string('ticket_no', 20)->unique();
            $table->string('category', 40);
            $table->string('related_reference')->nullable();
            $table->string('priority', 10)->default('Normal');
            $table->string('subject', 120);
            $table->text('description');
            $table->string('status', 20)->default('Open')->index();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('issue_report_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_report_id')->constrained()->cascadeOnDelete();
            $table->string('author_role', 20)->default('logistics');
            $table->text('body');
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->timestamps();
        });

        Schema::create('issue_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_report_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 60);
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_report_attachments');
        Schema::dropIfExists('issue_report_replies');
        Schema::dropIfExists('issue_reports');
        Schema::dropIfExists('cod_remittances');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('shipment_notifications');
        Schema::dropIfExists('shipment_events');

        Schema::table('parcels', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_attempts', 'failure_reason', 'failure_reasons', 'delivery_otp',
                'delivery_otp_verified_at', 'cod_amount', 'cod_collected', 'cod_remitted',
                'is_prepaid', 'shipping_zone_id', 'shipping_rate_id', 'chargeable_weight',
                'shipping_fee', 'lost_at', 'lost_reason', 'damaged_at', 'damage_photo_path',
                'damaged_reason', 'return_reason', 'returned_at', 'seller_received_at',
                'stock_restored_at', 'refunded_at', 'seller_earnings', 'pickup_fee', 'delivery_fee',
            ]);
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn([
                'phone_verified_at', 'otp_hash', 'otp_expires_at', 'otp_attempts',
                'otp_locked_until', 'otp_sent_at', 'cod_balance', 'or_cr_verified',
                'documents_uploaded', 'suspended_at', 'suspension_reason',
            ]);
        });
    }
};
