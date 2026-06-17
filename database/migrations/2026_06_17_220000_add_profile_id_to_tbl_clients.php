<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_clients', function (Blueprint $table) {
            // New FK-based profile reference
            $table->foreignId('profile_id')
                ->nullable()
                ->after('radius_profile')
                ->constrained('tbl_profiles')
                ->nullOnDelete();

            // Manual override: when admin manually changes client's profile
            $table->foreignId('override_profile_id')
                ->nullable()
                ->after('profile_id')
                ->constrained('tbl_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_clients', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
            $table->dropForeign(['override_profile_id']);
            $table->dropColumn('override_profile_id');
        });
    }
};
