<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `whatsapp_message_logs` MODIFY COLUMN `status` ENUM('pending','sending','sent','failed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE `whatsapp_message_logs` SET `status` = 'failed' WHERE `status` IN ('pending','sending')");
        DB::statement("ALTER TABLE `whatsapp_message_logs` MODIFY COLUMN `status` ENUM('sent','failed') NOT NULL");
    }
};
