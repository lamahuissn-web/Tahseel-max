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
        Schema::table('tbl_accounts_settings', function (Blueprint $table) {
            $table->foreignId('accountant_account_id')->nullable()->default(10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_accounts_settings', function (Blueprint $table) {
            $table->dropColumn('accountant_account_id');
        });
    }
};
