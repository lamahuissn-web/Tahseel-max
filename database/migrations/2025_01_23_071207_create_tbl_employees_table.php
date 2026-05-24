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
        Schema::create('tbl_employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_code',100)->nullable();
            $table->string('first_name', 150);
            $table->string('last_name', 150);
            $table->string('email')->unique();
            $table->string('national_id')->nullable();
            $table->enum('religion', ['muslim', 'mese7y'])->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_num')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('material_status', ['single', 'married', 'divorced'])->nullable();
            $table->string('position')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('hire_date')->default(\Carbon\Carbon::now()->toDateString())->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('profile_picture')->nullable();
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
        Schema::dropIfExists('tbl_employees');
    }
};
