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
        Schema::table('tbl_invoices', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->default(0)->after('remaining_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_invoices', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
