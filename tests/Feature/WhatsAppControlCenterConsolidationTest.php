<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class WhatsAppControlCenterConsolidationTest extends TestCase
{
    public function test_admin_login_route_uses_the_default_localized_prefix(): void
    {
        $this->assertStringEndsWith('/ar/admin/login', route('admin.login'));
    }

    public function test_super_admin_login_redirects_to_the_localized_dashboard(): void
    {
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('authenticate')->once();
        $request->shouldReceive('session->regenerate')->once();

        $user = Mockery::mock();
        $user->shouldReceive('hasRole')->with('Super-Admin')->once()->andReturnTrue();
        $guard = Mockery::mock();
        $guard->shouldReceive('user')->once()->andReturn($user);
        Auth::shouldReceive('guard')->with('admin')->once()->andReturn($guard);

        $response = app(AuthenticatedSessionController::class)->store($request);

        $this->assertSame(route('admin.dashboard'), $response->getTargetUrl());
    }

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
