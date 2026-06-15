<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("tbl_clients", function (Blueprint $table) {
            $table->date("radius_stop_at")->nullable()->after("radius_password")->comment("Scheduled RADIUS disconnection date");
        });
    }

    public function down(): void
    {
        Schema::table("tbl_clients", function (Blueprint $table) {
            $table->dropColumn("radius_stop_at");
        });
    }
};
