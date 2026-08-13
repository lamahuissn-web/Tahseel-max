<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class Sas4MigrationBoundaryTest extends TestCase
{
    public function test_admin_status_allows_only_client_ids_without_key_order_assumption(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../app/Http/Controllers/Admin/ClientController.php');

        $this->assertStringContainsString("array_diff(array_keys(\$input), ['client_ids']) === []", $controller);
        $this->assertStringNotContainsString("array_keys(\$input) === ['client_ids']", $controller);
    }

    public function test_migrated_controllers_and_command_do_not_use_raw_transport(): void
    {
        $paths = [
            __DIR__ . '/../../app/Http/Controllers/Admin/ClientController.php',
            __DIR__ . '/../../app/Http/Controllers/Api/ClientSasStatusController.php',
            __DIR__ . '/../../app/Console/Commands/Sas4AutoMatch.php',
        ];

        foreach ($paths as $path) {
            $this->assertStringNotContainsString('Sas4ApiService', file_get_contents($path), $path);
        }
    }
}
