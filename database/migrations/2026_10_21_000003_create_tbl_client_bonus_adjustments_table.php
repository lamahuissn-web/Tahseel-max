<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 021 — manual point adjustments and cycle resets.
 *
 * Progress is computed, so the only way an admin can bend it is with an
 * auditable row here:
 *  - type `points` : adds (or removes) months from the current cycle
 *  - type `reset`  : moves the cycle origin forward, dropping earlier credits
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tbl_client_bonus_adjustments')) {
            Schema::create('tbl_client_bonus_adjustments', function (Blueprint $table) {
                $table->id();
                $table->integer('client_id')->index();
                $table->enum('type', ['points', 'reset'])->default('points');
                $table->integer('points')->default(0);
                $table->string('reason', 255)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['client_id', 'created_at'], 'bonus_adj_client_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_client_bonus_adjustments');
    }
};
