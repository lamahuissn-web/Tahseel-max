<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppConfig;
use App\Models\Role;
use App\Services\Sas4\Sas4ApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\Support\Feature009Database;
use Tests\TestCase;

/**
 * Feature 009 (B5) — bounded queries and bounded SAS calls.
 *
 * Contract:
 *  - the batch endpoint issues a fixed number of DB queries whether the
 *    request carries 1 or 100 client IDs
 *  - exactly ONE all-users SAS search per batch (plus the single bounded
 *    retry on failure), never one call per ID
 *  - the 20s success cache turns repeated batches into zero SAS calls
 *  - only client_id / sas_username / status are returned; raw SAS fields
 *    (password, enabled, IP, expiration, profile, traffic, token, ...) never
 *    leak into the response
 *
 * Database bootstrap (B3): SQLite :memory: uses the minimal Feature 009
 * schema; MySQL runs only against a dedicated `_test` database with the
 * migrate:fresh-once + transaction pattern (see Tests\Support\Feature009Database).
 */
class ClientSasStatusBoundsTest extends TestCase
{
    use Feature009Database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapFeature009Database();
    }

    protected function tearDown(): void
    {
        $this->teardownFeature009Database();

        parent::tearDown();
    }

    public function test_db_query_count_is_identical_for_1_vs_100_ids(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل استعلامات', 'bounds-collector@example.test');
        $ids = $this->clientsWithSas(100);
        $sas = $this->bindSasMock();
        $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 4)->andReturn($this->sasPayload(100));

        DB::flushQueryLog();
        DB::enableQueryLog();
        $one = $this->sasStatusRequest($admin, ['client_ids' => [$ids[0]]]);
        $one->assertOk();
        $oneQueries = count(DB::getQueryLog());

        DB::flushQueryLog();
        $hundred = $this->sasStatusRequest($admin, ['client_ids' => $ids]);
        $hundred->assertOk();
        $hundredQueries = count(DB::getQueryLog());

        $this->assertSame($oneQueries, $hundredQueries, 'Query count must not scale with the number of client IDs.');
        $this->assertLessThanOrEqual(4, $hundredQueries);
        $this->assertCount(100, $hundred->json('data.statuses'));
        // The first batch populated the 20s success cache: the 100-ID batch
        // cost zero extra SAS calls.
        Mockery::close();
    }

    public function test_exactly_one_sas_call_per_batch_for_1_and_for_100_ids(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مكالمات', 'calls-collector@example.test');
        $ids = $this->clientsWithSas(100);
        $sas = $this->bindSasMock();
        $sas->shouldReceive('searchUsers')->times(2)->with('', 1, 5000, 4)->andReturn($this->sasPayload(100));

        $one = $this->sasStatusRequest($admin, ['client_ids' => [$ids[0]]]);
        $one->assertOk();
        $this->assertCount(1, $one->json('data.statuses'));

        // Fresh cache: the second batch must again cost exactly ONE call for
        // 100 IDs — never 100.
        Cache::flush();

        $hundred = $this->sasStatusRequest($admin, ['client_ids' => $ids]);
        $hundred->assertOk();
        $this->assertCount(100, $hundred->json('data.statuses'));
    }

    public function test_cache_turns_repeated_batches_into_zero_sas_calls(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل كاش', 'cache-collector@example.test');
        $ids = $this->clientsWithSas(5);
        $sas = $this->bindSasMock();
        $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 4)->andReturn($this->sasPayload(5));

        for ($i = 0; $i < 3; $i++) {
            $response = $this->sasStatusRequest($admin, ['client_ids' => $ids]);
            $response->assertOk();
            $this->assertCount(5, $response->json('data.statuses'));
        }

        $this->assertTrue(Cache::has(\App\Services\Sas4\ClientSasStatusService::CACHE_KEY));
        // Exactly one SAS call for three batches.
        Mockery::close();
    }

    public function test_rich_sas_payload_never_leaks_into_the_response(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل سرية', 'privacy-collector@example.test');
        $id = $this->clientWithSas('rich-user');
        $sas = $this->bindSasMock();
        $sas->shouldReceive('searchUsers')->once()->with('', 1, 5000, 4)->andReturn([
            'data' => [[
                'id' => 7,
                'username' => 'rich-user',
                'online_status' => 1,
                'password' => 'super-secret',
                'radpass' => 'radius-secret',
                'enabled' => 1,
                'ip' => '10.9.9.9',
                'static_ip' => '10.9.9.8',
                'expiration' => '2099-01-01',
                'profile_id' => 3,
                'profile' => ['name' => 'Gold', 'price' => 999],
                'traffic' => ['rx' => 1, 'tx' => 2],
                'total_bytes' => 12345,
                'token' => 'sas-jwt-token',
                'balance' => 55.5,
                'firstname' => 'Sensitive',
            ]],
        ]);

        $response = $this->sasStatusRequest($admin, ['client_ids' => [$id]]);

        $response->assertOk();
        $statuses = $response->json('data.statuses');
        $this->assertCount(1, $statuses);
        $this->assertSame(['client_id', 'sas_username', 'status'], array_keys($statuses[0]));
        $content = $response->getContent();
        foreach (['"password"', '"radpass"', '"enabled"', '"ip"', '"static_ip"', '"expiration"', '"profile_id"', '"profile"', '"traffic"', '"total_bytes"', '"token"', '"balance"', '"firstname"', 'super-secret', 'radius-secret', 'sas-jwt-token', '10.9.9.9'] as $leak) {
            $this->assertStringNotContainsString($leak, $content, "SAS field {$leak} must never leak into the API response.");
        }
        $this->assertSame('online', $statuses[0]['status']);
        $this->assertSame('rich-user', $statuses[0]['sas_username']);
    }

    // ------------------------------------------------------------- helpers

    private function clientsWithSas(int $count): array
    {
        $ids = [];
        for ($i = 1; $i <= $count; $i++) {
            $ids[] = $this->clientWithSas('user-'.$i);
        }

        return $ids;
    }

    private function clientWithSas(string $sasUsername): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name' => 'عميل '.$sasUsername,
            'phone' => '0555'.str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
            'address1' => 'عنوان '.$sasUsername,
            'client_type' => 'internet',
            'subscription_id' => 1,
            'price' => 100,
            'subscription_date' => '2026-01-01',
            'start_date' => '2026-01-01',
            'sas_username' => $sasUsername,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sasPayload(int $count): array
    {
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $users[] = ['username' => 'user-'.$i, 'online_status' => $i % 2];
        }

        return ['data' => $users];
    }

    private function bindSasMock(): Mockery\MockInterface
    {
        $sas = Mockery::mock(Sas4ApiService::class);
        $this->app->instance(Sas4ApiService::class, $sas);

        return $sas;
    }

    private function currency(string $value): void
    {
        AppConfig::create(['key' => 'currency', 'value' => $value]);
    }

    private function admin(string $name, string $email, string $roleName = 'collector', string $status = '1'): Admin
    {
        $admin = Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('test-password'),
            'status' => $status,
        ]);

        if ($roleName !== '') {
            Role::findOrCreate($roleName, 'admin');
            $admin->assignRole($roleName);
        }

        return $admin;
    }

    private function sasStatusRequest(Admin $admin, array $payload): TestResponse
    {
        return $this->withToken(auth('api')->login($admin))
            ->postJson('/api/v1/clients/sas-status', $payload);
    }
}
