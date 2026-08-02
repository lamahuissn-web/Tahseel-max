<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiAuthSafetyTest extends TestCase
{
    public function test_api_auth_controller_does_not_expose_debug_dumps(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Api/Auth/AuthController.php'));

        $this->assertStringNotContainsString('dd(', $source);
    }

    public function test_refresh_explicitly_uses_the_jwt_api_guard(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Api/Auth/AuthController.php'));

        $this->assertStringContainsString("auth('api')->refresh()", $source);
    }

    public function test_refresh_rebinds_the_rotated_token_before_building_user_data(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Api/Auth/AuthController.php'));

        $this->assertStringContainsString(
            '$token = auth(\'api\')->refresh();',
            $source,
        );
        $this->assertStringContainsString(
            'auth(\'api\')->setToken($token);',
            $source,
        );
    }
}
