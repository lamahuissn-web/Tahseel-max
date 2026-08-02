<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MobileCollectorInitialFeedbackTest extends TestCase
{
    public function test_clients_api_is_paginated_without_correlated_invoice_sums(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Api/ClientsController.php'));

        $this->assertStringContainsString('leftJoinSub(', $source);
        $this->assertStringContainsString('->paginate(', $source);
        $this->assertStringContainsString("orderBy('tbl_clients.id', 'desc')", $source);
        $this->assertStringContainsString("trim((string) \$request->input('search', ''))", $source);
        $this->assertStringContainsString("'search' => 'nullable|string|max:255'", $source);
        $this->assertStringContainsString("'per_page' => 'nullable|integer|min:1|max:100'", $source);
        $this->assertStringContainsString("'pagination'", $source);
    }

    public function test_collections_api_is_collector_scoped_and_ordered_by_received_at(): void
    {
        $routeSource = file_get_contents(base_path('routes/api.php'));
        $controllerSource = file_get_contents(
            app_path('Http/Controllers/Api/CollectionsController.php'),
        );

        $this->assertStringContainsString("Route::get('/collections'", $routeSource);
        $this->assertStringContainsString(
            'where(\'collected_by\', $user->id)',
            $controllerSource,
        );
        $this->assertStringContainsString("whereBetween('received_at'", $controllerSource);
        $this->assertStringContainsString("whereNotNull('invoice_id')", $controllerSource);
        $this->assertStringContainsString("orderByDesc('received_at')", $controllerSource);
        $this->assertStringContainsString('->paginate(', $controllerSource);
        $this->assertStringNotContainsString('Invoice::query()', $controllerSource);

        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($route) => $route->uri() === 'api/v1/collections');
        $this->assertNotNull($route);
    }

    public function test_collections_api_rejects_requests_without_a_jwt(): void
    {
        $response = $this->getJson('/api/v1/collections');

        $response->assertJsonPath('message', 'Token not found');
        $this->assertNotTrue($response->json('result'));
    }
}
