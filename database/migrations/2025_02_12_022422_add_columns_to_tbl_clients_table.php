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
            $table->enum('client_type', ['internet', 'satellite'])->after('phone')->default('internet');
            $table->string('user')->nullable()->after('client_type');
            $table->string('box_switch')->nullable()->after('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_clients', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'user', 'box_switch']);
        });
    }
};
