<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Feature 021 (loyalty bonus) test database bootstrap.
 *
 * These suites need a real MySQL test database:
 *   - BonusLedger::nextInvoiceNumber() uses `invoice_number REGEXP '^[0-9]+$'`
 *     and an ORDER BY CAST(... AS UNSIGNED), and the spiral query uses CURDATE();
 *     SQLite has neither REGEXP nor CURDATE, so the production SQL cannot run there.
 *   - Changing that SQL only to satisfy SQLite would touch live billing code for
 *     the sake of the test harness. Not worth it.
 *
 * So instead of the old behaviour — throw a guard exception that made
 * `php artisan test` report 19 FAILURES before a single assertion ran, while the
 * same code was green under MySQL — the suites now SKIP with an actionable
 * message when they are pointed at SQLite. A skipped suite is honest; a red one
 * teaches everybody to ignore the suite.
 *
 * Run them with:  scripts/test-bonus.sh
 *   (= DB_CONNECTION=mysql DB_DATABASE=<name>_test php vendor/bin/phpunit --filter Bonus)
 */
trait BonusDatabase
{
    private static bool $bonusMysqlMigrated = false;

    /**
     * NOTE: the method name deliberately avoids the `setUp*` prefix matching this
     * trait's basename — Laravel's setUpTraits() auto-invokes `setUpBonusDatabase`.
     */
    protected function bootstrapBonusDatabase(): void
    {
        if (config('database.default') !== 'mysql') {
            $this->markTestSkipped(
                'The loyalty-bonus suites need MySQL (REGEXP / CURDATE in the bonus SQL). '
                .'Run scripts/test-bonus.sh, or set DB_CONNECTION=mysql DB_DATABASE=…_test.'
            );
        }

        $database = (string) config('database.connections.mysql.database');

        if (! str_contains($database, '_test')) {
            throw new \RuntimeException(
                'Refusing destructive test setup outside a dedicated _test database (got: '.$database.').'
            );
        }

        if (! self::$bonusMysqlMigrated) {
            Artisan::call('migrate:fresh', ['--force' => true]);
            self::$bonusMysqlMigrated = true;
        }

        DB::beginTransaction();
    }

    protected function teardownBonusDatabase(): void
    {
        if (config('database.default') === 'mysql') {
            DB::rollBack();
        }
    }
}
