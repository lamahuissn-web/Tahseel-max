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
 * Bug (2026-11-04): `GET /api/v1/clients/{id}/invoices` filtered the
 * unpaid/partial list by `created_at >= now()->subYear()`, so an invoice that
 * stayed open for more than a year disappeared from the collector's screen and
 * from `total_unpaidAndPartial_amount` — the customer looked like he owed less
 * than he does (client #1065: two invoices / 14.00 shown instead of three /
 * 24.00).
 *
 * Contract under test:
 *  - an unpaid/partial invoice older than a year is returned and counted;
 *  - the paid-history window is untouched (a paid invoice older than a year is
 *    still excluded) so the payload does not grow without limit;
 *  - soft-deleted invoices never appear.
 *
 * Same guarded migrate:fresh-once + per-test transaction pattern as
 * ClientNextDueDateTest (never RefreshDatabase, dedicated MySQL test DB only).
 */
class ClientInvoicesUnpaidWindowTest extends TestCase
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

    public function test_an_unpaid_invoice_older_than_a_year_is_still_returned_and_counted(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'unpaid-window@example.test');
        $clientId = $this->client('SAT زبون قديم', '0555111001', 'satellite');

        // 18 months old, still unpaid — the case that was hidden.
        $old = $this->invoice([
            'client_id' => $clientId,
            'status' => 'unpaid',
            'amount' => '10.00',
            'remaining_amount' => '10.00',
            'due_date' => now()->subMonths(18)->toDateString(),
            'created_at' => now()->subMonths(18),
        ]);

        $recent = $this->invoice([
            'client_id' => $clientId,
            'status' => 'unpaid',
            'amount' => '4.00',
            'remaining_amount' => '4.00',
            'due_date' => now()->addDays(3)->toDateString(),
            'created_at' => now()->subDays(3),
        ]);

        $data = $this->invoicesPayload($admin, $clientId);

        $numbers = collect($data['unpaid_and_partial_invoices']['invoices'])->pluck('id')->all();

        $this->assertContains($old, $numbers, 'An 18-month-old unpaid invoice must not disappear.');
        $this->assertContains($recent, $numbers);
        $this->assertSame(2, $data['unpaid_and_partial_invoices']['count']);
        $this->assertEquals(14.0, (float) $data['unpaid_and_partial_invoices']['total_unpaidAndPartial_amount']);
    }

    public function test_paid_history_keeps_its_one_year_window(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'paid-window@example.test');
        $clientId = $this->client('زبون مدفوع', '0555111002', 'internet');

        $oldPaid = $this->invoice([
            'client_id' => $clientId,
            'status' => 'paid',
            'paid_amount' => '100.00',
            'remaining_amount' => '0.00',
            'paid_date' => now()->subMonths(18)->toDateString(),
            'due_date' => now()->subMonths(18)->toDateString(),
            'created_at' => now()->subMonths(18),
        ]);

        $data = $this->invoicesPayload($admin, $clientId);

        $paidIds = collect($data['paid_invoices']['invoices'])->pluck('id')->all();

        $this->assertNotContains($oldPaid, $paidIds, 'Paid history must keep its one-year window.');
    }

    public function test_soft_deleted_unpaid_invoices_never_appear(): void
    {
        $this->currency('$');
        $admin = $this->admin('محصل', 'soft-deleted@example.test');
        $clientId = $this->client('زبون محذوف', '0555111003', 'internet');

        $deleted = $this->invoice([
            'client_id' => $clientId,
            'status' => 'unpaid',
            'deleted_at' => now(),
            'created_at' => now()->subMonths(18),
        ]);

        $data = $this->invoicesPayload($admin, $clientId);

        $this->assertNotContains($deleted, collect($data['unpaid_and_partial_invoices']['invoices'])->pluck('id')->all());
        $this->assertSame(0, $data['unpaid_and_partial_invoices']['count']);
    }

    // ============================================================ helpers

    private function invoicesPayload(Admin $admin, int $clientId): array
    {
        $response = $this->withToken(auth('api')->login($admin))
            ->getJson('/api/v1/clients/'.$clientId.'/invoices');

        $response->assertOk();

        return (array) $response->json('data');
    }

    private function currency(string $value): void
    {
        AppConfig::create(['key' => 'currency', 'value' => $value]);
    }

    private function admin(string $name, string $email, string $roleName = 'collector'): Admin
    {
        $admin = Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('test-password'),
            'status' => '1',
        ]);

        if ($roleName !== '') {
            Role::findOrCreate($roleName, 'admin');
            $admin->assignRole($roleName);
        }

        return $admin;
    }

    private function client(string $name, string $phone, string $clientType = 'internet'): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name' => $name,
            'phone' => $phone,
            'address1' => 'عنوان',
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
}
