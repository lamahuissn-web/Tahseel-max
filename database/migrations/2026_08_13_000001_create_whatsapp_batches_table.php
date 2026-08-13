<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('source', 50)->index();
            $table->string('title');
            $table->string('template_type', 50)->nullable();
            $table->enum('status', ['queued', 'running', 'completed', 'completed_with_errors', 'cancelling', 'cancelled'])->default('queued')->index();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_batches');
    }
};
