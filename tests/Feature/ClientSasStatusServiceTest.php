<?php

namespace Tests\Feature;

use App\Services\Sas4\ClientSasStatusService;
use App\Services\Sas4\Sas4ApiService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

/**
 * Feature 009 (B3+B4) — dedicated batch SAS status service semantics.
 *
 * Pure unit tests (no DB): the service is constructed directly with a mocked
 * Sas4ApiService, so the SAS call count, retry behavior, timeout argument and
 * cache behavior are all observable without any external call.
 *
 * B3 contract:
 *  - one all-users search per batch, exact Unicode case-insensitive username
 *    matching, NEVER a fallback to client name/phone/user/fuzzy identifiers
 *  - online_status 1 => online, 0 => offline; absent or any other value =>
 *    unavailable; failure is never normalized to offline
 *  - successful response without the exact username => not_found
 *  - no nonblank username => unlinked with no SAS call at all
 *
 * B4 contract:
 *  - success-only 20s cache: hits skip SAS, expiry forces a new call,
 *    failed/malformed calls are never cached
 *  - 4s external timeout target passed to the SAS service
 *  - one bounded token-refresh retry only on failure; ordinary successful
 *    calls never touch the token cache
 */
class ClientSasStatusServiceTest extends TestCase
{
    // ------------------------------------------------------------ constants

    public function test_contract_constants_are_exact(): void
    {
        $this->assertSame(20, ClientSasStatusService::CACHE_TTL_SECONDS);
        $this->assertSame(4, ClientSasStatusService::EXTERNAL_TIMEOUT_SECONDS);
        $this->assertSame(1, ClientSasStatusService::MIN_EXTERNAL_TIMEOUT_SECONDS);
        $this->assertSame(20, ClientSasStatusService::MAX_EXTERNAL_TIMEOUT_SECONDS);
        $this->assertSame(4, config('sas4.status_timeout_seconds'));
        $this->assertSame(5000, ClientSasStatusService::ALL_USERS_SEARCH_COUNT);
        $this->assertSame('sas4_users_online_status_map', ClientSasStatusService::CACHE_KEY);
    }

    public function test_deployment_config_selects_the_status_timeout_when_no_explicit_override_is_injected(): void
    {
        config()->set('sas4.status_timeout_seconds', 20);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 20)->andReturn([
            'data' => [['username' => 'user-a', 'online_status' => 1]],
        ]);

        $result = (new ClientSasStatusService($sas))->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $result[1]['status']);
    }

    public function test_invalid_deployment_timeout_values_fall_back_to_the_bounded_default(): void
    {
        foreach (['', 0, -1, 1.5, '1.5', 21, '21', 60, '60', 'abc', null] as $invalid) {
            Cache::forget(ClientSasStatusService::CACHE_KEY);
            config()->set('sas4.status_timeout_seconds', $invalid);
            $sas = Mockery::mock(Sas4ApiService::class);
            $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 4)->andReturn([
                'data' => [['username' => 'user-a', 'online_status' => 1]],
            ]);

            $result = (new ClientSasStatusService($sas))->resolve([$this->client(1, 'user-a')]);

            $this->assertSame('online', $result[1]['status']);
        }
    }

    public function test_minimum_and_maximum_deployment_timeout_boundaries_are_accepted(): void
    {
        foreach ([1, '1', 20, '20'] as $valid) {
            Cache::forget(ClientSasStatusService::CACHE_KEY);
            config()->set('sas4.status_timeout_seconds', $valid);
            $sas = Mockery::mock(Sas4ApiService::class);
            $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, (int) $valid)->andReturn([
                'data' => [['username' => 'user-a', 'online_status' => 1]],
            ]);

            $result = (new ClientSasStatusService($sas))->resolve([$this->client(1, 'user-a')]);

            $this->assertSame('online', $result[1]['status']);
        }
    }

    public function test_explicit_constructor_timeout_still_overrides_deployment_config(): void
    {
        config()->set('sas4.status_timeout_seconds', 20);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 7)->andReturn([
            'data' => [['username' => 'user-a', 'online_status' => 1]],
        ]);

        $result = (new ClientSasStatusService($sas, 20, 7))->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $result[1]['status']);
    }

    public function test_out_of_range_constructor_timeout_falls_back_to_the_bounded_default(): void
    {
        config()->set('sas4.status_timeout_seconds', 4);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 4)->andReturn([
            'data' => [['username' => 'user-a', 'online_status' => 1]],
        ]);

        $result = (new ClientSasStatusService($sas, 20, 999))->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $result[1]['status']);
    }

    // ------------------------------------------------------------ B3: status

    public function test_unlinked_client_without_sas_username_never_calls_sas(): void
    {
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldNotReceive('searchUsers');
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, null), $this->client(2, '   ')]);

        $this->assertSame('unlinked', $result[1]['status']);
        $this->assertNull($result[1]['sas_username']);
        $this->assertSame('unlinked', $result[2]['status']);
        $this->assertNull($result[2]['sas_username']);
    }

    public function test_exact_case_insensitive_username_match_online(): void
    {
        $sas = $this->sasWithUsers(['Abed.Net' => 1, 'Other.User' => 0]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'abed.net')]);

        $this->assertSame('online', $result[1]['status']);
        $this->assertSame('abed.net', $result[1]['sas_username']);
    }

    public function test_exact_case_insensitive_username_match_offline(): void
    {
        $sas = $this->sasWithUsers(['Abed.Net' => 0]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'ABED.NET')]);

        $this->assertSame('offline', $result[1]['status']);
    }

    public function test_unicode_case_insensitive_match_is_safe(): void
    {
        $sas = $this->sasWithUsers(['Réseau.1' => 1]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'réseau.1')]);

        $this->assertSame('online', $result[1]['status']);
    }

    public function test_explicit_one_and_zero_values_are_valid_and_int_normalized(): void
    {
        $sas = $this->sasWithUsers([
            'int-one' => 1,
            'string-one' => '1',
            'int-zero' => 0,
            'string-zero' => '0',
        ]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([
            $this->client(1, 'int-one'),
            $this->client(2, 'string-one'),
            $this->client(3, 'int-zero'),
            $this->client(4, 'string-zero'),
        ]);

        $this->assertSame('online', $result[1]['status']);
        $this->assertSame('online', $result[2]['status']);
        $this->assertSame('offline', $result[3]['status']);
        $this->assertSame('offline', $result[4]['status']);
        // Only a minimal normalized map is cached: lowercase username => int(0|1).
        $this->assertSame(
            ['int-one' => 1, 'string-one' => 1, 'int-zero' => 0, 'string-zero' => 0],
            Cache::get(ClientSasStatusService::CACHE_KEY),
        );
    }

    public function test_online_status_not_exactly_zero_or_one_makes_the_whole_payload_malformed(): void
    {
        foreach ([true, false, 2, 'yes', null, ''] as $bad) {
            $sas = Mockery::mock(Sas4ApiService::class);
            $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)
                ->andReturn(['data' => [['username' => 'user-a', 'online_status' => $bad]]]);
            $service = new ClientSasStatusService($sas);

            $result = $service->resolve([$this->client(1, 'user-a')]);

            $this->assertSame(
                'unavailable',
                $result[1]['status'],
                'online_status '.var_export($bad, true).' must make the whole payload malformed (never offline).',
            );
            $this->assertFalse(Cache::has(ClientSasStatusService::CACHE_KEY), 'A malformed payload must never be cached.');
        }
    }

    public function test_non_array_row_makes_the_whole_payload_malformed(): void
    {
        $this->assertMalformed([['username' => 'user-a', 'online_status' => 1], 'garbage']);
    }

    public function test_missing_username_makes_the_whole_payload_malformed(): void
    {
        $this->assertMalformed([['online_status' => 1]]);
    }

    public function test_non_string_username_makes_the_whole_payload_malformed(): void
    {
        $this->assertMalformed([['username' => 123, 'online_status' => 1]]);
    }

    public function test_blank_username_makes_the_whole_payload_malformed(): void
    {
        $this->assertMalformed([['username' => " \t ", 'online_status' => 1]]);
    }

    public function test_missing_online_status_makes_the_whole_payload_malformed(): void
    {
        $this->assertMalformed([['username' => 'user-a']]);
    }

    public function test_duplicate_normalized_username_makes_the_whole_payload_malformed(): void
    {
        $this->assertMalformed([
            ['username' => 'User.Name', 'online_status' => 1],
            ['username' => 'user.name', 'online_status' => 0],
        ]);
    }

    public function test_unrelated_malformed_row_never_yields_partial_results(): void
    {
        // A perfectly valid row next to one malformed row: nothing may be
        // silently skipped and no partial map may resolve — the valid user
        // must NOT turn into online/not_found.
        $this->assertMalformed([
            ['username' => 'user-a', 'online_status' => 1],
            ['username' => 'user-b'], // missing online_status
        ]);
    }

    // ------------------------------------------- B1: data container strictness

    public function test_associative_data_container_is_malformed(): void
    {
        // {"data": {"a": {...}}} decodes to an associative array, not a list.
        $this->assertMalformed(['a' => ['username' => 'user-a', 'online_status' => 1]]);
    }

    public function test_sparse_numeric_data_container_is_malformed(): void
    {
        $this->assertMalformed([
            0 => ['username' => 'user-a', 'online_status' => 1],
            2 => ['username' => 'user-b', 'online_status' => 0],
        ]);
    }

    public function test_mixed_key_data_container_is_malformed(): void
    {
        $this->assertMalformed([
            0 => ['username' => 'user-a', 'online_status' => 1],
            'x' => ['username' => 'user-b', 'online_status' => 0],
        ]);
    }

    public function test_list_shaped_data_container_remains_valid(): void
    {
        $sas = $this->sasWithUsers(['user-a' => 1, 'user-b' => 0]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a'), $this->client(2, 'user-b')]);

        $this->assertSame('online', $result[1]['status']);
        $this->assertSame('offline', $result[2]['status']);
        $this->assertTrue(Cache::has(ClientSasStatusService::CACHE_KEY), 'A valid list-shaped container is cached normally.');
    }

    public function test_associative_data_triggers_one_token_forget_and_retry_then_recovers(): void
    {
        Cache::put('sas4_token', 'stale-token', 60);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)->andReturn(
            ['data' => ['x' => ['username' => 'user-a', 'online_status' => 1]]], // assoc => malformed
            ['data' => [['username' => 'user-a', 'online_status' => 1]]],        // list => valid
        );
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $result[1]['status'], 'The retry must recover on the second valid response.');
        $this->assertNull(Cache::get('sas4_token'), 'A non-list data container must invalidate the token cache exactly once.');
        $this->assertTrue(Cache::has(ClientSasStatusService::CACHE_KEY), 'The recovered success is cached.');
    }

    public function test_associative_data_twice_is_unavailable_and_not_cached(): void
    {
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)
            ->andReturn(['data' => ['x' => ['username' => 'user-a', 'online_status' => 1]]]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('unavailable', $result[1]['status']);
        $this->assertFalse(Cache::has(ClientSasStatusService::CACHE_KEY), 'A malformed data container must never be cached.');
    }

    public function test_malformed_row_triggers_one_token_forget_and_retry_then_recovers(): void
    {
        Cache::put('sas4_token', 'stale-token', 60);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)->andReturn(
            ['data' => [['username' => 'user-a', 'online_status' => 2]]], // malformed row
            ['data' => [['username' => 'user-a', 'online_status' => 1]]], // valid
        );
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $result[1]['status'], 'The retry must recover on the second valid response.');
        $this->assertNull(Cache::get('sas4_token'), 'A malformed payload must invalidate the token cache exactly once.');
        $this->assertTrue(Cache::has(ClientSasStatusService::CACHE_KEY), 'The recovered success is cached.');
    }

    public function test_successful_response_missing_exact_username_is_not_found(): void
    {
        $sas = $this->sasWithUsers(['real-user' => 1, 'another-user' => 0]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'ghost-user')]);

        $this->assertSame('not_found', $result[1]['status']);
        $this->assertSame('ghost-user', $result[1]['sas_username']);
    }

    public function test_no_fallback_to_client_name_phone_or_user(): void
    {
        // The SAS user's username differs from the client's sas_username, but
        // firstname and client name/phone coincide — nothing may be used as a
        // fallback identifier.
        $sas = $this->sasWithUsersRaw([
            ['username' => 'real-sas-user', 'firstname' => 'أحمد خالد', 'online_status' => 1],
        ]);
        $service = new ClientSasStatusService($sas);
        $client = $this->client(1, 'wrong-username');
        $client->name = 'أحمد خالد';
        $client->phone = '03555555';

        $result = $service->resolve([$client]);

        $this->assertSame('not_found', $result[1]['status'], 'Username must be the only matching key.');
    }

    public function test_malformed_non_array_data_twice_is_unavailable_and_never_cached(): void
    {
        // A non-array `data` value is an unusable payload: it must take the
        // single bounded token-refresh retry (2 calls), and if the retry is
        // also malformed the result is unavailable and is NOT cached.
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)->andReturn(['data' => 'bad']);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-1'), $this->client(2, 'user-2')]);

        $this->assertSame('unavailable', $result[1]['status']);
        $this->assertSame('unavailable', $result[2]['status']);
        $this->assertFalse(Cache::has(ClientSasStatusService::CACHE_KEY), 'Malformed calls must never be cached as success.');
    }

    public function test_non_array_data_first_response_triggers_one_token_forget_and_one_retry_then_recovers(): void
    {
        Cache::put('sas4_token', 'stale-token', 60);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')
            ->twice()
            ->with('', 1, 5000, 4)
            ->andReturn(
                ['data' => 'bad'],
                ['data' => [['username' => 'user-a', 'online_status' => 1]]]
            );
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $result[1]['status'], 'The retry must be able to recover on the second valid response.');
        $this->assertNull(Cache::get('sas4_token'), 'The unusable first response must invalidate the token cache exactly once.');
        $this->assertTrue(Cache::has(ClientSasStatusService::CACHE_KEY), 'The recovered success is cached normally.');
    }

    public function test_response_without_data_key_is_unavailable(): void
    {
        // An unusable payload triggers the single bounded token-refresh
        // retry; after the retry the response is still unusable => unavailable.
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)->andReturn(['error' => 'boom']);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-1')]);

        $this->assertSame('unavailable', $result[1]['status']);
    }

    public function test_null_response_is_unavailable_never_offline(): void
    {
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)->andReturn(null);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-1')]);

        $this->assertSame('unavailable', $result[1]['status']);
    }

    public function test_empty_data_array_is_a_successful_response(): void
    {
        $sas = $this->sasWithUsers([]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'ghost')]);

        // Well-formed empty user list: the exact username is simply absent.
        $this->assertSame('not_found', $result[1]['status']);
    }

    // ------------------------------------------------------------ B4: cache

    public function test_success_is_cached_and_second_resolve_skips_sas(): void
    {
        $sas = $this->sasWithUsers(['user-a' => 1]);
        $service = new ClientSasStatusService($sas);

        $first = $service->resolve([$this->client(1, 'user-a')]);
        $second = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $first[1]['status']);
        $this->assertSame('online', $second[1]['status']);
        $this->assertTrue(Cache::has(ClientSasStatusService::CACHE_KEY));
        // Exactly one SAS call for two resolves: the second is a cache hit.
        Mockery::close();
    }

    public function test_cache_expiry_forces_a_new_sas_call(): void
    {
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)->andReturn(['data' => [['username' => 'user-a', 'online_status' => 1]]]);
        $service = new ClientSasStatusService($sas, 1, 4);

        $first = $service->resolve([$this->client(1, 'user-a')]);
        $this->assertSame('online', $first[1]['status']);
        $this->assertTrue(Cache::has(ClientSasStatusService::CACHE_KEY));

        // Wait past the 1-second TTL (bounded sleep; the default TTL is 20s).
        sleep(2);

        $second = $service->resolve([$this->client(1, 'user-a')]);
        $this->assertSame('online', $second[1]['status']);
    }

    public function test_failed_call_is_never_cached(): void
    {
        // Each resolve performs the single bounded retry (2 SAS calls), and
        // the failure is never cached: two resolves => 4 SAS calls total.
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->times(4)->with('', 1, 5000, 4)->andReturn(null);
        $service = new ClientSasStatusService($sas);

        $first = $service->resolve([$this->client(1, 'user-a')]);
        $second = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('unavailable', $first[1]['status']);
        $this->assertSame('unavailable', $second[1]['status']);
        $this->assertFalse(Cache::has(ClientSasStatusService::CACHE_KEY));
        // Two resolves, two SAS calls: failure was never cached as success.
        Mockery::close();
    }

    // ------------------------------------------------------------ B4: retry

    public function test_token_refresh_retry_once_then_success(): void
    {
        Cache::put('sas4_token', 'stale-token', 60);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')
            ->twice()
            ->with('', 1, 5000, 4)
            ->andReturn(null, ['data' => [['username' => 'user-a', 'online_status' => 1]]]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $result[1]['status']);
        // The stale token cache entry was invalidated for the retry.
        $this->assertNull(Cache::get('sas4_token'));
        $this->assertTrue(Cache::has(ClientSasStatusService::CACHE_KEY));
    }

    public function test_retry_happens_exactly_once_when_both_attempts_fail(): void
    {
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)->andReturn(null);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('unavailable', $result[1]['status']);
        $this->assertFalse(Cache::has(ClientSasStatusService::CACHE_KEY));
    }

    public function test_ordinary_successful_calls_never_touch_the_token_cache(): void
    {
        Cache::put('sas4_token', 'fresh-token', 60);
        $sas = $this->sasWithUsers(['user-a' => 1]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('online', $result[1]['status']);
        $this->assertSame('fresh-token', Cache::get('sas4_token'), 'Success path must not refresh the token.');
    }

    // ------------------------------------------- B2: cache read invariant

    public function test_uppercase_cached_map_is_invalid_forgotten_and_refetched(): void
    {
        Cache::put(ClientSasStatusService::CACHE_KEY, ['USER-A' => 1], 60);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 4)
            ->andReturn(['data' => [['username' => 'user-a', 'online_status' => 1]]]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        // A poisoned cache entry must never drive not_found/offline/online
        // directly: it is forgotten, treated as a miss and refetched.
        $this->assertSame('online', $result[1]['status'], 'Invalid cached map must be forgotten and refetched (not used as-is).');
        $this->assertSame(['user-a' => 1], Cache::get(ClientSasStatusService::CACHE_KEY), 'The invalid entry must be replaced by the normalized map.');
        Mockery::close();
    }

    public function test_invalid_cached_map_shapes_are_forgotten_and_refetched(): void
    {
        $invalidMaps = [
            'untrimmed key' => [' user-a ' => 1],
            'blank key' => ['' => 1],
            'int key' => [123 => 1],
            'numeric-string key' => ['7' => 1], // PHP normalizes to int key 7
            'string-zero value' => ['user-a' => '0'],
            'string-one value' => ['user-a' => '1'],
            'bool value' => ['user-a' => true],
            'null value' => ['user-a' => null],
            'int-2 value' => ['user-a' => 2],
            'nested value' => ['user-a' => [0]],
            'assoc value' => ['user-a' => ['x' => 0]],
        ];

        foreach ($invalidMaps as $label => $map) {
            Cache::flush();
            Cache::put(ClientSasStatusService::CACHE_KEY, $map, 60);
            $sas = Mockery::mock(Sas4ApiService::class);
            $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 4)
                ->andReturn(['data' => [['username' => 'user-a', 'online_status' => 1]]]);
            $service = new ClientSasStatusService($sas);

            $result = $service->resolve([$this->client(1, 'user-a')]);

            $this->assertSame('online', $result[1]['status'], "cached map ({$label}) must be treated as a miss and refetched");
            $this->assertSame(['user-a' => 1], Cache::get(ClientSasStatusService::CACHE_KEY), "cached map ({$label}) must be replaced by the normalized map");
        }
        Mockery::close();
    }

    public function test_valid_minimal_cached_map_is_used_with_zero_sas_calls(): void
    {
        Cache::put(ClientSasStatusService::CACHE_KEY, ['user-a' => 1, 'user-b' => 0], 60);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldNotReceive('searchUsers');
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a'), $this->client(2, 'user-b'), $this->client(3, 'ghost')]);

        $this->assertSame('online', $result[1]['status']);
        $this->assertSame('offline', $result[2]['status']);
        $this->assertSame('not_found', $result[3]['status']);
        Mockery::close();
    }

    public function test_empty_cached_map_is_valid_and_used_with_zero_sas_calls(): void
    {
        Cache::put(ClientSasStatusService::CACHE_KEY, [], 60);
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldNotReceive('searchUsers');
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a')]);

        $this->assertSame('not_found', $result[1]['status'], 'An empty cached map is a valid success (no users).');
        Mockery::close();
    }

    // ------------------------------------------------------------- helpers

    private function client(int $id, ?string $sasUsername): object
    {
        return (object) ['id' => $id, 'sas_username' => $sasUsername];
    }

    private function sasWithUsers(array $onlineByUsername): Mockery\MockInterface
    {
        $users = [];
        foreach ($onlineByUsername as $username => $online) {
            $users[] = ['username' => $username, 'online_status' => $online];
        }

        return $this->sasWithUsersRaw($users);
    }

    private function sasWithUsersRaw(array $users): Mockery\MockInterface
    {
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 4)->andReturn(['data' => $users]);

        return $sas;
    }

    /**
     * A malformed row payload must trigger the single bounded token-refresh
     * retry (2 calls), leave every linked client unavailable and never be
     * cached — no silent row skipping, no partial resolution.
     */
    private function assertMalformed(array $users): void
    {
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')->twice()->with('', 1, 5000, 4)->andReturn(['data' => $users]);
        $service = new ClientSasStatusService($sas);

        $result = $service->resolve([$this->client(1, 'user-a'), $this->client(2, 'user-b')]);

        $this->assertSame('unavailable', $result[1]['status'], 'Every linked client must be unavailable for a malformed payload.');
        $this->assertSame('unavailable', $result[2]['status']);
        $this->assertFalse(Cache::has(ClientSasStatusService::CACHE_KEY), 'A malformed payload must never be cached.');
    }
}
