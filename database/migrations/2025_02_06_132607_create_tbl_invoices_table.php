<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->integer('client_id');
            $table->integer('subscription_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            $table->date('enshaa_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['paid', 'unpaid', 'partial'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->enum('invoice_type', ['subscription', 'service'])->default('subscription');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_invoices');
    }
};
