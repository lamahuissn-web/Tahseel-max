<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 021 — the admin decides, the system never issues on its own.
 *
 * Adds the `postponed` status (the right is kept, the decision is delayed)
 * and removes the auto-apply-on-approve switch from app_config.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tbl_client_bonuses MODIFY status ENUM('pending','approved','postponed','redeemed','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('tbl_client_bonuses', function ($table) {
            $table->timestamp('postponed_at')->nullable()->after('approved_at');
            $table->unsignedBigInteger('postponed_by')->nullable()->after('postponed_at');
            $table->string('postpone_reason', 255)->nullable()->after('postponed_by');
            $table->string('postpone_target_month', 7)->nullable()->after('postpone_reason');
        });

        DB::table('app_config')->where('key', 'bonus_auto_apply_on_approve')->delete();
    }

    public function down(): void
    {
        Schema::table('tbl_client_bonuses', function ($table) {
            $table->dropColumn(['postponed_at', 'postponed_by', 'postpone_reason', 'postpone_target_month']);
        });

        DB::statement("ALTER TABLE tbl_client_bonuses MODIFY status ENUM('pending','approved','redeemed','cancelled') NOT NULL DEFAULT 'pending'");

        DB::table('app_config')->insert([
            'key'        => 'bonus_auto_apply_on_approve',
            'value'      => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
