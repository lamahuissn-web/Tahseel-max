<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Feature 005 benchmark justification (dedicated MariaDB test DB,
     * 1,400 clients / 60,000 invoices, five-run median):
     *
     *  - idx_tbl_invoices_status_due (status, deleted_at, due_date, id):
     *    the unpaid-invoices page query plan moves from type=ALL (full table
     *    scan, ~70.8 ms) to type=range with "Using index condition"
     *    (~60.6 ms) and stops scanning the whole table as invoice volume
     *    grows (~18k rows/year for 1,500 clients).
     *  - idx_tbl_invoices_client_id (client_id): not needed by the LEFT JOIN
     *    above (eq_ref on clients PRIMARY), but the existing
     *    /clients/{id}/invoices lookup filters WHERE client_id = ? with no
     *    index today: type=ALL ~31.3 ms -> type=ref ~2.5 ms (42 rows) at 60k.
     *
     * The filtered COUNT query cost (~38 ms at 60k) is unchanged by either
     * index — MariaDB plans it as a single scan; that residual cost is
     * acceptable and no index shape improved it (tested three shapes).
     *
     * Non-destructive, fully reversible. This migration is NOT run against
     * production; it is exercised only on dedicated test databases.
     */
    public function up(): void
    {
        Schema::table('tbl_invoices', function (Blueprint $table) {
            $table->index(['status', 'deleted_at', 'due_date', 'id'], 'idx_tbl_invoices_status_due');
            $table->index(['client_id'], 'idx_tbl_invoices_client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_tbl_invoices_status_due');
            $table->dropIndex('idx_tbl_invoices_client_id');
        });
    }
};
