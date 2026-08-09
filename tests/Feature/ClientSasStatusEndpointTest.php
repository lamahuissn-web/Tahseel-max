<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppConfig;
use App\Models\Role;
use App\Services\Sas4\Sas4ApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\Support\Feature009Database;
use Tests\TestCase;

/**
 * Feature 009 (B2) — POST /api/v1/clients/sas-status JWT batch endpoint.
 *
 * Contract:
 *  - JWT required; 401 otherwise
 *  - client_ids: required array, 1..100 unique positive integers; extra
 *    top-level keys and malformed/non-integer entries are rejected safely 422
 *    with the existing safe envelope (result/message/data)
 *  - IDs resolve inside the exact same active + non-deleted visibility scope
 *    as GET /api/v1/clients; unknown/out-of-scope IDs are omitted without
 *    disclosure
 *  - response data.statuses items carry exactly client_id / sas_username /
 *    status and follow the requested ID order
 *  - unlinked clients (no nonblank sas_username) never trigger a SAS call
 *
 * Database bootstrap (B3): SQLite :memory: uses the minimal Feature 009
 * schema; MySQL runs only against a dedicated `_test` database with the
 * migrate:fresh-once + transaction pattern (see Tests\Support\Feature009Database).
 */
class ClientSasStatusEndpointTest extends TestCase
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

    // ---------------------------------------------------------------- auth

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->postJson('/api/v1/clients/sas-status', ['client_ids' => [1]]);

        $response
            ->assertStatus(401)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'authentication_required');
    }

    public function test_invalid_token_returns_401(): void
    {
        $response = $this->withToken('not-a-real.jwt.token')
            ->postJson('/api/v1/clients/sas-status', ['client_ids' => [1]]);

        $response
            ->assertStatus(401)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'authentication_invalid');
    }

    // ---------------------------------------------------------- validation

    public function test_client_ids_required_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مطلوب', 'required-collector@example.test');

        $this->assertInvalidQuery($this->sasStatusRequest($admin, []));
        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => null]));
    }

    public function test_client_ids_empty_array_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل فارغ', 'empty-collector@example.test');

        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => []]));
    }

    public function test_client_ids_over_100_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مئة', 'hundred-collector@example.test');

        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => range(1, 101)]));
    }

    public function test_client_ids_duplicates_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل تكرار', 'duplicate-collector@example.test');

        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => [1, 1]]));
        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => [1, 2, 3, 2]]));
    }

    public function test_client_ids_zero_and_negative_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل صفر', 'zero-collector@example.test');

        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => [0]]));
        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => [-5]]));
        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => [1, -2]]));
    }

    public function test_client_ids_malformed_entries_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مشوه', 'malformed-collector@example.test');

        foreach ([
            'string' => ['client_ids' => ['abc']],
            'numeric_string' => ['client_ids' => ['1']],
            'float' => ['client_ids' => [1.5]],
            'bool' => ['client_ids' => [true]],
            'null_entry' => ['client_ids' => [null]],
            'nested_array' => ['client_ids' => [[1]]],
            'empty_object' => ['client_ids' => (object) []],
            'scalar' => ['client_ids' => 5],
            'assoc' => ['client_ids' => ['a' => 1]],
        ] as $label => $payload) {
            $this->assertInvalidQuery($this->sasStatusRequest($admin, $payload), $label);
        }
    }

    public function test_extra_top_level_keys_are_rejected_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل زائد', 'extra-collector@example.test');

        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => [1], 'extra' => 'x']));
        $this->assertInvalidQuery($this->sasStatusRequest($admin, ['client_ids' => [1], 'username' => 'attacker']));
    }

    public function test_validation_envelope_has_only_result_message_data(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل غلاف', 'envelope-collector@example.test');

        $response = $this->sasStatusRequest($admin, ['client_ids' => []]);

        $response->assertStatus(422);
        $this->assertSame(['result', 'message', 'data'], array_keys($response->json()));
        $this->assertSame([], $response->json('data'));
    }

    // ------------------------------------------------------- scope/omission

    public function test_resolves_only_active_nondeleted_clients_and_omits_unknown(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل نطاق', 'scope-collector@example.test');
        $active = $this->client('عميل نشط', '0555000101', 'عنوان نشط', 'internet', 'active-user');
        $inactive = $this->client('عميل موقوف', '0555000102', 'عنوان موقوف', 'internet', 'inactive-user');
        $deleted = $this->client('عميل محذوف', '0555000103', 'عنوان محذوف', 'internet', 'deleted-user');
        DB::table('tbl_clients')->where('id', $inactive)->update(['is_active' => '0']);
        DB::table('tbl_clients')->where('id', $deleted)->update(['deleted_at' => now()]);
        $sas = $this->bindSasMock(['active-user', 'inactive-user', 'deleted-user'], ['active-user' => 1]);

        $response = $this->sasStatusRequest($admin, ['client_ids' => [$active, $inactive, $deleted, 999999]]);

        $response->assertOk()->assertJsonPath('result', true);
        $statuses = $response->json('data.statuses');
        // Only the active client is returned; inactive/deleted/unknown IDs are
        // omitted entirely — no disclosure that they exist.
        $this->assertSame([$active], array_column($statuses, 'client_id'));
        $this->assertSame('active-user', $statuses[0]['sas_username']);
        $this->assertSame('online', $statuses[0]['status']);
        $this->assertCount(1, $statuses);
    }

    public function test_statuses_follow_requested_id_order_and_exact_shape(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ترتيب', 'order-collector@example.test');
        $a = $this->client('عميل أ', '0555000201', 'عنوان أ', 'internet', 'user-a');
        $b = $this->client('عميل ب', '0555000202', 'عنوان ب', 'internet', 'user-b');
        $c = $this->client('عميل ج', '0555000203', 'عنوان ج', 'internet', null);
        $sas = $this->bindSasMock(['user-a', 'user-b'], ['user-a' => 0, 'user-b' => 1]);

        $response = $this->sasStatusRequest($admin, ['client_ids' => [$b, $a, $c]]);

        $response->assertOk();
        $statuses = $response->json('data.statuses');
        $this->assertSame([$b, $a, $c], array_column($statuses, 'client_id'));
        foreach ($statuses as $item) {
            $this->assertSame(['client_id', 'sas_username', 'status'], array_keys($item));
        }
        $this->assertSame(['user-b', 'user-a', null], array_column($statuses, 'sas_username'));
        $this->assertSame(['online', 'offline', 'unlinked'], array_column($statuses, 'status'));
        $this->assertSame(['statuses'], array_keys($response->json('data')));
    }

    public function test_unlinked_clients_never_trigger_a_sas_call(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل غير مربوط', 'unlinked-collector@example.test');
        $a = $this->client('عميل أ', '0555000301', 'عنوان أ', 'internet', null);
        $b = $this->client('عميل ب', '0555000302', 'عنوان ب', 'internet', '   ');
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldNotReceive('searchUsers');
        $this->app->instance(Sas4ApiService::class, $sas);

        $response = $this->sasStatusRequest($admin, ['client_ids' => [$a, $b]]);

        $response->assertOk();
        $statuses = $response->json('data.statuses');
        $this->assertCount(2, $statuses);
        foreach ($statuses as $item) {
            $this->assertSame('unlinked', $item['status']);
            $this->assertNull($item['sas_username']);
        }
        Mockery::close();
    }

    public function test_endpoint_integration_maps_exact_statuses_from_one_batch_call(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل تكامل', 'integration-collector@example.test');
        $offline = $this->client('عميل خارج', '0555000401', 'عنوان خارج', 'internet', 'linked-user');
        $online = $this->client('عميل داخل', '0555000402', 'عنوان داخل', 'internet', 'onlineuser');
        $ghost = $this->client('عميل شبح', '0555000403', 'عنوان شبح', 'internet', 'ghost-user');
        $odd = $this->client('عميل غريب', '0555000404', 'عنوان غريب', 'internet', 'odd-user');
        $unlinked = $this->client('عميل بلا', '0555000405', 'عنوان بلا', 'internet', null);

        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')
            ->once()
            ->with('', 1, 5000, 4)
            ->andReturn([
                'data' => [
                    ['username' => 'linked-user', 'online_status' => 0],
                    ['username' => 'OnlineUser', 'online_status' => 1],
                    ['username' => 'unrelated', 'online_status' => 1],
                ],
            ]);
        $this->app->instance(Sas4ApiService::class, $sas);

        $response = $this->sasStatusRequest($admin, ['client_ids' => [$offline, $online, $ghost, $odd, $unlinked]]);

        $response->assertOk();
        $statuses = $response->json('data.statuses');
        $byId = collect($statuses)->keyBy('client_id');
        $this->assertSame('offline', $byId[$offline]['status']);
        $this->assertSame('linked-user', $byId[$offline]['sas_username']);
        // Exact case-insensitive username match, no fallback.
        $this->assertSame('online', $byId[$online]['status']);
        $this->assertSame('onlineuser', $byId[$online]['sas_username']);
        // Successful response without the exact username => not_found.
        $this->assertSame('not_found', $byId[$ghost]['status']);
        // Absent from the valid user list (not malformed) => not_found too.
        $this->assertSame('not_found', $byId[$odd]['status']);
        $this->assertSame('unlinked', $byId[$unlinked]['status']);
        $this->assertNull($byId[$unlinked]['sas_username']);
    }

    // ------------------------------------------------------------- helpers

    private function bindSasMock(array $usernames, array $onlineByUsername): Mockery\MockInterface
    {
        $users = [];
        foreach ($usernames as $username) {
            $users[] = [
                'username' => $username,
                'online_status' => $onlineByUsername[$username] ?? 0,
            ];
        }
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldReceive('searchUsers')
            ->once()
            ->with('', 1, 5000, 4)
            ->andReturn(['data' => $users]);
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

    private function client(string $name, string $phone, string $address, string $clientType = 'internet', ?string $sasUsername = null): int
    {
        $data = [
            'name' => $name,
            'phone' => $phone,
            'address1' => $address,
            'client_type' => $clientType,
            'subscription_id' => 1,
            'price' => 100,
            'subscription_date' => '2026-01-01',
            'start_date' => '2026-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($sasUsername !== null) {
            $data['sas_username'] = $sasUsername;
        }

        return DB::table('tbl_clients')->insertGetId($data);
    }

    private function sasStatusRequest(Admin $admin, array $payload): TestResponse
    {
        return $this->withToken(auth('api')->login($admin))
            ->postJson('/api/v1/clients/sas-status', $payload);
    }

    private function assertInvalidQuery(TestResponse $response, string $label = ''): void
    {
        $response
            ->assertStatus(422)
            ->assertJsonPath('result', false);
        if ($label !== '') {
            $this->assertSame(['result', 'message', 'data'], array_keys($response->json()), "envelope for case: {$label}");
        }
    }
}
