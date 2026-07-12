<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_message_logs', function (Blueprint $table) {
            // Add missing columns for WhatsApp Control Center features
            $table->string('client_name')->nullable()->after('client_id');
            $table->string('template_type', 50)->nullable()->after('message');
            $table->string('sent_by', 100)->nullable()->after('template_type');

            // Additional indexes for search & filter performance
            $table->index('client_name');
            $table->index('template_type');
            $table->index('sent_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_message_logs', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'template_type', 'sent_by']);
            $table->dropIndex(['client_name']);
            $table->dropIndex(['template_type']);
            $table->dropIndex(['sent_by']);
            $table->dropIndex(['created_at']);
        });
    }
};
