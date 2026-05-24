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
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_name')->nullable();
            $table->string('user_image', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('gender', array('male', 'female'))->nullable();
            $table->enum('status', array('active', 'not-active'))->default('active');
            $table->string('extradata', 255)->nullable();
            $table->text('tokenNoti')->nullable();
            $table->enum('lang', array('ar', 'en'))->default('ar');

            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_name');
            $table->dropColumn('tokenNoti');
            $table->dropColumn('extradata');
            $table->dropColumn('status');
            $table->dropColumn('phone');
            $table->dropColumn('user_image');
            $table->dropColumn('gender');
            $table->dropColumn('lang');
            $table->dropSoftDeletes();
        });
        Schema::table('tbl_members', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
