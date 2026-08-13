<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MobileAdminSasStatusCallerTest extends TestCase
{
    public function test_active_mobile_caller_posts_json_client_ids_and_maps_closed_statuses(): void
    {
        $view = file_get_contents(__DIR__ . '/../../resources/views/dashbord/mobile_view/clients.blade.php');
        $partial = file_get_contents(__DIR__ . '/../../resources/views/dashbord/mobile_view/partials/clients_list.blade.php');

        $this->assertStringContainsString("const clientId = Number(\$badge.data('client-id'));", $view);
        $this->assertStringContainsString("data: JSON.stringify({ client_ids: clientIds })", $view);
        $this->assertStringContainsString("contentType: 'application/json'", $view);
        $this->assertStringContainsString("const status = info ? info.status : 'unavailable';", $view);
        $this->assertStringContainsString("online: { cssClass: 'badge-light-success', label: 'متصل' }", $view);
        $this->assertStringContainsString("offline: { cssClass: 'badge-light-secondary', label: 'غير متصل' }", $view);
        $this->assertStringContainsString("not_found: { cssClass: 'badge-light-secondary', label: 'غير موجود' }", $view);
        $this->assertStringContainsString("unavailable: { cssClass: 'badge-light-warning', label: 'غير متاح' }", $view);
        $this->assertStringNotContainsString('usernames', $view);
        $this->assertStringNotContainsString('info.enabled', $view);
        $this->assertStringContainsString('data-client-id="{{ $client->id }}"', $partial);
    }
}
