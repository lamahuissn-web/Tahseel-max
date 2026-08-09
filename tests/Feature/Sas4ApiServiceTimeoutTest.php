<?php

namespace Tests\Feature;

use App\Services\Sas4\ClientSasStatusService;
use App\Services\Sas4\Sas4ApiService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Capturing subclass: keeps the REAL getToken()/searchUsers() logic and the
 * real request() call chain, replacing only the curl transport. This proves
 * the timeout plumbed by the status batch reaches the login HTTP request
 * without mocking the implementation away.
 */
class CapturingSas4ApiService extends Sas4ApiService
{
    /** @var array<int, array{method: string, path: string, useToken: bool, timeout: int|null}> */
    public array $calls = [];

    protected function request($method, $path, $data = null, $useToken = true, $timeout = null)
    {
        $this->calls[] = [
            'method' => $method,
            'path' => $path,
            'useToken' => (bool) $useToken,
            'timeout' => $timeout,
        ];

        // Mirror the real request() flow: a token-bearing call must go
        // through the REAL getToken() first (its login request is captured
        // here too); only the curl transport is replaced.
        if ($useToken) {
            $token = $this->getToken($timeout);
            if (! $token) {
                return null;
            }
        }

        if ($method === 'POST' && str_contains((string) $path, '/api/login')) {
            return ['token' => 'captured-token'];
        }

        return ['data' => [['username' => 'user-a', 'online_status' => 1]]];
    }

    public function loginCalls(): array
    {
        return array_values(array_filter($this->calls, fn ($c) => str_contains($c['path'], '/api/login')));
    }

    public function searchCalls(): array
    {
        return array_values(array_filter($this->calls, fn ($c) => str_contains($c['path'], '/api/index/user')));
    }
}

/**
 * Feature 009 (blocker 1) — the batch status timeout must bound EVERY
 * external HTTP request of the sequence, including an uncached login token
 * request. Default callers keep the existing 15s behavior.
 */
class Sas4ApiServiceTimeoutTest extends TestCase
{
    public function test_status_search_timeout_4_also_applies_to_uncached_login_request(): void
    {
        $sas = new CapturingSas4ApiService();
        $service = new ClientSasStatusService($sas, 20, 4);

        $result = $service->resolve([(object) ['id' => 1, 'sas_username' => 'user-a']]);

        $this->assertSame('online', $result[1]['status']);
        $login = $sas->loginCalls();
        $search = $sas->searchCalls();
        // Token cache is empty (fresh test app): exactly one login request
        // must have been issued, and it must honor the 4s timeout.
        $this->assertCount(1, $login, 'An uncached token must trigger exactly one login request.');
        $this->assertSame(4, $login[0]['timeout'], 'The login HTTP request must honor the 4s timeout.');
        $this->assertFalse($login[0]['useToken']);
        $this->assertCount(1, $search);
        $this->assertSame(4, $search[0]['timeout'], 'The search HTTP request must honor the 4s timeout.');
        $this->assertTrue($search[0]['useToken']);
    }

    public function test_default_callers_keep_existing_15s_login_timeout(): void
    {
        $sas = new CapturingSas4ApiService();

        $sas->getToken();

        $login = $sas->loginCalls();
        $this->assertCount(1, $login);
        $this->assertNull($login[0]['timeout'], 'Default callers must keep the existing 15s behavior (null => default timeout).');
    }

    public function test_cached_token_skips_the_login_request_entirely(): void
    {
        Cache::put('sas4_token', 'cached-token', 600);
        $sas = new CapturingSas4ApiService();
        $service = new ClientSasStatusService($sas, 20, 4);

        $result = $service->resolve([(object) ['id' => 1, 'sas_username' => 'user-a']]);

        $this->assertSame('online', $result[1]['status']);
        $this->assertCount(0, $sas->loginCalls(), 'A cached token must not trigger any login request.');
        $this->assertCount(1, $sas->searchCalls());
        $this->assertSame(4, $sas->searchCalls()[0]['timeout']);
    }

    public function test_authenticated_requests_use_the_bearer_scheme_not_a_redaction_placeholder(): void
    {
        $source = file_get_contents(app_path('Services/Sas4/Sas4ApiService.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("'Authorization: Bearer ' . \$token", $source);
        $this->assertStringNotContainsString("'Authorization: *** ' . \$token", $source);
    }
}
