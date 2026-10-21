<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * How many months a used catch-up keeps the «استدراك» label on the customer.
 * Beyond that the label fades and the customer reads as منتظم, because the
 * status must describe today rather than the customer's history.
 */
class AddBonusCatchupVisibleMonthsSetting extends Migration
{
    private const DEFAULTS = [
        'bonus_catchup_visible_months' => '2',
    ];

    public function up(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            $exists = DB::table('app_config')->where('key', $key)->exists();
            if (! $exists) {
                DB::table('app_config')->insert([
                    'key'        => $key,
                    'value'      => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('app_config')->whereIn('key', array_keys(self::DEFAULTS))->delete();
    }
}
