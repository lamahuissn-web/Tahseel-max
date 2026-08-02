<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_payment_operations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->uuid('idempotency_key')->unique();
            $table->char('request_hash', 64);
            $table->unsignedBigInteger('invoice_id')->index();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('collector_id')->index();
            $table->unsignedBigInteger('account_id')->index();
            $table->decimal('expected_remaining', 10, 2);
            $table->decimal('amount', 10, 2)->nullable();
            $table->unsignedBigInteger('revenue_id')->nullable()->unique();
            $table->unsignedBigInteger('financial_transaction_id')->nullable()->unique();
            $table->string('status', 20)->default('processing')->index();
            $table->timestamp('received_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_payment_operations');
    }
};
