<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 009 test database bootstrap (B3 release blocker).
 *
 * SQLite (:memory:):
 *   Builds the minimal schema required by the Feature 009 suites directly
 *   with Schema::create — the legacy migrations are MySQL-oriented (raw
 *   ENUM ALTERs / doctrine/dbal) and are never run here. The in-memory
 *   database is brand new per test application, so the schema is created in
 *   every test deterministically; no static flags, no cross-class state.
 *
 * MySQL (dedicated test database only):
 *   Keeps the production-DB safety guard — destructive setup refuses to run
 *   unless the configured database name ends with `_test` — and preserves
 *   the migrate:fresh-once-per-class + per-test transaction pattern used by
 *   the rest of the suite.
 */
trait Feature009Database
{
    private static bool $feature009MysqlMigrated = false;

    /**
     * NOTE: method names deliberately avoid the `setUp*` / `tearDown*`
     * prefixes matching this trait's basename — Laravel's setUpTraits()
     * auto-invokes `setUp{BasenameOfTrait}` and registers
     * `tearDown{BasenameOfTrait}`, which would double-run this bootstrap.
     */
    protected function bootstrapFeature009Database(): void
    {
        if ($this->feature009UsesSqlite()) {
            $this->createFeature009SqliteSchema();

            return;
        }

        $database = (string) config('database.connections.'.config('database.default').'.database');
        if (! str_contains($database, '_test')) {
            throw new \RuntimeException(
                'Refusing destructive test setup outside a dedicated _test database (got: '.$database.').'
            );
        }

        if (! self::$feature009MysqlMigrated) {
            Artisan::call('migrate:fresh', ['--force' => true]);
            self::$feature009MysqlMigrated = true;
        }

        DB::beginTransaction();
    }

    protected function teardownFeature009Database(): void
    {
        if (! $this->feature009UsesSqlite()) {
            DB::rollBack();
        }
    }

    private function feature009UsesSqlite(): bool
    {
        return config('database.default') === 'sqlite';
    }

    /**
     * Minimal schema for the Feature 009 suites only: admins (JWT auth),
     * spatie roles/permissions pivots, app_config (currency), and the three
     * business tables the clients list query path touches.
     */
    private function createFeature009SqliteSchema(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('real_password')->nullable();
            $table->string('status')->nullable()->default('1');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('app_config', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('title')->nullable(); // json, stored as text
            $table->string('guard_name')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('guard_name')->nullable();
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        Schema::create('tbl_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('tbl_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address1')->nullable();
            $table->string('client_type')->nullable()->default('internet');
            $table->string('user')->nullable();
            $table->string('box_switch')->nullable();
            $table->string('sas_username')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->date('subscription_date')->nullable();
            $table->date('start_date')->nullable();
            $table->string('is_active')->nullable()->default('1');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('tbl_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->decimal('remaining_amount', 10, 2)->nullable();
            $table->date('enshaa_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->nullable();
            $table->string('invoice_type')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
