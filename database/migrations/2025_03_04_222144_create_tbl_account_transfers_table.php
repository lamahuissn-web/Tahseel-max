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
        Schema::create('tbl_account_transfers', function (Blueprint $table) {
            $table->id();
            $table->integer('from_account');
            $table->integer('to_account');
            $table->decimal('amount', 10, 2);
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_account_transfers');
    }
};
