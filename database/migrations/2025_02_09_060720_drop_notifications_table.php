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
        Schema::table('notifications', function (Blueprint $table) {
            Schema::dropIfExists('notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->text('details')->nullable();
            $table->enum('send_to', ['member', 'trainer', 'all'])->default('member')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
