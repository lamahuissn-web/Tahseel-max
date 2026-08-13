<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_message_logs', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('id')->constrained('whatsapp_batches')->nullOnDelete();
            $table->uuid('delivery_token')->nullable()->after('batch_id')->index();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `whatsapp_message_logs` MODIFY COLUMN `status` ENUM('pending','sending','sent','failed','cancelled') NOT NULL DEFAULT 'pending'");
        } elseif (DB::getDriverName() === 'sqlite') {
            $this->changeSqliteStatusConstraint(['pending', 'sending', 'sent', 'failed', 'cancelled'], 'pending');
            $this->addSqliteBatchForeignKey();
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE `whatsapp_message_logs` SET `status` = 'failed' WHERE `status` = 'cancelled'");
            DB::statement("ALTER TABLE `whatsapp_message_logs` MODIFY COLUMN `status` ENUM('pending','sending','sent','failed') NOT NULL DEFAULT 'pending'");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::table('whatsapp_message_logs')->where('status', 'cancelled')->update(['status' => 'failed']);
            $this->changeSqliteStatusConstraint(['pending', 'sending', 'sent', 'failed'], 'pending');
            $this->removeSqliteBatchForeignKey();
            Schema::table('whatsapp_message_logs', function (Blueprint $table) {
                $table->dropIndex(['delivery_token']);
            });
        }

        Schema::table('whatsapp_message_logs', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->dropColumn(['batch_id', 'delivery_token']);

                return;
            }

            $table->dropConstrainedForeignId('batch_id');
            $table->dropColumn('delivery_token');
        });
    }

    private function changeSqliteStatusConstraint(array $statuses, string $default): void
    {
        $row = DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'whatsapp_message_logs'"
        );
        $allowed = implode(', ', array_map(fn (string $status): string => "'{$status}'", $statuses));
        $replacement = sprintf(
            '"status" varchar check ("status" in (%s)) not null default \'%s\'',
            $allowed,
            $default
        );
        $sql = preg_replace(
            '/"status" varchar check \("status" in \([^)]*\)\) not null(?: default \'[^\']*\')?/i',
            $replacement,
            $row?->sql ?? '',
            1,
            $count
        );

        if ($count !== 1 || ! is_string($sql)) {
            throw new RuntimeException('Unable to locate SQLite whatsapp_message_logs status constraint.');
        }

        $this->replaceSqliteTableDefinition($sql);
    }

    private function addSqliteBatchForeignKey(): void
    {
        $sql = $this->sqliteTableDefinition();
        if (str_contains($sql, 'foreign key("batch_id")')) {
            return;
        }

        $sql = preg_replace(
            '/\)\s*$/',
            ', foreign key("batch_id") references "whatsapp_batches"("id") on delete set null)',
            $sql,
            1,
            $count
        );
        if ($count !== 1 || ! is_string($sql)) {
            throw new RuntimeException('Unable to add SQLite whatsapp batch foreign key.');
        }
        $this->replaceSqliteTableDefinition($sql);
    }

    private function removeSqliteBatchForeignKey(): void
    {
        $sql = preg_replace(
            '/, foreign key\("batch_id"\) references "whatsapp_batches"\("id"\) on delete set null/i',
            '',
            $this->sqliteTableDefinition(),
            1,
            $count
        );
        if ($count !== 1 || ! is_string($sql)) {
            throw new RuntimeException('Unable to remove SQLite whatsapp batch foreign key.');
        }
        $this->replaceSqliteTableDefinition($sql);
    }

    private function sqliteTableDefinition(): string
    {
        return (string) (DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'whatsapp_message_logs'"
        )?->sql ?? '');
    }

    private function replaceSqliteTableDefinition(string $sql): void
    {
        $schemaVersion = (int) (DB::selectOne('PRAGMA schema_version')->schema_version ?? 0);
        DB::statement('PRAGMA writable_schema = ON');
        try {
            DB::update(
                "UPDATE sqlite_master SET sql = ? WHERE type = 'table' AND name = 'whatsapp_message_logs'",
                [$sql]
            );
            DB::statement('PRAGMA schema_version = '.($schemaVersion + 1));
        } finally {
            DB::statement('PRAGMA writable_schema = OFF');
        }
    }
};
