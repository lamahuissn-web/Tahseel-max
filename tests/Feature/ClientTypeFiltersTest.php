<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppConfig;
use App\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Feature 008 — optional exact client_type filter (internet|satellite) on
 * GET /api/v1/clients and GET /api/v1/unpaid-invoices, plus minimal nullable
 * invoice notes on unpaid rows.
 *
 * Contract:
 *  - accepted nonblank client_type values are exactly 'internet'/'satellite';
 *    absent/blank means all; arrays/unknown/overlong are rejected with 422 and
 *    the existing safe envelope of each endpoint
 *  - server-side filtering happens before pagination/count/order and composes
 *    with grouped search and all auth/financial/deleted scope
 *  - unpaid rows add a nullable 'notes' key sourced only from tbl_invoices.notes,
 *    blank normalized to null, stored text preserved (no HTML transformation),
 *    no revenue/account PII
 *
 * Same guarded migrate:fresh-once + per-test transaction pattern as
 * UnpaidInvoicesSearchTest (never RefreshDatabase, dedicated MySQL test DB
 * only).
 */
class ClientTypeFiltersTest extends TestCase
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

    // ============================================================ clients

    public function test_clients_internet_filter_applies_before_pagination_and_count(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل إنترنت', 'internet-filter@example.test');
        foreach (['أحمد', 'محمد', 'خالد'] as $name) {
            $this->client($name, '0555'.random_int(100000, 999999), 'عنوان '.$name, 'internet');
        }
        foreach (['سارة', 'ليلى'] as $name) {
            $this->client($name, '0555'.random_int(100000, 999999), 'عنوان '.$name, 'satellite');
        }

        $response = $this->clientsRequest($admin, ['client_type' => 'internet', 'per_page' => 2]);

        $response->assertOk();
        $this->assertSame(3, $response->json('data.pagination.total'), 'Filter must apply before the count.');
        $this->assertSame(2, $response->json('data.pagination.last_page'), 'Filter must apply before pagination.');
        $this->assertSame(2, count($response->json('data.clients')));
        foreach ($response->json('data.clients') as $client) {
            $this->assertSame('internet', $client['clientType']);
        }
    }

    public function test_clients_satellite_filter_returns_only_satellite(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ستلايت', 'satellite-filter@example.test');
        $this->client('أحمد', '0555000101', 'عنوان أحمد', 'internet');
        $sat1 = $this->client('سارة', '0555000102', 'عنوان سارة', 'satellite');
        $sat2 = $this->client('ليلى', '0555000103', 'عنوان ليلى', 'satellite');

        $response = $this->clientsRequest($admin, ['client_type' => 'satellite']);

        $response->assertOk();
        $this->assertSame(2, $response->json('data.pagination.total'));
        // Ordering is created_at desc, id desc — same-second inserts make the
        // id order reverse insertion, so compare as a set, not a sequence.
        $ids = array_column($response->json('data.clients'), 'id');
        sort($ids);
        $this->assertSame([$sat1, $sat2], $ids);
        foreach ($response->json('data.clients') as $client) {
            $this->assertSame('satellite', $client['clientType']);
        }
    }

    public function test_clients_absent_and_blank_client_type_returns_all(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل الكل', 'all-clients@example.test');
        $this->client('أحمد', '0555000201', 'عنوان أحمد', 'internet');
        $this->client('سارة', '0555000202', 'عنوان سارة', 'satellite');

        $noParam = $this->clientsRequest($admin);
        $blank = $this->clientsRequest($admin, ['client_type' => '']);
        $whitespace = $this->clientsRequest($admin, ['client_type' => '   ']);

        foreach ([$noParam, $blank, $whitespace] as $response) {
            $response->assertOk();
            $this->assertSame(2, $response->json('data.pagination.total'));
        }
    }

    public function test_clients_unknown_client_type_returns_422_with_safe_envelope(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل قيمة', 'unknown-clients@example.test');

        $response = $this->clientsRequest($admin, ['client_type' => 'cable']);

        $response
            ->assertStatus(422)
            ->assertJsonPath('result', false);
        $this->assertSame(['result', 'message', 'data'], array_keys($response->json()));
        $this->assertSame([], $response->json('data'));
    }

    public function test_clients_array_client_type_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مصفوفة', 'array-clients@example.test');

        $response = $this->withToken(auth('api')->login($admin))
            ->getJson('/api/v1/clients?client_type[]=internet');

        $response
            ->assertStatus(422)
            ->assertJsonPath('result', false);
    }

    public function test_clients_overlong_client_type_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل طويل', 'long-clients@example.test');

        $response = $this->clientsRequest($admin, ['client_type' => str_repeat('a', 17)]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('result', false);
    }

    public function test_clients_filter_composes_with_search_before_count(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل بحث', 'search-clients@example.test');
        $this->client('أحمد الأول', '0555000301', 'عنوان أحمد', 'internet');
        $this->client('محمد الثاني', '0555000302', 'عنوان محمد', 'internet');
        $this->client('أحمد السات', '0555000303', 'عنوان أحمد', 'satellite');

        $filtered = $this->clientsRequest($admin, ['search' => 'أحمد', 'client_type' => 'internet']);
        $unfiltered = $this->clientsRequest($admin, ['search' => 'أحمد']);

        $filtered->assertOk();
        $this->assertSame(1, $filtered->json('data.pagination.total'));
        $this->assertSame(['أحمد الأول'], array_column($filtered->json('data.clients'), 'name'));
        $this->assertSame(2, $unfiltered->json('data.pagination.total'));
    }

    public function test_clients_filter_keeps_active_and_deleted_scope(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل نطاق', 'scope-clients@example.test');
        $kept = $this->client('نشط', '0555000401', 'عنوان نشط', 'internet');
        $inactive = $this->client('موقوف', '0555000402', 'عنوان موقوف', 'internet');
        $deleted = $this->client('محذوف', '0555000403', 'عنوان محذوف', 'internet');
        $this->client('سات نشط', '0555000404', 'عنوان سات', 'satellite');
        DB::table('tbl_clients')->where('id', $inactive)->update(['is_active' => '0']);
        DB::table('tbl_clients')->where('id', $deleted)->update(['deleted_at' => now()]);

        $response = $this->clientsRequest($admin, ['client_type' => 'internet']);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame([$kept], array_column($response->json('data.clients'), 'id'));
    }

    public function test_clients_response_never_contains_notes_or_invoice_note_text(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ملاحظات', 'notes-clients@example.test');
        $clientId = $this->client('أحمد', '0555000501', 'عنوان أحمد', 'internet');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-NOTES-CLIENT-1', 'notes' => 'ملاحظة داخلية سرية للعميل']);

        $response = $this->clientsRequest($admin, ['client_type' => 'internet']);

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringNotContainsString('ملاحظة داخلية سرية للعميل', $content);
        $this->assertStringNotContainsString('"notes"', $content);
    }

    public function test_clients_endpoint_requires_authentication_even_with_filter(): void
    {
        $response = $this->getJson('/api/v1/clients?client_type=internet');

        $response
            ->assertStatus(401)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'authentication_required');
    }

    // ============================================================== unpaid

    public function test_unpaid_internet_filter_applies_before_pagination_and_count(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل إنترنت', 'internet-unpaid@example.test');
        $internetA = $this->client('أحمد', '0555000601', 'عنوان أحمد', 'internet');
        $internetB = $this->client('محمد', '0555000602', 'عنوان محمد', 'internet');
        $satellite = $this->client('سارة', '0555000603', 'عنوان سارة', 'satellite');
        $this->invoice(['client_id' => $internetA, 'invoice_number' => 'INV-INT-1']);
        $this->invoice(['client_id' => $internetB, 'invoice_number' => 'INV-INT-2']);
        $this->invoice(['client_id' => $satellite, 'invoice_number' => 'INV-SAT-1']);

        $response = $this->unpaidRequest($admin, ['client_type' => 'internet', 'per_page' => 1]);

        $response->assertOk();
        $this->assertSame(2, $response->json('data.pagination.total'), 'Filter must apply before the count.');
        $this->assertSame(2, $response->json('data.pagination.last_page'), 'Filter must apply before pagination.');
        $this->assertSame(['INV-INT-1'], array_column($response->json('data.invoices'), 'invoice_number'));
    }

    public function test_unpaid_satellite_filter_returns_only_satellite_invoices(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ستلايت', 'satellite-unpaid@example.test');
        $internet = $this->client('أحمد', '0555000701', 'عنوان أحمد', 'internet');
        $satellite = $this->client('سارة', '0555000702', 'عنوان سارة', 'satellite');
        $this->invoice(['client_id' => $internet, 'invoice_number' => 'INV-INT-3']);
        $satInv = $this->invoice(['client_id' => $satellite, 'invoice_number' => 'INV-SAT-2']);

        $response = $this->unpaidRequest($admin, ['client_type' => 'satellite']);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame([$satInv], array_column($response->json('data.invoices'), 'id'));
    }

    public function test_unpaid_absent_and_blank_client_type_returns_all(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل الكل', 'all-unpaid@example.test');
        $internet = $this->client('أحمد', '0555000801', 'عنوان أحمد', 'internet');
        $satellite = $this->client('سارة', '0555000802', 'عنوان سارة', 'satellite');
        $this->invoice(['client_id' => $internet, 'invoice_number' => 'INV-ALL-1']);
        $this->invoice(['client_id' => $satellite, 'invoice_number' => 'INV-ALL-2']);

        $noParam = $this->unpaidRequest($admin);
        $blank = $this->unpaidRequest($admin, ['client_type' => '']);
        $whitespace = $this->unpaidRequest($admin, ['client_type' => '   ']);

        foreach ([$noParam, $blank, $whitespace] as $response) {
            $response->assertOk();
            $this->assertSame(2, $response->json('data.pagination.total'));
        }
    }

    public function test_unpaid_unknown_client_type_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل قيمة', 'unknown-unpaid@example.test');

        $this->assertInvalidQuery($this->unpaidRequest($admin, ['client_type' => 'cable']));
    }

    public function test_unpaid_array_client_type_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مصفوفة', 'array-unpaid@example.test');

        $response = $this->withToken(auth('api')->login($admin))
            ->getJson('/api/v1/unpaid-invoices?client_type[]=internet');

        $this->assertInvalidQuery($response);
    }

    public function test_unpaid_overlong_client_type_returns_422(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل طويل', 'long-unpaid@example.test');

        $this->assertInvalidQuery($this->unpaidRequest($admin, ['client_type' => str_repeat('a', 17)]));
    }

    public function test_unpaid_filter_composes_with_search_and_financial_scope(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل تركيب', 'compose-unpaid@example.test');
        $satellite = $this->client('شركة السات', '0555000901', 'عنوان السات', 'satellite');
        $internet = $this->client('شركة النت', '0555000902', 'عنوان النت', 'internet');
        $this->invoice(['client_id' => $satellite, 'invoice_number' => 'TRAP-2026-001']);
        $this->invoice(['client_id' => $satellite, 'invoice_number' => 'TRAP-2026-002', 'remaining_amount' => '0.00']);
        $this->invoice(['client_id' => $internet, 'invoice_number' => 'TRAP-2026-003', 'status' => 'paid']);
        $this->invoice(['client_id' => $internet, 'invoice_number' => 'INV-OTHER-1']);

        $response = $this->unpaidRequest($admin, ['search' => 'TRAP', 'client_type' => 'satellite']);

        $response->assertOk();
        // Only the satellite unpaid invoice with positive remaining amount:
        // the zero-remaining row and the paid internet row stay excluded even
        // though the search term matches them.
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame(['TRAP-2026-001'], array_column($response->json('data.invoices'), 'invoice_number'));
    }

    public function test_unpaid_filter_composes_with_deleted_scope(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل حذف', 'deleted-unpaid@example.test');
        $internet = $this->client('أحمد', '0555001001', 'عنوان أحمد', 'internet');
        $kept = $this->invoice(['client_id' => $internet, 'invoice_number' => 'INV-KEEP-1']);
        $deleted = $this->invoice(['client_id' => $internet, 'invoice_number' => 'INV-GONE-1']);
        DB::table('tbl_invoices')->where('id', $deleted)->update(['deleted_at' => now()]);

        $response = $this->unpaidRequest($admin, ['client_type' => 'internet']);

        $response->assertOk();
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame([$kept], array_column($response->json('data.invoices'), 'id'));
    }

    public function test_unpaid_inactive_admin_is_denied_before_any_query_with_filter_present(): void
    {
        $this->currency('$');
        $inactive = $this->admin('محصل موقوف', 'inactive-unpaid@example.test', 'collector', '0');
        $clientId = $this->client('عميل موقوف', '0555001101', 'عنوان موقوف', 'internet');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-INACTIVE-2']);

        $response = $this->unpaidRequest($inactive, ['client_type' => 'internet']);

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'account_inactive');
        $this->assertSame([], $response->json('data'));
        $this->assertStringNotContainsString('"invoices"', $response->getContent());
    }

    // ------------------------------------------------------------- notes

    public function test_unpaid_notes_key_is_present_nullable_and_plain_text(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ملاحظات', 'notes-unpaid@example.test');
        $clientId = $this->client('عميل ملاحظات', '0555001201', 'عنوان ملاحظات', 'internet');
        $withNotes = $this->invoice([
            'client_id' => $clientId,
            'invoice_number' => 'INV-NOTES-1',
            'notes' => 'يرجى مراجعة العداد قبل الزيارة',
        ]);
        $withoutNotes = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-NOTES-2', 'notes' => null]);

        $response = $this->unpaidRequest($admin);

        $response->assertOk();
        $byNumber = collect($response->json('data.invoices'))->keyBy('invoice_number');
        $this->assertSame('يرجى مراجعة العداد قبل الزيارة', $byNumber['INV-NOTES-1']['notes']);
        $this->assertNull($byNumber['INV-NOTES-2']['notes']);
        $this->assertSame([$withNotes, $withoutNotes], array_column($response->json('data.invoices'), 'id'));
    }

    public function test_unpaid_blank_and_whitespace_notes_normalize_to_null(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل فراغ', 'blank-notes@example.test');
        $clientId = $this->client('عميل فراغ', '0555001301', 'عنوان فراغ', 'internet');
        $blank = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-BLANK-1', 'notes' => '']);
        $space = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-BLANK-2', 'notes' => '   ']);

        $response = $this->unpaidRequest($admin);

        $response->assertOk();
        $byNumber = collect($response->json('data.invoices'))->keyBy('invoice_number');
        $this->assertNull($byNumber['INV-BLANK-1']['notes']);
        $this->assertNull($byNumber['INV-BLANK-2']['notes']);
        $this->assertSame([$blank, $space], array_column($response->json('data.invoices'), 'id'));
    }

    public function test_unpaid_notes_are_preserved_as_plain_stored_text_no_html_transformation(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل نص', 'html-notes@example.test');
        $clientId = $this->client('عميل نص', '0555001401', 'عنوان نص', 'internet');
        $this->invoice([
            'client_id' => $clientId,
            'invoice_number' => 'INV-HTML-1',
            'notes' => '<b>هام</b> & "اقتباس" \'واحد\'',
        ]);

        $response = $this->unpaidRequest($admin);

        $response->assertOk();
        $this->assertSame(
            '<b>هام</b> & "اقتباس" \'واحد\'',
            $response->json('data.invoices.0.notes'),
            'Stored note text must be returned byte-for-byte without HTML transformation.',
        );
    }

    public function test_unpaid_revenue_notes_never_leak_into_rows(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل إيراد', 'revenue-notes@example.test');
        $clientId = $this->client('عميل إيراد', '0555001501', 'عنوان إيراد', 'internet');
        $invoiceId = $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-REV-1']);
        DB::table('tbl_revenues')->insert([
            'invoice_id' => $invoiceId,
            'client_id' => $clientId,
            'collected_by' => $admin->id,
            'amount' => '100.00',
            'remaining_amount' => '0.00',
            'received_at' => now(),
            'status' => 'paid',
            'notes' => 'إيراد سري لا يظهر في الفواتير غير المدفوعة',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->unpaidRequest($admin);

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringNotContainsString('إيراد سري لا يظهر في الفواتير غير المدفوعة', $content);
        $this->assertSame('INV-REV-1', $response->json('data.invoices.0.invoice_number'));
        $this->assertNull($response->json('data.invoices.0.notes'));
    }

    // ----------------------------------------------- notes preview bound

    public function test_unpaid_notes_exactly_1000_characters_are_kept_whole(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل ألف', 'exact-1000@example.test');
        $clientId = $this->client('عميل ألف', '0555001511', 'عنوان ألف', 'internet');
        $full = str_repeat('م', 1000);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-1000-1', 'notes' => $full]);

        $response = $this->unpaidRequest($admin);

        $response->assertOk();
        $preview = $response->json('data.invoices.0.notes');
        $this->assertSame(1000, mb_strlen((string) $preview));
        $this->assertSame($full, $preview);
    }

    public function test_unpaid_notes_over_1000_characters_truncate_to_1000_prefix(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل طويل', 'over-1000@example.test');
        $clientId = $this->client('عميل طويل', '0555001512', 'عنوان طويل', 'internet');
        $full = str_repeat('م', 1500);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-LONG-1', 'notes' => $full]);

        $response = $this->unpaidRequest($admin);

        $response->assertOk();
        $preview = $response->json('data.invoices.0.notes');
        $this->assertSame(1500, mb_strlen($full));
        $this->assertSame(1000, mb_strlen((string) $preview));
        $this->assertSame(mb_substr($full, 0, 1000), $preview);
        // The dropped tail must not resurface anywhere in the payload.
        $this->assertStringNotContainsString(mb_substr($full, 1000), $response->getContent());
    }

    public function test_unpaid_notes_truncation_is_multibyte_arabic_safe(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل يونيكود', 'multibyte-notes@example.test');
        $clientId = $this->client('عميل يونيكود', '0555001513', 'عنوان يونيكود', 'internet');
        // 990 Arabic code points + a 10-char ASCII marker + a dropped Arabic
        // tail: the preview boundary lands exactly on the marker's end.
        $full = str_repeat('ق', 990).'0123456789'.'نهاية';
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-MB-1', 'notes' => $full]);

        $response = $this->unpaidRequest($admin);

        $response->assertOk();
        $preview = $response->json('data.invoices.0.notes');
        $this->assertIsString($preview);
        // mb_substr never splits a multi-byte code point: the preview must be
        // valid UTF-8 and exactly the first 1000 code points.
        $this->assertTrue(mb_check_encoding($preview, 'UTF-8'));
        $this->assertSame(1000, mb_strlen($preview));
        $this->assertSame(mb_substr($full, 0, 1000), $preview);
        $this->assertStringEndsWith('0123456789', $preview);
        $this->assertStringNotContainsString('نهاية', $preview);
    }

    public function test_unpaid_notes_surrounding_whitespace_is_trimmed_internal_content_preserved(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل مسافات', 'trim-notes@example.test');
        $clientId = $this->client('عميل مسافات', '0555001514', 'عنوان مسافات', 'internet');
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-TRIM-1', 'notes' => " \t\n ملاحظة داخلية  \n "]);
        $this->invoice(['client_id' => $clientId, 'invoice_number' => 'INV-TRIM-2', 'notes' => 'ملاحظة  بمسافتين داخليتين']);

        $response = $this->unpaidRequest($admin);

        $response->assertOk();
        $byNumber = collect($response->json('data.invoices'))->keyBy('invoice_number');
        $this->assertSame('ملاحظة داخلية', $byNumber['INV-TRIM-1']['notes']);
        // Only SURROUNDING whitespace is trimmed; internal spacing is kept.
        $this->assertSame('ملاحظة  بمسافتين داخليتين', $byNumber['INV-TRIM-2']['notes']);
    }

    // ------------------------------------------------------ page contract

    public function test_unpaid_filtered_pagination_page_reset_semantics(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل صفحات', 'pages-unpaid@example.test');
        $internet = $this->client('عميل صفحات', '0555001601', 'عنوان صفحات', 'internet');
        $satellite = $this->client('عميل سات', '0555001602', 'عنوان سات', 'satellite');
        for ($i = 1; $i <= 8; $i++) {
            $this->invoice(['client_id' => $internet, 'invoice_number' => 'FILT-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT)]);
        }
        $this->invoice(['client_id' => $satellite, 'invoice_number' => 'FILT-099']);

        $page1 = $this->unpaidRequest($admin, ['client_type' => 'internet', 'per_page' => 3, 'page' => 1]);
        $page2 = $this->unpaidRequest($admin, ['client_type' => 'internet', 'per_page' => 3, 'page' => 2]);

        $page1->assertOk();
        $page2->assertOk();
        // Selecting a chip sends a fresh request for page 1: the server is
        // authoritative — totals/last_page always reflect the filter, so the
        // client can reset pagination safely.
        $this->assertSame(
            ['current_page' => 1, 'last_page' => 3, 'per_page' => 3, 'total' => 8, 'has_more' => true],
            $page1->json('data.pagination'),
        );
        $this->assertSame(3, count($page1->json('data.invoices')));
        $this->assertSame(3, count($page2->json('data.invoices')));
        $ids = array_merge(
            array_column($page1->json('data.invoices'), 'id'),
            array_column($page2->json('data.invoices'), 'id'),
        );
        $this->assertSame(6, count(array_unique($ids)), 'No duplicates across filtered pages.');
    }

    // -------------------------------------------------------- query count

    public function test_unpaid_query_count_is_bounded_with_filter_and_notes(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل استعلامات', 'queries-unpaid@example.test');
        $internet = $this->client('عميل استعلامات', '0555001701', 'عنوان استعلامات', 'internet');
        for ($i = 1; $i <= 30; $i++) {
            $this->invoice([
                'client_id' => $internet,
                'invoice_number' => 'QC2-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'status' => $i % 2 === 0 ? 'partial' : 'unpaid',
                'notes' => $i % 3 === 0 ? 'ملاحظة '.$i : null,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $small = $this->unpaidRequest($admin, ['client_type' => 'internet', 'per_page' => 1]);
        $small->assertOk();
        $smallQueries = count(DB::getQueryLog());

        DB::flushQueryLog();
        $large = $this->unpaidRequest($admin, ['client_type' => 'internet', 'per_page' => 50]);
        $large->assertOk();
        $largeQueries = count(DB::getQueryLog());

        // The page-size invariant is the N+1 proof: the filter adds a WHERE
        // clause and notes only widen the SELECT list — never extra queries.
        $this->assertSame($smallQueries, $largeQueries, 'Query count must not scale with page size (N+1).');
        $this->assertLessThanOrEqual(4, $largeQueries);
        $this->assertSame(30, $large->json('data.pagination.total'));
        foreach ($large->json('data.invoices') as $invoice) {
            $this->assertArrayHasKey('notes', $invoice);
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

    private function client(string $name, string $phone, string $address, string $clientType = 'internet'): int
    {
        return DB::table('tbl_clients')->insertGetId([
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

    private function unpaidRequest(Admin $admin, array $query = []): TestResponse
    {
        $url = '/api/v1/unpaid-invoices';
        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $this->withToken(auth('api')->login($admin))->getJson($url);
    }

    private function clientsRequest(Admin $admin, array $query = []): TestResponse
    {
        $url = '/api/v1/clients';
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
