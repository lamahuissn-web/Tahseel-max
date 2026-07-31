<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WhatsAppControlCenterConsolidationTest extends TestCase
{
    public function test_legacy_settings_page_redirects_to_control_center_dashboard(): void
    {
        $this->withoutMiddleware()
            ->get(route('admin.settings.whatsapp'))
            ->assertRedirect(route('admin.whatsapp.dashboard'));
    }

    public function test_control_center_exposes_monitor_actions_under_its_own_namespace(): void
    {
        $expectedRoutes = [
            'admin.whatsapp.monitor.status' => 'GET',
            'admin.whatsapp.monitor.qr' => 'GET',
            'admin.whatsapp.monitor.restart' => 'POST',
            'admin.whatsapp.monitor.emergency_stop' => 'POST',
            'admin.whatsapp.monitor.emergency_restart' => 'POST',
        ];

        foreach ($expectedRoutes as $name => $method) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Missing route {$name}");
            $this->assertStringContainsString('whatsapp/monitor/', $route->uri());
            $this->assertContains($method, $route->methods());
        }
    }

    public function test_legacy_action_routes_remain_available_during_the_transition(): void
    {
        $legacyActionRoutes = [
            'admin.settings.whatsapp.update',
            'admin.settings.whatsapp.preview',
            'admin.settings.whatsapp.test',
            'admin.settings.whatsapp.restart',
            'admin.settings.whatsapp.api_status',
            'admin.settings.whatsapp.api_qr',
            'admin.settings.whatsapp.emergency_stop',
            'admin.settings.whatsapp.emergency_restart',
        ];

        foreach ($legacyActionRoutes as $name) {
            $this->assertNotNull(Route::getRoutes()->getByName($name), "Missing compatibility route {$name}");
        }
    }
}
