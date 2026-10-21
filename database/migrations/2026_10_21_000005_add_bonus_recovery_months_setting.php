<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A customer returns to «منتظم» only after N clean months (paid inside their own
 * month) following a break; before that he is «عائد للانتظام». Replaces the
 * earlier time-based catch-up label window.
 */
class AddBonusRecoveryMonthsSetting extends Migration
{
    public function up(): void
    {
        $exists = DB::table('app_config')->where('key', 'bonus_recovery_months')->exists();
        if (! $exists) {
            DB::table('app_config')->insert([
                'key'        => 'bonus_recovery_months',
                'value'      => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // The time-based label window is superseded by the recovery counter.
        DB::table('app_config')->where('key', 'bonus_catchup_visible_months')->delete();
    }

    public function down(): void
    {
        DB::table('app_config')->where('key', 'bonus_recovery_months')->delete();
    }
}
