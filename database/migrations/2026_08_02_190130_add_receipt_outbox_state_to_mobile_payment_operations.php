<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_payment_operations', function (Blueprint $table) {
            $table->string('receipt_status', 20)->default('pending')->index()->after('response_payload');
            $table->unsignedInteger('receipt_attempts')->default(0)->after('receipt_status');
            $table->timestamp('receipt_last_attempt_at')->nullable()->after('receipt_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_payment_operations', function (Blueprint $table) {
            $table->dropIndex(['receipt_status']);
            $table->dropColumn(['receipt_status', 'receipt_attempts', 'receipt_last_attempt_at']);
        });
    }
};
