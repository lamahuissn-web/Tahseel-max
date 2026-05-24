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
        Schema::table('tbl_clients', function (Blueprint $table) {
            $table->string('sas_username')->nullable()->after('phone')->comment('SAS 4 internet username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_clients', function (Blueprint $table) {
            $table->dropColumn('sas_username');
        });
    }
};
