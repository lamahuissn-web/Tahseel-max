<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Feature 021 — satellite customers are not part of the bonus program.
 * Stores the eligible client types as an admin-editable setting.
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('app_config')->where('key', 'bonus_client_types')->exists();

        if (! $exists) {
            DB::table('app_config')->insert([
                'key'        => 'bonus_client_types',
                'value'      => 'internet',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('app_config')->where('key', 'bonus_client_types')->delete();
    }
};
