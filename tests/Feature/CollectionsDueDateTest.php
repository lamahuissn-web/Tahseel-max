<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppConfig;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Feature 006 — GET /api/v1/collections due_date contract.
 *
 * The collection-event card redesign requires each item to expose a minimal
 * date-only `due_date` (YYYY-MM-DD) sourced ONLY from the already
 * eager-loaded `invoice` relation. No extra query, no schema change, and no
 * payment/write behavior change. Missing invoice or missing due_date must map
 * to null safely — never an invented date.
 *
 * Same guarded migrate:fresh-once + per-test transaction pattern as
 * UnpaidInvoicesSearchTest / CollectorBalancesTest: never uses RefreshDatabase
 * (so it cannot flip the process-wide RefreshDatabaseState flag), refuses to
 * run outside a dedicated test database, and keeps every test isolated inside
 * a rolled-back transaction. The migration chain is MySQL/MariaDB-only (raw
 * ENUM ALTER statements), hence the MySQL test-DB guard.
 */
class CollectionsDueDateTest extends TestCase
{
    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! str_contains((string) config('database.connections.'.config('database.default').'.database'), '_test')) {
            throw new \RuntimeException('Refusing migrate:fresh outside a dedicated test database.');
        }

        if (! self::$migrated) {
            Artisan::call('migrate:fresh', ['--force' => true]);
            self::$migrated = true;
        }

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    // ----------------------------------------------------------- auth guard

    public function test_collections_api_rejects_requests_without_a_jwt(): void
    {
        $response = $this->getJson('/api/v1/collections');

        $response->assertJsonPath('message', 'Token not found');
        $this->assertNotTrue($response->json('result'));
    }

    // ------------------------------------------------------------- due_date

    public function test_each_collection_item_exposes_due_date_from_its_invoice(): void
    {
        $this->currency('$');
        $collector = $this->admin('محصل استحقاق', 'due-date-collector@example.test');
        $clientId = $this->client('عميل استحقاق', '0555000101', 'عنوان استحقاق');
        $invoiceId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-DUE-1', 'due_date' => '2026-02-01']);
        $this->revenue([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $collector->id,
            'amount' => '25.00',
            'received_at' => '2026-07-20 10:00:00',
        ]);

        $response = $this->collectionsRequest($collector, ['start_date' => '2026-07-01', 'end_date' => '2026-07-31']);

        $response->assertOk()->assertJsonPath('result', true);
        $this->assertSame('2026-02-01', $response->json('data.collections.0.due_date'));
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            (string) $response->json('data.collections.0.due_date'),
            'due_date must be strict date-only YYYY-MM-DD.',
        );
    }

    public function test_collection_item_exposes_the_exact_contract_keys(): void
    {
        $this->currency('$');
        $collector = $this->admin('محصل مفاتيح', 'keys-collector@example.test');
        $clientId = $this->client('عميل مفاتيح', '0555000102', 'عنوان مفاتيح');
        $invoiceId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-KEYS-1', 'due_date' => '2026-02-01']);
        $this->revenue([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $collector->id,
            'amount' => '25.00',
            'received_at' => '2026-07-20 10:00:00',
        ]);

        $response = $this->collectionsRequest($collector, ['start_date' => '2026-07-01', 'end_date' => '2026-07-31']);

        $response->assertOk()->assertJsonPath('result', true);
        $this->assertSame([
            'id',
            'reference',
            'invoice_id',
            'invoice_number',
            'client_id',
            'client_name',
            'amount',
            'remaining_amount',
            'received_at',
            'collected_by',
            'notes',
            'currency',
            'due_date',
        ], array_keys($response->json('data.collections.0')));
    }

    public function test_dangling_invoice_id_produces_null_due_date_safely(): void
    {
        $this->currency('$');
        $collector = $this->admin('محصل بدون فاتورة', 'no-invoice-collector@example.test');
        $clientId = $this->client('عميل بدون فاتورة', '0555000103', 'عنوان بدون فاتورة');
        // invoice_id intentionally points at a row that does not exist: the
        // relation is null, so due_date (and invoice_number) must be null.
        $this->revenue([
            'invoice_id' => 999999,
            'client_id' => $clientId,
            'collected_by' => $collector->id,
            'amount' => '25.00',
            'received_at' => '2026-07-20 10:00:00',
        ]);

        $response = $this->collectionsRequest($collector, ['start_date' => '2026-07-01', 'end_date' => '2026-07-31']);

        $response->assertOk()->assertJsonPath('result', true);
        $this->assertNull($response->json('data.collections.0.due_date'));
        $this->assertNull($response->json('data.collections.0.invoice_number'));
    }

    public function test_invoice_without_due_date_produces_null_safely(): void
    {
        $this->currency('$');
        $collector = $this->admin('محصل بلا تاريخ', 'no-due-collector@example.test');
        $clientId = $this->client('عميل بلا تاريخ', '0555000104', 'عنوان بلا تاريخ');
        $invoiceId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-NODUE-1', 'due_date' => null]);
        $this->revenue([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $collector->id,
            'amount' => '25.00',
            'received_at' => '2026-07-20 10:00:00',
        ]);

        $response = $this->collectionsRequest($collector, ['start_date' => '2026-07-01', 'end_date' => '2026-07-31']);

        $response->assertOk()->assertJsonPath('result', true);
        $this->assertNull($response->json('data.collections.0.due_date'));
    }

    // ------------------------------------------- scope / order / pagination

    public function test_collector_scope_summary_and_envelope_are_unchanged(): void
    {
        $this->currency('$');
        $collector = $this->admin('محصل أ', 'scope-a@example.test');
        $other = $this->admin('محصل ب', 'scope-b@example.test');
        $clientId = $this->client('عميل النطاق', '0555000105', 'عنوان النطاق');
        $invoiceId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-SCOPE-1', 'due_date' => '2026-02-01']);

        $ownedRevenueId = $this->revenue([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $collector->id,
            'amount' => '25.00',
            'received_at' => '2026-07-20 10:00:00',
        ]);
        $this->revenue([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $other->id,
            'amount' => '75.00',
            'received_at' => '2026-07-21 10:00:00',
        ]);

        $response = $this->collectionsRequest($collector, ['start_date' => '2026-07-01', 'end_date' => '2026-07-31']);

        $response->assertOk()->assertJsonPath('result', true);
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame(1, $response->json('data.summary.count'));
        $this->assertSame('25.00', $response->json('data.summary.total_amount'));
        $this->assertSame('$', $response->json('data.summary.currency'));
        $this->assertSame('2026-07-01', $response->json('data.summary.start_date'));
        $this->assertSame('2026-07-31', $response->json('data.summary.end_date'));
        $this->assertStringNotContainsString('"75.00"', $response->getContent());
        // The owned collection event itself is the one returned first (the
        // foreign collector's revenue must never leak into the list).
        $this->assertSame($ownedRevenueId, $response->json('data.collections.0.id'));
    }

    public function test_ordering_and_pagination_are_unchanged(): void
    {
        $this->currency('$');
        $collector = $this->admin('محصل ترتيب', 'order-collector@example.test');
        $clientId = $this->client('عميل الترتيب', '0555000106', 'عنوان الترتيب');
        $invoiceId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ORDER-1', 'due_date' => '2026-02-01']);

        $first = $this->revenue([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $collector->id,
            'amount' => '25.00',
            'received_at' => '2026-07-20 10:00:00',
        ]);
        $second = $this->revenue([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $collector->id,
            'amount' => '30.00',
            'received_at' => '2026-07-22 12:00:00',
        ]);

        $response = $this->collectionsRequest($collector, [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'per_page' => 1,
        ]);

        $response->assertOk()->assertJsonPath('result', true);
        // received_at DESC, id DESC: the newest row must come first.
        $this->assertSame($second, $response->json('data.collections.0.id'));
        $this->assertSame('2026-07-22 12:00:00', $response->json('data.collections.0.received_at'));
        $this->assertSame(2, $response->json('data.pagination.total'));
        $this->assertSame(1, $response->json('data.pagination.per_page'));
        $this->assertTrue($response->json('data.pagination.has_more'));
        $this->assertSame(2, $response->json('data.summary.count'));
        $this->assertSame('55.00', $response->json('data.summary.total_amount'));

        $pageTwo = $this->collectionsRequest($collector, [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'per_page' => 1,
            'page' => 2,
        ]);
        $pageTwo->assertOk()->assertJsonPath('result', true);
        $this->assertSame($first, $pageTwo->json('data.collections.0.id'));
        $this->assertSame(2, $pageTwo->json('data.pagination.current_page'));
        $this->assertFalse($pageTwo->json('data.pagination.has_more'));
    }

    public function test_missing_currency_configuration_yields_null_currency_safely(): void
    {
        // No AppConfig currency row: the hoisted lookup returns null and both
        // the item and the summary must carry null (pre-existing semantics,
        // unchanged by the single-lookup refactor).
        $collector = $this->admin('محصل بلا عملة', 'no-currency-collector@example.test');
        $clientId = $this->client('عميل بلا عملة', '0555000108', 'عنوان بلا عملة');
        $invoiceId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-NOCUR-1', 'due_date' => '2026-02-01']);
        $this->revenue([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $collector->id,
            'amount' => '25.00',
            'received_at' => '2026-07-20 10:00:00',
        ]);

        $response = $this->collectionsRequest($collector, ['start_date' => '2026-07-01', 'end_date' => '2026-07-31']);

        $response->assertOk()->assertJsonPath('result', true);
        $this->assertNull($response->json('data.collections.0.currency'));
        $this->assertNull($response->json('data.summary.currency'));
    }

    // ---------------------------------------------------------- query count

    public function test_query_count_does_not_grow_with_page_size_and_remains_bounded(): void
    {
        $this->currency('$');
        $collector = $this->admin('محصل استعلامات', 'queries-collector@example.test');
        $clientId = $this->client('عميل استعلامات', '0555000107', 'عنوان استعلامات');
        $invoiceId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-QC-1', 'due_date' => '2026-02-01']);
        for ($i = 1; $i <= 50; $i++) {
            $this->revenue([
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'collected_by' => $collector->id,
                'amount' => sprintf('%d.00', $i),
                'received_at' => '2026-07-'.str_pad((string) (($i % 28) + 1), 2, '0', STR_PAD_LEFT).' 10:00:00',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $small = $this->collectionsRequest($collector, [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'per_page' => 1,
        ]);
        $small->assertOk();
        $smallQueries = count(DB::getQueryLog());

        DB::flushQueryLog();
        $large = $this->collectionsRequest($collector, [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'per_page' => 50,
        ]);
        $large->assertOk();
        $largeQueries = count(DB::getQueryLog());

        // The page-size invariant is the N+1 proof: growing the page from 1 to
        // 50 rows must never add a single query (count + page + eager loads +
        // ONE hoisted currency lookup, no per-row lookups). The hard cap keeps
        // accidental growth visible. due_date adds zero per-row queries because
        // it reads the already eager-loaded invoice relation.
        $this->assertSame($smallQueries, $largeQueries, 'Query count must not grow with page size (N+1).');
        $this->assertLessThanOrEqual(12, $smallQueries);
        $this->assertSame(50, $large->json('data.pagination.total'));
    }

    // --------------------------------------------------------------- helpers

    private function currency(string $value): void
    {
        AppConfig::create(['key' => 'currency', 'value' => $value]);
    }

    private function admin(string $name, string $email): Admin
    {
        return Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('test-password'),
            'status' => '1',
        ]);
    }

    private function client(string $name, string $phone, string $address): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name' => $name,
            'phone' => $phone,
            'address1' => $address,
            'subscription_id' => 1,
            'price' => 100,
            'subscription_date' => '2026-01-01',
            'start_date' => '2026-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function invoice(array $overrides = []): int
    {
        $data = array_merge([
            'invoice_number' => 'INV-'.strtoupper(uniqid()),
            'client_id' => 1,
            'subscription_id' => 1,
            'amount' => '100.00',
            'paid_amount' => '100.00',
            'remaining_amount' => '0.00',
            'enshaa_date' => '2026-01-01',
            'due_date' => '2026-02-01',
            'status' => 'paid',
            'invoice_type' => 'subscription',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        return DB::table('tbl_invoices')->insertGetId($data);
    }

    private function revenue(array $overrides = []): int
    {
        $data = array_merge([
            'amount' => '25.00',
            'invoice_id' => 1,
            'client_id' => 1,
            'collected_by' => 1,
            'received_at' => '2026-07-20 10:00:00',
            'status' => 'paid',
            'remaining_amount' => '0.00',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        return DB::table('tbl_revenues')->insertGetId($data);
    }

    private function collectionsRequest(Admin $admin, array $query = []): TestResponse
    {
        $url = '/api/v1/collections';
        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $this->withToken(auth('api')->login($admin))->getJson($url);
    }
}
