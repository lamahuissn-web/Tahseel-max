<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_subscriptions', function (Blueprint $table) {
            $table->string('radius_profile')->nullable()->after('price');
            $table->string('radius_speed')->nullable()->after('radius_profile');
        });

        Schema::table('tbl_clients', function (Blueprint $table) {
            $table->string('radius_profile')->nullable()->after('sas_username');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['radius_profile', 'radius_speed']);
        });

        Schema::table('tbl_clients', function (Blueprint $table) {
            $table->dropColumn('radius_profile');
        });
    }
};
