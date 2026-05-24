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
        Schema::table('tbl_revenues', function (Blueprint $table) {
            $table->enum('status', ['paid', 'partial'])->default('paid')->after('received_at');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_revenues', function (Blueprint $table) {
            $table->dropColumn(['status', 'remaining_amount']);
        });
    }
};
