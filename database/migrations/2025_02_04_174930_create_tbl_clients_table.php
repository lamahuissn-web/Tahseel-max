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
        Schema::create('tbl_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_code')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('image')->nullable();
            $table->enum('is_active',[1,0])->default(1)->nullable();
            $table->integer('subscription_id');
            $table->decimal('price', 8, 2)->default(0);
            $table->date('subscription_date');
            $table->date('start_date');
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_clients');
    }
};
