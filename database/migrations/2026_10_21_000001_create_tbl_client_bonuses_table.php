<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 021 — loyalty bonus ledger.
 *
 * Only the *earned bonus* is stored. Progress (x/24) is always computed from
 * invoices + revenues so it can never drift after a restore or a payment edit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tbl_client_bonuses')) {
            Schema::create('tbl_client_bonuses', function (Blueprint $table) {
                $table->id();
                $table->integer('client_id')->index();
                $table->date('earned_at');
                $table->enum('status', ['pending', 'approved', 'redeemed', 'cancelled'])->default('pending');
                $table->enum('source', ['auto', 'manual'])->default('auto');
                $table->date('cycle_start')->nullable();
                $table->unsignedSmallInteger('cycle_months')->default(24);
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->unsignedBigInteger('redeemed_invoice_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['client_id', 'status'], 'client_bonuses_client_status_idx');
            });
        }

        $now = now();
        $defaults = [
            'bonus_enabled'               => '0',
            'bonus_months_required'       => '24',
            'bonus_grace_days'            => '0',
            'bonus_catchup_per_cycle'     => '1',
            'bonus_hard_reset_after_months' => '2',
            'bonus_spiral_after_months'   => '2',
            'bonus_count_start_date'      => '2026-01-01',
            'bonus_require_zero_balance'  => '1',
            'bonus_auto_apply_on_approve' => '1',
            'bonus_invoice_value'         => '0',
            'bonus_invoice_notes'         => 'شهر مجاني - برنامج البونص',
        ];

        foreach ($defaults as $key => $value) {
            $exists = DB::table('app_config')->where('key', $key)->exists();
            if (! $exists) {
                DB::table('app_config')->insert([
                    'key'        => $key,
                    'value'      => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_client_bonuses');

        DB::table('app_config')->where('key', 'like', 'bonus\_%')->delete();
    }
};
