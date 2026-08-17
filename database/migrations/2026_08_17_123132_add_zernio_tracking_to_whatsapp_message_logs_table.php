<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_message_logs', function (Blueprint $table) {
            $table->string('provider_message_id')->nullable()->after('status')
                ->comment('Zernio/OpenWA message ID (wamid) for webhook matching');
            $table->timestamp('delivered_at')->nullable()->after('provider_message_id')
                ->comment('When Zernio confirmed delivery via webhook');
            $table->index('provider_message_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_message_logs', function (Blueprint $table) {
            $table->dropIndex(['provider_message_id']);
            $table->dropColumn(['provider_message_id', 'delivered_at']);
        });
    }
};
