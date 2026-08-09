<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppConfig;
use App\Models\Role;
use App\Services\UnpaidInvoicesSearchService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\TestCase;

/**
 * Feature 005 — GET /api/v1/unpaid-invoices (fast unpaid invoice search).
 *
 * Contract scope: active (status '1') authenticated admins only — inactive
 * accounts are denied with 403 account_inactive before any query; invoices
 * must be non-deleted, status unpaid|partial, and remaining_amount > 0
 * (zero/negative stale rows carry no money owed and are excluded).
 *
 * Same guarded migrate:fresh-once + per-test transaction pattern as
 * CollectorBalancesTest: never uses RefreshDatabase (so it cannot flip the
 * process-wide RefreshDatabaseState flag), refuses to run outside a dedicated
 * test database, and keeps every test isolated inside a rolled-back
 * transaction. The full migration chain is MySQL/MariaDB-only (raw ENUM
 * ALTER statements), so the class guard requires a dedicated MySQL test DB,
 * exactly like CollectorBalancesTest.
 */
class UnpaidInvoicesSearchTest extends TestCase
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

    // ---------------------------------------------------------------- auth

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/unpaid-invoices');

        $response
            ->assertStatus(401)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'authentication_required');
    }

    public function test_inactive_admin_is_denied_before_any_query_with_403(): void
    {
        $this->currency('$');
        $inactive = $this->admin('محصل موقوف', 'inactive-search@example.test', 'collector', '0');
        $clientId = $this->client('عميل موقوف', '0555000001', 'عنوان موقوف');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-INACTIVE-1']);

        $response = $this->authedRequest($inactive);

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'account_inactive');
        $this->assertSame([], $response->json('data'));
        $this->assertStringNotContainsString('"invoices"', $response->getContent());
    }

    public function test_inactive_admin_denial_never_invokes_the_search_service(): void
    {
        $inactive = $this->admin('محصل موقوف ب', 'inactive-b@example.test', 'collector', '0');
        $service = Mockery::mock(UnpaidInvoicesSearchService::class);
        $service->shouldNotReceive('search');
        $this->app->instance(UnpaidInvoicesSearchService::class, $service);

        $response = $this->authedRequest($inactive);

        $response->assertStatus(403)->assertJsonPath('error.code', 'account_inactive');
    }

    public function test_any_active_authenticated_admin_role_can_read_the_list(): void
    {
        $this->currency('$');
        $collector = $this->admin('محصل', 'role-collector@example.test', 'collector');
        $accounting = $this->admin('محاسب', 'role-accounting@example.test', 'accounting');
        $superAdmin = $this->admin('مدير عام', 'role-super@example.test', 'Super-Admin');
        $clientId = $this->client('عميل أدوار', '0555000002', 'عنوان أدوار');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ROLES-1']);

        foreach ([$collector, $accounting, $superAdmin] as $admin) {
            $this->authedRequest($admin)
                ->assertOk()
                ->assertJsonPath('result', true)
                ->assertJsonCount(1, 'data.invoices');
        }
    }

    // ---------------------------------------------------------- validation

    public function test_search_longer_than_100_characters_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل تحقق', 'validation-collector@example.test');

        $response = $this->authedRequest($admin, ['search' => str_repeat('a', 101)]);

        $this->assertInvalidQuery($response);
    }

    public function test_search_sent_as_array_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مصفوفة', 'array-collector@example.test');

        $response = $this->withToken(auth('api')->login($admin))
            ->getJson('/api/v1/unpaid-invoices?search[]=x');

        $this->assertInvalidQuery($response);
    }

    public function test_page_below_one_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل صفحة', 'page-collector@example.test');

        $this->assertInvalidQuery($this->authedRequest($admin, ['page' => 0]));
        $this->assertInvalidQuery($this->authedRequest($admin, ['page' => -3]));
    }

    public function test_non_numeric_page_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل صفحة نص', 'page-text-collector@example.test');

        $this->assertInvalidQuery($this->authedRequest($admin, ['page' => 'abc']));
        $this->assertInvalidQuery($this->authedRequest($admin, ['page' => '1.5']));
    }

    public function test_per_page_out_of_bounds_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل حجم', 'perpage-collector@example.test');

        $this->assertInvalidQuery($this->authedRequest($admin, ['per_page' => 0]));
        $this->assertInvalidQuery($this->authedRequest($admin, ['per_page' => 51]));
        $this->assertInvalidQuery($this->authedRequest($admin, ['per_page' => -1]));
    }

    public function test_non_numeric_per_page_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل حجم نص', 'perpage-text-collector@example.test');

        $this->assertInvalidQuery($this->authedRequest($admin, ['per_page' => 'abc']));
    }

    public function test_default_page_and_per_page_apply_when_omitted(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل افتراضي', 'defaults-collector@example.test');
        $clientId = $this->client('عميل افتراضي', '0555000003', 'عنوان افتراضي');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-DEF-1']);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.current_page'));
        $this->assertSame(25, $response->json('data.pagination.per_page'));
        $this->assertSame(1, $response->json('data.pagination.total'));
    }

    public function test_whitespace_search_behaves_as_no_search(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مسافة', 'space-collector@example.test');
        $clientId = $this->client('عميل مسافة', '0555000004', 'عنوان مسافة');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-SPACE-1']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-SPACE-2']);

        $noSearch = $this->authedRequest($admin);
        $whitespace = $this->authedRequest($admin, ['search' => '   ']);

        $noSearch->assertOk();
        $whitespace->assertOk();
        $this->assertSame(
            $noSearch->json('data.pagination.total'),
            $whitespace->json('data.pagination.total'),
        );
        $this->assertSame(2, $whitespace->json('data.pagination.total'));
    }

    // ------------------------------------------------------- scope/filters

    public function test_only_unpaid_and_partial_invoices_are_returned(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل نطاق', 'scope-collector@example.test');
        $clientId = $this->client('عميل نطاق', '0555000005', 'عنوان نطاق');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-UNPAID-1', 'status' => 'unpaid']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-PARTIAL-1', 'status' => 'partial']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-PAID-1', 'status' => 'paid']);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        $this->assertSame(2, $response->json('data.pagination.total'));
        $statuses = array_column($response->json('data.invoices'), 'status');
        $this->assertSame(['unpaid', 'partial'], $statuses);
    }

    public function test_invoices_with_zero_remaining_amount_are_excluded(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل صفر', 'zero-collector@example.test');
        $clientId = $this->client('عميل صفر', '0555000028', 'عنوان صفر');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ZERO-1', 'status' => 'unpaid', 'remaining_amount' => '0.00']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ZERO-2', 'status' => 'partial', 'remaining_amount' => '0.00']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ZERO-3', 'status' => 'unpaid', 'remaining_amount' => '100.00']);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame(['INV-ZERO-3'], array_column($response->json('data.invoices'), 'invoice_number'));
    }

    public function test_invoices_with_negative_remaining_amount_are_excluded(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل سالب', 'negative-collector@example.test');
        $clientId = $this->client('عميل سالب', '0555000029', 'عنوان سالب');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-NEG-1', 'status' => 'partial', 'remaining_amount' => '-5.00']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-NEG-2', 'status' => 'unpaid', 'remaining_amount' => '25.00']);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame(['INV-NEG-2'], array_column($response->json('data.invoices'), 'invoice_number'));
    }

    public function test_positive_remaining_unpaid_and_partial_remain_in_scope(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل موجب', 'positive-collector@example.test');
        $clientId = $this->client('عميل موجب', '0555000030', 'عنوان موجب');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-POS-1', 'status' => 'unpaid', 'remaining_amount' => '150.00']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-POS-2', 'status' => 'partial', 'remaining_amount' => '0.01']);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        $this->assertSame(2, $response->json('data.pagination.total'));
        $this->assertSame(['INV-POS-1', 'INV-POS-2'], array_column($response->json('data.invoices'), 'invoice_number'));
    }

    public function test_search_cannot_escape_the_remaining_amount_filter(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل هروب صفر', 'zero-escape-collector@example.test');
        $clientId = $this->client('عميل هروب صفر', '0555000031', 'عنوان هروب صفر');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'ZERO-TRAP-99', 'status' => 'unpaid', 'remaining_amount' => '0.00']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-OK-3', 'status' => 'unpaid', 'remaining_amount' => '50.00']);

        $response = $this->authedRequest($admin, ['search' => 'ZERO-TRAP']);

        $response->assertOk();
        $this->assertSame(0, $response->json('data.pagination.total'));
        $this->assertSame([], $response->json('data.invoices'));
    }

    public function test_soft_deleted_invoices_are_excluded(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل محذوف', 'deleted-scope-collector@example.test');
        $clientId = $this->client('عميل محذوف', '0555000006', 'عنوان محذوف');
        $kept = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-KEPT-1', 'status' => 'unpaid']);
        $deleted = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-DELETED-1', 'status' => 'unpaid']);
        DB::table('tbl_invoices')->where('id', $deleted)->update(['deleted_at' => now()]);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame([$kept], array_column($response->json('data.invoices'), 'id'));
    }

    public function test_search_matching_a_paid_invoice_cannot_escape_status_filter(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل أولوية', 'precedence-collector@example.test');
        $clientId = $this->client('عميل أولوية', '0555000007', 'عنوان أولوية');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'PAID-TRAP-77', 'status' => 'paid']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-OK-1', 'status' => 'unpaid']);

        // The OR group must stay inside the status/deleted filters: a search
        // that only matches a paid invoice number must return zero rows.
        $response = $this->authedRequest($admin, ['search' => 'PAID-TRAP']);

        $response->assertOk();
        $this->assertSame(0, $response->json('data.pagination.total'));
        $this->assertSame([], $response->json('data.invoices'));
    }

    public function test_search_matching_a_deleted_invoice_cannot_escape_deleted_filter(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل حذف أولوية', 'deleted-precedence-collector@example.test');
        $clientId = $this->client('عميل حذف أولوية', '0555000008', 'عنوان حذف أولوية');
        $deleted = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'DELETED-TRAP-88', 'status' => 'unpaid']);
        DB::table('tbl_invoices')->where('id', $deleted)->update(['deleted_at' => now()]);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-OK-2', 'status' => 'unpaid']);

        $response = $this->authedRequest($admin, ['search' => 'DELETED-TRAP']);

        $response->assertOk();
        $this->assertSame(0, $response->json('data.pagination.total'));
    }

    // ---------------------------------------------------------------- search

    public function test_search_by_invoice_number_substring(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل رقم', 'number-collector@example.test');
        $clientId = $this->client('عميل رقم', '0555000009', 'عنوان رقم');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-2026-0001']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-2026-0002']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'SRV-2026-0001']);

        $response = $this->authedRequest($admin, ['search' => '2026-0001']);

        $response->assertOk();
        $this->assertSame(2, $response->json('data.pagination.total'));
        $this->assertSame(
            ['INV-2026-0001', 'SRV-2026-0001'],
            array_column($response->json('data.invoices'), 'invoice_number'),
        );
    }

    public function test_search_by_client_name_substring(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل اسم', 'name-collector@example.test');
        $target = $this->client('أحمد بن يوسف', '0555000010', 'عنوان أحمد');
        $other = $this->client('محمد الأمين', '0555000011', 'عنوان محمد');
        $this->invoice(['client_id' => $target, 'invoice_number' => 'INV-AHMED-1']);
        $this->invoice(['client_id' => $target, 'invoice_number' => 'INV-AHMED-2']);
        $this->invoice(['client_id' => $other, 'invoice_number' => 'INV-MOHAMED-1']);

        $response = $this->authedRequest($admin, ['search' => 'أحمد']);

        $response->assertOk();
        $this->assertSame(2, $response->json('data.pagination.total'));
        $this->assertSame(
            ['INV-AHMED-1', 'INV-AHMED-2'],
            array_column($response->json('data.invoices'), 'invoice_number'),
        );
    }

    public function test_search_matches_invoice_number_or_client_name(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مجموعة', 'group-collector@example.test');
        $byName = $this->client('شركة الأمل للإنترنت', '0555000012', 'عنوان الأمل');
        $byNumber = $this->client('عميل رقمي', '0555000013', 'عنوان رقمي');
        $this->invoice(['client_id' => $byName, 'invoice_number' => 'INV-OTHER-1']);
        $this->invoice(['client_id' => $byNumber, 'invoice_number' => 'ALAMAL-2026-1']);

        $response = $this->authedRequest($admin, ['search' => 'الأمل']);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));

        $response = $this->authedRequest($admin, ['search' => 'ALAMAL']);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
    }

    public function test_search_does_not_match_phone_address_or_notes(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل خصوصية', 'privacy-collector@example.test');
        $clientId = $this->client('عميل خصوصية', '0777000014', 'شارع الاستقلال رقم 12');
        $this->invoice([
            'client_id' => $clientId,
            'invoice_number' => 'INV-PRIV-1',
            'notes' => 'ملاحظة داخلية سرية خاصة بالعميل',
        ]);

        foreach (['0777', 'الاستقلال', 'سرية'] as $term) {
            $response = $this->authedRequest($admin, ['search' => $term]);
            $response->assertOk();
            $this->assertSame(0, $response->json('data.pagination.total'), "search term {$term} must not match PII");
        }
    }

    // ----------------------------------------------------------------- order

    public function test_unpaid_sorts_before_partial_regardless_of_due_date(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ترتيب', 'order-collector@example.test');
        $clientId = $this->client('عميل ترتيب', '0555000015', 'عنوان ترتيب');
        $partialId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ORDER-P1', 'status' => 'partial', 'due_date' => '2026-01-01']);
        $unpaidId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ORDER-U1', 'status' => 'unpaid', 'due_date' => '2026-05-01']);

        $response = $this->authedRequest($admin);

        $this->assertSame([$unpaidId, $partialId], array_column($response->json('data.invoices'), 'id'));
    }

    public function test_due_date_ascending_within_same_status(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل تاريخ', 'date-collector@example.test');
        $clientId = $this->client('عميل تاريخ', '0555000016', 'عنوان تاريخ');
        $later = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-DATE-1', 'due_date' => '2026-03-01']);
        $earlier = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-DATE-2', 'due_date' => '2026-01-01']);

        $response = $this->authedRequest($admin);

        $this->assertSame([$earlier, $later], array_column($response->json('data.invoices'), 'id'));
    }

    public function test_id_ascending_tiebreak_for_equal_due_dates(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ربط', 'tie-collector@example.test');
        $clientId = $this->client('عميل ربط', '0555000017', 'عنوان ربط');
        $first = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-TIE-1', 'due_date' => '2026-02-01']);
        $second = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-TIE-2', 'due_date' => '2026-02-01']);

        $response = $this->authedRequest($admin);

        $this->assertSame([$first, $second], array_column($response->json('data.invoices'), 'id'));
    }

    // ------------------------------------------------------------ pagination

    public function test_pagination_metadata_is_authoritative_and_deterministic(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل صفحات', 'pages-collector@example.test');
        $clientId = $this->client('عميل صفحات', '0555000018', 'عنوان صفحات');
        for ($i = 1; $i <= 61; $i++) {
            $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-PAGE-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT)]);
        }

        $page1 = $this->authedRequest($admin, ['page' => 1, 'per_page' => 25]);
        $page2 = $this->authedRequest($admin, ['page' => 2, 'per_page' => 25]);
        $page3 = $this->authedRequest($admin, ['page' => 3, 'per_page' => 25]);
        $page4 = $this->authedRequest($admin, ['page' => 4, 'per_page' => 25]);

        foreach ([$page1, $page2, $page3, $page4] as $response) {
            $response->assertOk();
        }

        $this->assertSame(
            ['current_page' => 1, 'last_page' => 3, 'per_page' => 25, 'total' => 61, 'has_more' => true],
            $page1->json('data.pagination'),
        );
        $this->assertSame(25, count($page1->json('data.invoices')));
        $this->assertSame(25, count($page2->json('data.invoices')));
        $this->assertSame(11, count($page3->json('data.invoices')));
        $this->assertSame(false, $page3->json('data.pagination.has_more'));
        $this->assertSame(0, count($page4->json('data.invoices')));
        $this->assertSame(61, $page4->json('data.pagination.total'));

        // No duplicates and no omissions across pages.
        $ids = array_merge(
            array_column($page1->json('data.invoices'), 'id'),
            array_column($page2->json('data.invoices'), 'id'),
            array_column($page3->json('data.invoices'), 'id'),
        );
        $this->assertSame(61, count($ids));
        $this->assertSame(61, count(array_unique($ids)));
    }

    public function test_pagination_total_reflects_the_search_filter(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل بحث صفحات', 'search-pages-collector@example.test');
        $clientId = $this->client('عميل بحث صفحات', '0555000019', 'عنوان بحث صفحات');
        for ($i = 1; $i <= 8; $i++) {
            $this->invoice(['client_id' => $clientId, 'invoice_number' => 'TARGET-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT)]);
        }
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'OTHER-0001']);

        $response = $this->authedRequest($admin, ['search' => 'TARGET', 'per_page' => 3]);

        $response->assertOk();
        $this->assertSame(
            ['current_page' => 1, 'last_page' => 3, 'per_page' => 3, 'total' => 8, 'has_more' => true],
            $response->json('data.pagination'),
        );
    }

    public function test_per_page_boundaries_are_accepted(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل حدود', 'bounds-collector@example.test');
        $clientId = $this->client('عميل حدود', '0555000020', 'عنوان حدود');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-BOUND-1']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-BOUND-2']);

        $one = $this->authedRequest($admin, ['per_page' => 1]);
        $fifty = $this->authedRequest($admin, ['per_page' => 50]);

        $one->assertOk()->assertJsonCount(1, 'data.invoices');
        $fifty->assertOk()->assertJsonCount(2, 'data.invoices');
        $this->assertSame(50, $fifty->json('data.pagination.per_page'));
    }

    // -------------------------------------------------------------- contract

    public function test_response_exposes_exact_contract_keys(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مفاتيح', 'keys-collector@example.test');
        $clientId = $this->client('عميل مفاتيح', '0555000021', 'عنوان مفاتيح');
        $this->invoice([
            'client_id' => $clientId,
            'invoice_number' => 'INV-KEYS-1',
            'amount' => '15420.50',
            'remaining_amount' => '75.00',
            'due_date' => '2026-02-15',
            'status' => 'partial',
            'invoice_type' => 'service',
        ]);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        // Shared mobile ApiEnvelope contract: success is `result: true` (the
        // boolean `status` key is NOT part of the envelope and must be absent).
        $this->assertSame(['result', 'data'], array_keys($response->json()));
        $this->assertSame(['invoices', 'currency', 'pagination'], array_keys($response->json('data')));
        $this->assertSame(
            ['id', 'invoice_number', 'client_id', 'client_name', 'invoice_type', 'status', 'amount', 'remaining_amount', 'due_date', 'notes'],
            array_keys($response->json('data.invoices.0')),
        );
        // Feature 008: the nullable notes key is always present, never a
        // missing key, and is null when the stored invoice note is blank.
        $this->assertNull($response->json('data.invoices.0.notes'));
        $this->assertSame(
            ['current_page', 'last_page', 'per_page', 'total', 'has_more'],
            array_keys($response->json('data.pagination')),
        );
    }

    public function test_money_values_are_exact_two_decimal_strings(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل نقود', 'money-collector@example.test');
        $clientId = $this->client('عميل نقود', '0555000022', 'عنوان نقود');
        $this->invoice([
            'client_id' => $clientId,
            'invoice_number' => 'INV-MONEY-1',
            'amount' => '15420.50',
            'remaining_amount' => '15420.50',
        ]);
        $this->invoice([
            'client_id' => $clientId,
            'invoice_number' => 'INV-MONEY-2',
            'amount' => '12345678.90',
            'remaining_amount' => '25.50',
        ]);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        foreach ($response->json('data.invoices') as $invoice) {
            $this->assertIsString($invoice['amount']);
            $this->assertIsString($invoice['remaining_amount']);
            $this->assertMatchesRegularExpression('/^-?[0-9]{1,8}\.[0-9]{2}$/', $invoice['amount']);
            $this->assertMatchesRegularExpression('/^-?[0-9]{1,8}\.[0-9]{2}$/', $invoice['remaining_amount']);
        }
        $this->assertSame('15420.50', $response->json('data.invoices.0.amount'));
        $this->assertSame('25.50', $response->json('data.invoices.1.remaining_amount'));
    }

    public function test_currency_is_top_level_non_empty_and_not_repeated_in_items(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل عملة', 'currency-collector@example.test');
        $clientId = $this->client('عميل عملة', '0555000023', 'عنوان عملة');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-CUR-1']);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        $this->assertSame('$', $response->json('data.currency'));
        $this->assertArrayNotHasKey('currency', $response->json('data.invoices.0'));
    }

    public function test_invoice_status_values_are_only_unpaid_or_partial(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل حالات', 'statuses-collector@example.test');
        $clientId = $this->client('عميل حالات', '0555000024', 'عنوان حالات');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ST-1', 'status' => 'unpaid']);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-ST-2', 'status' => 'partial']);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        foreach ($response->json('data.invoices') as $invoice) {
            $this->assertContains($invoice['status'], ['unpaid', 'partial']);
        }
    }

    public function test_response_contains_no_pii_beyond_client_name_and_invoice_notes(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل بيانات حساسة', 'pii-collector@example.test');
        $clientId = $this->client('عميل بيانات حساسة', '0777000025', 'شارع خاص بالعميل 25');
        $this->invoice([
            'client_id' => $clientId,
            'invoice_number' => 'INV-PII-1',
            'notes' => 'ملاحظة العداد تظهر بحكم العقد الجديد',
        ]);

        $response = $this->authedRequest($admin);

        $response->assertOk();
        $content = $response->getContent();
        // Feature 008 contract: the invoice's own note is now part of the row
        // (plain stored text) — it must appear exactly as stored. All other
        // PII (phone, address, email, collector/account data) stays forbidden.
        $this->assertSame('ملاحظة العداد تظهر بحكم العقد الجديد', $response->json('data.invoices.0.notes'));
        $forbidden = ['0777000025', 'شارع خاص بالعميل 25', 'pii-collector@example.test'];
        foreach ($forbidden as $needle) {
            $this->assertStringNotContainsString($needle, $content);
        }
        foreach (['phone', 'address1', 'address2', 'email', 'collected_by', 'collector', 'account', 'paid_amount'] as $key) {
            $this->assertStringNotContainsString('"'.$key.'"', $content);
        }
    }

    // --------------------------------------------------------- error handling

    public function test_missing_currency_configuration_returns_safe_500(): void
    {
        $admin = $this->admin('محصل بلا عملة', 'no-currency-collector@example.test');
        $clientId = $this->client('عميل بلا عملة', '0555000026', 'عنوان بلا عملة');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-NOCUR-1']);

        $response = $this->authedRequest($admin);

        $response
            ->assertStatus(500)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'unpaid_invoices_internal_error')
            ->assertJsonStructure(['error' => ['correlation_id']]);
        $this->assertStringNotContainsString('Currency configuration is missing', $response->getContent());
    }

    public function test_internal_service_failure_returns_safe_envelope_without_exception_detail(): void
    {
        $admin = $this->admin('محصل خطأ', 'error-collector@example.test');
        $service = Mockery::mock(UnpaidInvoicesSearchService::class);
        $service->shouldReceive('search')->once()->andThrow(new \RuntimeException('sensitive-detail-xyz'));
        $this->app->instance(UnpaidInvoicesSearchService::class, $service);

        $response = $this->authedRequest($admin);

        $response
            ->assertStatus(500)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'unpaid_invoices_internal_error')
            ->assertJsonStructure(['error' => ['correlation_id']]);
        $this->assertStringNotContainsString('sensitive-detail-xyz', $response->getContent());
    }

    // ---------------------------------------------------------- query count

    public function test_query_count_is_bounded_and_does_not_scale_with_page_size(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل استعلامات', 'queries-collector@example.test');
        $clientId = $this->client('عميل استعلامات', '0555000027', 'عنوان استعلامات');
        for ($i = 1; $i <= 30; $i++) {
            $this->invoice([
                'client_id' => $clientId,
                'invoice_number' => 'QC-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'status' => $i % 2 === 0 ? 'partial' : 'unpaid',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $small = $this->authedRequest($admin, ['per_page' => 1]);
        $small->assertOk();
        $smallQueries = count(DB::getQueryLog());

        DB::flushQueryLog();
        $large = $this->authedRequest($admin, ['per_page' => 50]);
        $large->assertOk();
        $largeQueries = count(DB::getQueryLog());

        // The page-size invariant is the N+1 proof: doubling the rows on the
        // page must never add queries (count + page + currency, no per-row
        // lookups). The hard cap keeps accidental growth visible.
        $this->assertSame($smallQueries, $largeQueries, 'Query count must not scale with page size (N+1).');
        $this->assertLessThanOrEqual(4, $largeQueries);
        $this->assertSame(30, $large->json('data.pagination.total'));
    }

    // --------------------------------------------------------------- helpers

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
            'paid_amount' => '0.00',
            'remaining_amount' => '100.00',
            'enshaa_date' => '2026-01-01',
            'due_date' => '2026-02-01',
            'status' => 'unpaid',
            'invoice_type' => 'subscription',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        return DB::table('tbl_invoices')->insertGetId($data);
    }

    private function authedRequest(Admin $admin, array $query = []): TestResponse
    {
        $url = '/api/v1/unpaid-invoices';
        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $this->withToken(auth('api')->login($admin))->getJson($url);
    }

    private function assertInvalidQuery(TestResponse $response): void
    {
        $response
            ->assertStatus(422)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'invalid_query_parameters');
    }
}
