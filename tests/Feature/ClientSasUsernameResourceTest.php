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
 * Feature 009 (B1) — Client-list contract: nullable normalized camelCase
 * `sasUsername` on the client resource and `tbl_clients.sas_username` inside
 * the existing grouped active/deleted search scope.
 *
 * Contract:
 *  - every client row exposes `sasUsername`, blank/whitespace normalized to null
 *  - search may match `sas_username` but stays inside the grouped search group
 *    composed with the active/deleted scope and the client_type filter
 *  - the clients list never waits for / calls SAS
 *  - adding the field adds no DB queries (it is a plain loaded column)
 *
 * Database bootstrap (B3): SQLite :memory: uses the minimal Feature 009
 * schema; MySQL runs only against a dedicated `_test` database with the
 * migrate:fresh-once + transaction pattern (see Tests\Support\Feature009Database).
 */
class ClientSasUsernameResourceTest extends TestCase
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

    public function test_clients_resource_exposes_sas_username_normalized_to_null_when_blank(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل المورد', 'resource-collector@example.test');
        $this->client('عميل مرتبط', '0555000001', 'عنوان مرتبط', 'internet', 'Abed.Net');
        $this->client('عميل بلا', '0555000002', 'عنوان بلا', 'internet', null);
        $this->client('عميل فارغ', '0555000003', 'عنوان فارغ', 'internet', '');
        $this->client('عميل مسافة', '0555000004', 'عنوان مسافة', 'internet', " \t ");
        $this->client('عميل تقليم', '0555000005', 'عنوان تقليم', 'internet', '  user1  ');

        $response = $this->clientsRequest($admin, ['per_page' => 50]);

        $response->assertOk();
        $byName = collect($response->json('data.clients'))->keyBy('name');
        $this->assertSame('Abed.Net', $byName['عميل مرتبط']['sasUsername']);
        $this->assertNull($byName['عميل بلا']['sasUsername']);
        $this->assertNull($byName['عميل فارغ']['sasUsername']);
        $this->assertNull($byName['عميل مسافة']['sasUsername']);
        $this->assertSame('user1', $byName['عميل تقليم']['sasUsername']);
        foreach ($response->json('data.clients') as $client) {
            $this->assertArrayHasKey('sasUsername', $client);
        }
    }

    public function test_clients_search_matches_sas_username_substring(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل بحث', 'search-collector@example.test');
        $target = $this->client('عميل شبكة', '0555000010', 'عنوان شبكة', 'internet', 'radwan-net');
        $this->client('عميل آخر', '0555000011', 'عنوان آخر', 'internet', 'other-user');

        $response = $this->clientsRequest($admin, ['search' => 'radwan']);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame([$target], array_column($response->json('data.clients'), 'id'));
        $this->assertSame('radwan-net', $response->json('data.clients.0.sasUsername'));
    }

    public function test_clients_sas_username_search_cannot_escape_active_deleted_scope(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل نطاق', 'scope-collector@example.test');
        $kept = $this->client('عميل نشط', '0555000020', 'عنوان نشط', 'internet', 'radwan-active');
        $inactive = $this->client('عميل موقوف', '0555000021', 'عنوان موقوف', 'internet', 'radwan-inactive');
        $deleted = $this->client('عميل محذوف', '0555000022', 'عنوان محذوف', 'internet', 'radwan-deleted');
        DB::table('tbl_clients')->where('id', $inactive)->update(['is_active' => '0']);
        DB::table('tbl_clients')->where('id', $deleted)->update(['deleted_at' => now()]);

        $response = $this->clientsRequest($admin, ['search' => 'radwan']);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame([$kept], array_column($response->json('data.clients'), 'id'));
    }

    public function test_clients_sas_username_search_composes_with_client_type_filter(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل تركيب', 'compose-collector@example.test');
        $internet = $this->client('عميل نت', '0555000030', 'عنوان نت', 'internet', 'net-user');
        $satellite = $this->client('عميل سات', '0555000031', 'عنوان سات', 'satellite', 'sat-user');

        $response = $this->clientsRequest($admin, ['search' => 'user', 'client_type' => 'satellite']);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame([$satellite], array_column($response->json('data.clients'), 'id'));
        $this->assertSame('sat-user', $response->json('data.clients.0.sasUsername'));

        $all = $this->clientsRequest($admin, ['search' => 'user']);
        $this->assertSame(2, $all->json('data.pagination.total'));
    }

    public function test_clients_list_never_calls_sas(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ساس', 'sas-free-collector@example.test');
        $this->client('عميل مرتبط', '0555000040', 'عنوان مرتبط', 'internet', 'some-sas-user');
        $sas = Mockery::mock(Sas4ApiService::class);
        $sas->shouldNotReceive('searchUsers', 'getToken', 'getUserByUsername', 'getUserById');
        $this->app->instance(Sas4ApiService::class, $sas);

        $response = $this->clientsRequest($admin);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame('some-sas-user', $response->json('data.clients.0.sasUsername'));
        $content = $response->getContent();
        $this->assertStringNotContainsString('online_status', $content);
        $this->assertStringNotContainsString('"status"', $content);
        Mockery::close();
    }

    public function test_clients_list_query_count_is_sas_username_neutral(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل استعلامات', 'queries-collector@example.test');
        for ($i = 1; $i <= 50; $i++) {
            $this->client('عميل استعلام '.$i, '0555'.str_pad((string) $i, 6, '0', STR_PAD_LEFT), 'عنوان استعلام '.$i, $i % 2 ? 'internet' : 'satellite', $i % 2 ? 'sas-user-'.$i : null);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $small = $this->clientsRequest($admin, ['per_page' => 1]);
        $small->assertOk();
        $smallQueries = count(DB::getQueryLog());

        DB::flushQueryLog();
        $large = $this->clientsRequest($admin, ['per_page' => 50]);
        $large->assertOk();
        $largeQueries = count(DB::getQueryLog());

        // The sasUsername field is a plain column already covered by
        // `select('tbl_clients.*')`: the ONLY per-page growth comes from the
        // two PRE-EXISTING per-item lookups in ClientResource
        // (get_app_config_data('currency') + the latest_invoice_due_date
        // accessor) — 49 extra items × 2 == 98. sasUsername itself adds
        // exactly zero queries, so this delta is unchanged by Feature 009.
        $this->assertSame((50 - 1) * 2, $largeQueries - $smallQueries);
        foreach ($large->json('data.clients') as $client) {
            $this->assertArrayHasKey('sasUsername', $client);
        }
    }

    // ------------------------------------------------------------- helpers

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

    private function clientsRequest(Admin $admin, array $query = []): TestResponse
    {
        $url = '/api/v1/clients';
        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $this->withToken(auth('api')->login($admin))->getJson($url);
    }
}
