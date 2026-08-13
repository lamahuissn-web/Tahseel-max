<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppBatchMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.url' => 'http://localhost',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
        });

        $this->migration('2026_05_26_000000_create_whatsapp_message_logs_table.php')->up();
        $this->migration('2026_08_13_000001_create_whatsapp_batches_table.php')->up();
    }

    public function test_sqlite_migration_expands_original_status_constraint_and_preserves_schema(): void
    {
        DB::table('whatsapp_message_logs')->insert([
            'client_id' => 10,
            'invoice_id' => 20,
            'phone' => '96170000000',
            'message' => 'existing',
            'status' => 'sent',
        ]);

        $migration = $this->migration('2026_08_13_000002_add_batch_control_to_whatsapp_message_logs.php');
        $migration->up();

        foreach (['pending', 'sending', 'sent', 'failed', 'cancelled'] as $status) {
            DB::table('whatsapp_message_logs')->insert([
                'phone' => '9617000000'.DB::table('whatsapp_message_logs')->count(),
                'message' => $status,
                'status' => $status,
            ]);
        }

        $this->assertSame('existing', DB::table('whatsapp_message_logs')->where('client_id', 10)->value('message'));
        $this->assertTrue(Schema::hasColumns('whatsapp_message_logs', [
            'client_id', 'invoice_id', 'phone', 'message', 'status', 'error',
            'batch_id', 'delivery_token', 'created_at', 'updated_at',
        ]));
        $this->assertTrue($this->hasIndex('whatsapp_message_logs', 'whatsapp_message_logs_status_index'));
        $this->assertTrue($this->hasIndex('whatsapp_message_logs', 'whatsapp_message_logs_delivery_token_index'));
        $this->assertTrue($this->hasForeignKey('whatsapp_message_logs', 'batch_id', 'whatsapp_batches'));
        $this->assertSame(
            ['cancelled', 'failed', 'pending', 'sending', 'sent'],
            DB::table('whatsapp_message_logs')->distinct()->orderBy('status')->pluck('status')->all()
        );
    }

    public function test_sqlite_rollback_normalizes_cancelled_and_restores_effective_pre_feature_constraint(): void
    {
        $migration = $this->migration('2026_08_13_000002_add_batch_control_to_whatsapp_message_logs.php');
        $migration->up();
        foreach (['pending', 'sending', 'sent', 'failed', 'cancelled'] as $status) {
            DB::table('whatsapp_message_logs')->insert([
                'phone' => '9617000000'.DB::table('whatsapp_message_logs')->count(),
                'message' => $status,
                'status' => $status,
            ]);
        }

        $migration->down();

        $this->assertFalse(Schema::hasColumn('whatsapp_message_logs', 'batch_id'));
        $this->assertFalse(Schema::hasColumn('whatsapp_message_logs', 'delivery_token'));
        $this->assertSame(2, DB::table('whatsapp_message_logs')->where('status', 'failed')->count());
        foreach (['pending', 'sending', 'sent', 'failed'] as $status) {
            DB::table('whatsapp_message_logs')->insert([
                'phone' => '9617111111'.DB::table('whatsapp_message_logs')->count(),
                'message' => 'rollback '.$status,
                'status' => $status,
            ]);
        }

        try {
            DB::table('whatsapp_message_logs')->insert([
                'phone' => '96179999999',
                'message' => 'cancelled rejected',
                'status' => 'cancelled',
            ]);
            $this->fail('Rollback must reject the feature-only cancelled status.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('CHECK constraint failed', $exception->getMessage());
        }
        $this->assertTrue($this->hasIndex('whatsapp_message_logs', 'whatsapp_message_logs_status_index'));
    }

    private function migration(string $filename): object
    {
        return require database_path('migrations/'.$filename);
    }

    private function hasIndex(string $table, string $name): bool
    {
        return collect(DB::select("PRAGMA index_list('{$table}')"))->contains(
            fn (object $index): bool => $index->name === $name
        );
    }

    private function hasForeignKey(string $table, string $column, string $target): bool
    {
        return collect(DB::select("PRAGMA foreign_key_list('{$table}')"))->contains(
            fn (object $key): bool => $key->from === $column && $key->table === $target
        );
    }
}
