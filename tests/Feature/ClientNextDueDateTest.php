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
 * Feature 018 — collector-facing "next due date" on the mobile subscriber list.
 *
 * Contract:
 *  - GET /api/v1/clients rows expose `nextDueDate`: the EARLIEST `due_date`
 *    among the client's unpaid/partial, non-soft-deleted invoices;
 *  - paid invoices never influence it, and soft-deleted invoices are ignored;
 *  - it is null (never '', never 'N/A') when the client has no outstanding
 *    invoice, so the app can render "لا يوجد استحقاق";
 *  - the existing `subscriptionDate` key keeps its previous meaning unchanged;
 *  - every row carries its own value (no cross-row leakage in a page);
 *  - one grouped subquery per page, never a query per client row.
 *
 * Same guarded migrate:fresh-once + per-test transaction pattern as
 * ClientTypeFiltersTest (never RefreshDatabase, dedicated MySQL test DB only).
 */
class ClientNextDueDateTest extends TestCase
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

    // ============================================================ contract

    public function test_next_due_date_is_the_earliest_unpaid_due_date(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'next-due-1@example.test');
        $clientId = $this->client('عميل واحد', '0555000001', 'عنوان', 'internet');

        $this->invoice(['client_id' => $clientId, 'due_date' => '2026-09-01', 'status' => 'unpaid']);
        $this->invoice(['client_id' => $clientId, 'due_date' => '2026-07-15', 'status' => 'partial', 'paid_amount' => '20.00', 'remaining_amount' => '80.00']);
        $this->invoice(['client_id' => $clientId, 'due_date' => '2026-11-01', 'status' => 'unpaid']);

        $row = $this->clientsRow($admin, $clientId);

        $this->assertSame('2026-07-15', $row['nextDueDate']);
    }

    public function test_paid_invoices_never_win_the_next_due_date(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'next-due-2@example.test');
        $clientId = $this->client('عميل اثنان', '0555000002', 'عنوان', 'internet');

        // An older PAID invoice must not be reported as the outstanding due date.
        $this->invoice(['client_id' => $clientId, 'due_date' => '2026-01-01', 'status' => 'paid', 'paid_amount' => '100.00', 'remaining_amount' => '0.00']);
        $this->invoice(['client_id' => $clientId, 'due_date' => '2026-10-05', 'status' => 'unpaid']);

        $row = $this->clientsRow($admin, $clientId);

        $this->assertSame('2026-10-05', $row['nextDueDate']);
    }

    public function test_soft_deleted_invoices_are_ignored(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'next-due-3@example.test');
        $clientId = $this->client('عميل ثلاثة', '0555000003', 'عنوان', 'internet');

        $this->invoice(['client_id' => $clientId, 'due_date' => '2026-03-01', 'status' => 'unpaid', 'deleted_at' => now()]);
        $this->invoice(['client_id' => $clientId, 'due_date' => '2026-09-20', 'status' => 'unpaid']);

        $row = $this->clientsRow($admin, $clientId);

        $this->assertSame('2026-09-20', $row['nextDueDate']);
    }

    public function test_next_due_date_is_null_when_nothing_is_outstanding(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'next-due-4@example.test');

        $onlyPaid = $this->client('عميل مدفوع', '0555000004', 'عنوان', 'internet');
        $this->invoice(['client_id' => $onlyPaid, 'due_date' => '2026-05-01', 'status' => 'paid', 'paid_amount' => '100.00', 'remaining_amount' => '0.00']);

        $noInvoices = $this->client('عميل بلا فواتير', '0555000005', 'عنوان', 'satellite');

        $paidRow = $this->clientsRow($admin, $onlyPaid);
        $emptyRow = $this->clientsRow($admin, $noInvoices);

        $this->assertNull($paidRow['nextDueDate'], 'A fully paid client has no outstanding due date.');
        $this->assertNull($emptyRow['nextDueDate'], 'A client without invoices has no outstanding due date.');
        $this->assertNotSame('', $paidRow['nextDueDate'], 'Never an empty string.');
        $this->assertNotSame('N/A', $paidRow['nextDueDate'], 'Never the N/A placeholder.');
    }

    public function test_existing_subscription_date_key_is_still_present(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'next-due-5@example.test');
        $clientId = $this->client('عميل خمسة', '0555000006', 'عنوان', 'internet');

        $this->invoice(['client_id' => $clientId, 'due_date' => '2026-12-01', 'status' => 'unpaid']);

        $row = $this->clientsRow($admin, $clientId);

        // The pre-existing key keeps its previous type/shape; its meaning is
        // deliberately NOT changed by this feature.
        $this->assertArrayHasKey('subscriptionDate', $row);
        $this->assertTrue(
            $row['subscriptionDate'] === null || is_string($row['subscriptionDate']),
            'subscriptionDate keeps its previous nullable-string shape.'
        );
    }

    public function test_each_row_carries_its_own_next_due_date(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'next-due-6@example.test');

        $first = $this->client('عميل أ', '0555000007', 'عنوان', 'internet');
        $second = $this->client('عميل ب', '0555000008', 'عنوان', 'internet');
        $third = $this->client('عميل ج', '0555000009', 'عنوان', 'satellite');

        $this->invoice(['client_id' => $first, 'due_date' => '2026-07-01', 'status' => 'unpaid']);
        $this->invoice(['client_id' => $second, 'due_date' => '2026-08-02', 'status' => 'unpaid']);
        // $third has nothing outstanding.

        $response = $this->clientsRequest($admin, ['per_page' => 50]);
        $response->assertOk();

        $byId = collect($response->json('data.clients'))->keyBy('id');

        $this->assertSame('2026-07-01', $byId[$first]['nextDueDate']);
        $this->assertSame('2026-08-02', $byId[$second]['nextDueDate']);
        $this->assertNull($byId[$third]['nextDueDate']);
    }

    public function test_next_due_date_subquery_does_not_grow_with_the_number_of_rows(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'next-due-7@example.test');

        for ($i = 0; $i < 5; $i++) {
            $clientId = $this->client('عميل '.$i, '05550100'.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'عنوان', 'internet');
            $this->invoice(['client_id' => $clientId, 'due_date' => '2026-09-0'.($i + 1), 'status' => 'unpaid']);
        }

        $countDueDateQueries = function (int $perPage) use ($admin): int {
            $queries = [];
            DB::listen(function ($query) use (&$queries) {
                if (str_contains($query->sql, 'next_due_date')) {
                    $queries[] = $query->sql;
                }
            });

            $this->clientsRequest($admin, ['per_page' => $perPage])->assertOk();

            return count($queries);
        };

        $smallPage = $countDueDateQueries(2);
        $bigPage = $countDueDateQueries(5);

        // Constant per page (paginate issues its own count query too) and
        // explicitly independent of how many client rows are returned.
        $this->assertSame($smallPage, $bigPage, 'The due-date resolution must not run per client row.');
        $this->assertLessThanOrEqual(2, $bigPage, 'At most one grouped subquery per page plus the pagination count.');
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

    private function clientsRequest(Admin $admin, array $query = []): TestResponse
    {
        $url = '/api/v1/clients';
        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $this->withToken(auth('api')->login($admin))->getJson($url);
    }

    /** @return array<string, mixed> */
    private function clientsRow(Admin $admin, int $clientId): array
    {
        $response = $this->clientsRequest($admin, ['per_page' => 100]);
        $response->assertOk();

        $row = collect($response->json('data.clients'))->firstWhere('id', $clientId);
        $this->assertNotNull($row, "Client {$clientId} must be present in the list payload.");

        return $row;
    }
}
