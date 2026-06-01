<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("tbl_clients", function (Blueprint $table) {
            $table->string("radius_password")->nullable()->after("sas_username")->comment("RADIUS authentication password");
        });
    }

    public function down(): void
    {
        Schema::table("tbl_clients", function (Blueprint $table) {
            $table->dropColumn("radius_password");
        });
    }
};
