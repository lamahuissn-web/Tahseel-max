<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Pool Name in MikroTik');
            $table->string('speed', 50)->nullable()->comment('e.g. 20M/20M');
            $table->integer('simultaneous_use')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_profiles');
    }
};
