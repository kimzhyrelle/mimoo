<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('region')->nullable();
            $table->timestamps();
        });

        DB::table('companies')->insert([
            ['id' => 1, 'code' => 'LOG-001', 'name' => 'Bilis Hatid Express Inc.', 'region' => 'NCR and CALABARZON', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'code' => 'LOG-002', 'name' => 'Agila Cargo Movers Corp.', 'region' => 'Davao', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->default(1)->index();
            $table->foreignId('parent_id')->nullable()->index();
            $table->enum('type', ['branch', 'hub', 'warehouse'])->index();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach (['areas', 'riders', 'parcels', 'rider_applications', 'activities', 'conversations', 'branch_checklists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->default(1)->index();
            });

            DB::table($tableName)->whereNull('company_id')->update(['company_id' => 1]);
        }

        Schema::table('riders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->index();
        });

        Schema::table('parcels', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('order_id')->nullable()->index();
        });

        Schema::table('parcel_status_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->default(1)->index();
        });

        DB::table('branches')->insert([
            ['id' => 1, 'company_id' => 1, 'parent_id' => null, 'type' => 'branch', 'code' => 'BR-001', 'name' => 'Bilis Hatid NCR Branch', 'address' => 'Quezon City, Metro Manila', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'company_id' => 1, 'parent_id' => 1, 'type' => 'hub', 'code' => 'HUB-001', 'name' => 'Bilis Hatid Central Hub', 'address' => 'Santa Cruz, Laguna', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'company_id' => 1, 'parent_id' => 1, 'type' => 'warehouse', 'code' => 'WH-001', 'name' => 'Bilis Hatid CALABARZON Warehouse', 'address' => 'Calamba, Laguna', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'company_id' => 2, 'parent_id' => null, 'type' => 'branch', 'code' => 'BR-002', 'name' => 'Agila Cargo Davao Branch', 'address' => 'Davao City', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'company_id' => 2, 'parent_id' => 4, 'type' => 'hub', 'code' => 'HUB-002', 'name' => 'Agila Cargo Davao Hub', 'address' => 'Davao City', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('riders')->whereNull('branch_id')->update(['branch_id' => 2]);
        DB::table('parcels')->whereNull('branch_id')->update(['branch_id' => 2]);
        DB::table('parcels')->whereNull('company_id')->update(['company_id' => 1]);
    }

    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'order_id']);
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });

        Schema::table('parcel_status_logs', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });

        foreach (['areas', 'riders', 'parcels', 'rider_applications', 'activities', 'conversations', 'branch_checklists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        Schema::dropIfExists('branches');
        Schema::dropIfExists('companies');
    }
};
