<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'radius';

    public function up(): void
    {
        Schema::connection('radius')->table('nas', function (Blueprint $table) {
            $table->integer('coa_port')->nullable()->default(3799)->after('ports');
            $table->boolean('enabled')->default(true)->after('coa_port');
            $table->boolean('ip_accounting')->default(false)->after('enabled');
            $table->boolean('ping_monitor')->default(true)->after('ip_accounting');
            $table->string('pool_name')->nullable()->after('ping_monitor');
            $table->string('mikrotik_version')->nullable()->after('pool_name');
            $table->string('site')->nullable()->after('mikrotik_version');
            $table->integer('http_port')->nullable()->default(80)->after('site');
            $table->string('ssh_username')->nullable()->after('http_port');
            $table->string('ssh_password')->nullable()->after('ssh_username');
            $table->integer('ssh_port')->nullable()->default(22)->after('ssh_password');
        });
    }

    public function down(): void
    {
        Schema::connection('radius')->table('nas', function (Blueprint $table) {
            $table->dropColumn([
                'coa_port', 'enabled', 'ip_accounting', 'ping_monitor',
                'pool_name', 'mikrotik_version', 'site', 'http_port',
                'ssh_username', 'ssh_password', 'ssh_port'
            ]);
        });
    }
};
