<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Replaces DatabaseTransactions for WhatsApp feature tests.
 *
 * WHY (post-incident 2026-08-21): these tests switch the default connection to the
 * real MySQL DB (tahseel_new) inside setUp() via putenv/config + DB::purge/
 * reconnect. Laravel's DatabaseTransactions trait starts its transaction in
 * parent::setUp() BEFORE that purge/reconnect, so reconnecting destroys the active
 * transaction and seeded rows were never rolled back (they leaked into the real
 * dev DB — 49 test clients were left behind).
 *
 * Instead: begin an explicit transaction AFTER the connection switch, and always
 * roll it back on tearDown (even on failure). Combined with the global
 * Queue::fake(['whatsapp_database']) in tests/TestCase.php, no seeded data persists
 * and no real WhatsApp job can be dispatched.
 *
 * Usage in a test:
 *   use WrapsMysqlTransaction;
 *   // in setUp(), AFTER DB::purge/reconnect:
 *   $this->beginTahseelTransaction();
 *   // in your existing tearDown() (Mockery::close etc.):
 *   $this->rollbackTahseelTransaction();
 */
trait WrapsMysqlTransaction
{
    protected function beginTahseelTransaction(): void
    {
        DB::beginTransaction();
    }

    protected function rollbackTahseelTransaction(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    }
}