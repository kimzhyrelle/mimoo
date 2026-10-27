<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hubName = config('sorting.hub_name', 'Laguna Hub (Santa Cruz)');

        DB::table('hub_profiles')
            ->where('name', 'Laguna Hub')
            ->update(['name' => $hubName, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('hub_profiles')
            ->where('name', config('sorting.hub_name', 'Laguna Hub (Santa Cruz)'))
            ->update(['name' => 'Laguna Hub', 'updated_at' => now()]);
    }
};
