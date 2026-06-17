<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_subscriptions', function (Blueprint $table) {
            $table->foreignId('profile_id')
                ->nullable()
                ->after('price')
                ->constrained('tbl_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });
    }
};
