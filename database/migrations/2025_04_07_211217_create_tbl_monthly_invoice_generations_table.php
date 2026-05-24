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
        Schema::create('tbl_monthly_invoice_generations', function (Blueprint $table) {
            $table->id();
            $table->string('year_month', 7);
            $table->dateTime('generated_at');
            $table->integer('invoices_created');
            $table->integer('generated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_monthly_invoice_generations');
    }
};
