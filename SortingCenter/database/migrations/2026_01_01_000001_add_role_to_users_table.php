<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'staff' = Sorting Center staff, 'rider' = delivery rider
            $table->enum('role', ['staff', 'rider'])->default('staff')->after('email');
            $table->string('initials', 4)->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'initials']);
        });
    }
};
