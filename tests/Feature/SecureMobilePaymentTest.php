<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Admin\Invoice;
use App\Services\SecureMobilePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Mockery;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SecureMobilePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_payment_requires_authentication(): void
    {
        $response = $this->postJson(
            '/api/v1/invoices/1/payments',
            ['expected_remaining' => '25.00'],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $response
            ->assertStatus(401)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'authentication_required');
    }

    public function test_authorized_collector_can_pay_the_full_remaining_balance_atomically(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $key = (string) Str::uuid();

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '75.00'],
            ['Idempotency-Key' => $key],
        );

        $response
            ->assertCreated()
            ->assertJsonPath('result', true)
            ->assertJsonPath('data.amount', '75.00')
            ->assertJsonPath('data.collector.id', $collector->id)
            ->assertJsonPath('data.collector.name', $collector->name)
            ->assertJsonPath('data.invoice.status', 'paid')
            ->assertJsonPath('data.invoice.remaining_amount', '0.00');

        $reference = $response->json('data.reference');
        $this->assertIsString($reference);
        $this->assertNotSame('', $reference);

        $this->assertDatabaseHas('tbl_invoices', [
            'id' => $invoice->id,
            'paid_amount' => 100,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('tbl_revenues', [
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'collected_by' => $collector->id,
            'amount' => 75,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('tbl_financial_transactions', [
            'account_id' => $collector->account_id,
            'created_by' => $collector->id,
            'amount' => 75,
            'type' => 'qapd',
        ]);
        $this->assertDatabaseHas('mobile_payment_operations', [
            'idempotency_key' => $key,
            'reference' => $reference,
            'invoice_id' => $invoice->id,
            'collector_id' => $collector->id,
            'amount' => 75,
            'status' => 'committed',
        ]);
        $this->assertDatabaseHas('logs', [
            'action' => 'mobile_invoice_paid',
            'model_id' => $invoice->id,
            'user_id' => $collector->id,
        ]);
        $audit = DB::table('logs')
            ->where('action', 'mobile_invoice_paid')
            ->where('model_id', $invoice->id)
            ->first();
        $this->assertSame('partial', json_decode($audit->old_data, true)['status']);
        $this->assertSame('paid', json_decode($audit->new_data, true)['status']);
        $this->assertSame($reference, json_decode($audit->new_data, true)['payment_reference']);
    }

    public function test_invoice_detail_returns_the_actual_paid_status(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $invoice->forceFill([
            'status' => 'paid',
            'paid_amount' => '75.00',
            'remaining_amount' => '0.00',
            'paid_date' => now(),
        ])->save();

        $response = $this->withToken(auth('api')->login($collector))->getJson(
            "/api/v1/invoice/{$invoice->id}",
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.invoice.status', 'paid')
            ->assertJsonPath('data.invoice.remaining_amount', '0.00');
    }

    public function test_collector_without_payment_permission_is_forbidden(): void
    {
        [$collector, $invoice] = $this->paymentFixture(false);

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '75.00'],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $response
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'payment_forbidden');

        $this->assertDatabaseMissing('tbl_revenues', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
    }

    public function test_stale_expected_balance_is_rejected_without_financial_writes(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '70.00'],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $response
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'stale_invoice_balance');

        $this->assertDatabaseHas('tbl_invoices', [
            'id' => $invoice->id,
            'paid_amount' => 25,
            'remaining_amount' => 75,
            'status' => 'partial',
        ]);
        $this->assertDatabaseMissing('tbl_revenues', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
    }

    public function test_inconsistent_invoice_totals_are_rejected_without_writes(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $invoice->forceFill(['paid_amount' => 20, 'remaining_amount' => 75])->save();

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '75.00'],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $response
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'invoice_balance_inconsistent');
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('tbl_revenues', ['invoice_id' => $invoice->id]);
    }

    public function test_repeating_the_same_idempotency_key_replays_one_committed_payment(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $token = auth('api')->login($collector);
        $key = (string) Str::uuid();
        $body = ['expected_remaining' => '75.00'];
        $headers = ['Idempotency-Key' => $key];

        $first = $this->withToken($token)->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            $body,
            $headers,
        );
        DB::table('tbl_invoices')->where('id', $invoice->id)->update([
            'paid_amount' => '0.00',
            'remaining_amount' => '100.00',
            'status' => 'unpaid',
            'deleted_at' => now(),
        ]);
        $second = $this->withToken($token)->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            $body,
            $headers,
        );

        $first->assertCreated()->assertJsonPath('data.replayed', false);
        $second
            ->assertOk()
            ->assertJsonPath('data.replayed', true)
            ->assertJsonPath('data.reference', $first->json('data.reference'))
            ->assertJsonPath('data.amount', $first->json('data.amount'))
            ->assertJsonPath('data.invoice.status', $first->json('data.invoice.status'))
            ->assertJsonPath('data.invoice.paid_amount', $first->json('data.invoice.paid_amount'))
            ->assertJsonPath('data.invoice.remaining_amount', $first->json('data.invoice.remaining_amount'))
            ->assertJsonPath('data.collector.id', $first->json('data.collector.id'))
            ->assertJsonPath('data.collector.name', $first->json('data.collector.name'));

        $this->assertSame(1, DB::table('mobile_payment_operations')->count());
        $this->assertSame(1, DB::table('tbl_revenues')->count());
        $this->assertSame(1, DB::table('tbl_financial_transactions')->count());
        $this->assertSame(1, DB::table('logs')->where('action', 'mobile_invoice_paid')->count());
    }

    public function test_reusing_an_idempotency_key_for_a_different_request_is_rejected(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $token = auth('api')->login($collector);
        $key = (string) Str::uuid();

        $first = $this->withToken($token)->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '75.00'],
            ['Idempotency-Key' => $key],
        );
        $conflict = $this->withToken($token)->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '70.00'],
            ['Idempotency-Key' => $key],
        );

        $first->assertCreated();
        $conflict
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'idempotency_conflict');
        $this->assertSame(1, DB::table('mobile_payment_operations')->count());
        $this->assertSame(1, DB::table('tbl_revenues')->count());
    }

    public function test_a_second_key_cannot_pay_an_already_paid_invoice(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $token = auth('api')->login($collector);

        $first = $this->withToken($token)->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '75.00'],
            ['Idempotency-Key' => (string) Str::uuid()],
        );
        $duplicate = $this->withToken($token)->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '75.00'],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $first->assertCreated();
        $duplicate
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'invoice_already_paid');
        $this->assertSame(1, DB::table('mobile_payment_operations')->count());
        $this->assertSame(1, DB::table('tbl_revenues')->count());
    }

    public function test_mobile_cannot_submit_an_amount_or_note(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            [
                'expected_remaining' => '75.00',
                'amount' => '1.00',
                'note' => 'override',
            ],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'payment_validation_failed');
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
    }

    public function test_expected_remaining_is_bounded_to_the_database_money_range(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '999999999999999999999999999999.99'],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'payment_validation_failed');
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
    }

    public function test_long_user_agent_is_safely_bounded_without_losing_the_payment(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $response = $this
            ->withToken(auth('api')->login($collector))
            ->withHeader('User-Agent', str_repeat('A', 1000))
            ->postJson(
                "/api/v1/invoices/{$invoice->id}/payments",
                ['expected_remaining' => '75.00'],
                ['Idempotency-Key' => (string) Str::uuid()],
            );

        $response->assertCreated();
        $this->assertSame(255, strlen((string) DB::table('logs')->value('user_agent')));
    }

    public function test_unknown_payment_fields_are_rejected(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            [
                'expected_remaining' => '75.00',
                'collector_amount' => '1.00',
            ],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'payment_validation_failed');
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
    }

    public function test_idempotency_key_is_rejected_from_form_body_even_with_valid_header(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $response = $this
            ->withToken(auth('api')->login($collector))
            ->withHeader('Idempotency-Key', (string) Str::uuid())
            ->post("/api/v1/invoices/{$invoice->id}/payments", [
                'expected_remaining' => '75.00',
                'idempotency_key' => (string) Str::uuid(),
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'payment_validation_failed');
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
    }

    public function test_collector_can_resolve_a_timeout_by_querying_the_same_idempotency_key(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $token = auth('api')->login($collector);
        $key = (string) Str::uuid();

        $payment = $this->withToken($token)->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '75.00'],
            ['Idempotency-Key' => $key],
        );
        $status = $this->withToken($token)->getJson("/api/v1/payments/{$key}");

        $payment->assertCreated();
        $status
            ->assertOk()
            ->assertJsonPath('data.reference', $payment->json('data.reference'))
            ->assertJsonPath('data.status', 'committed')
            ->assertJsonPath('data.invoice.remaining_amount', '0.00');
        $this->assertSame(1, DB::table('tbl_revenues')->count());
    }

    public function test_status_lookup_hides_unexpected_failures_behind_a_correlation_id(): void
    {
        [$collector] = $this->paymentFixture();
        $service = Mockery::mock(SecureMobilePaymentService::class);
        $service->shouldReceive('status')->once()->andThrow(new \RuntimeException('sensitive failure'));
        $this->app->instance(SecureMobilePaymentService::class, $service);

        $response = $this->withToken(auth('api')->login($collector))->getJson(
            '/api/v1/payments/'.Str::uuid(),
        );

        $response
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'payment_internal_error')
            ->assertJsonStructure(['error' => ['correlation_id']]);
        $this->assertStringNotContainsString('sensitive failure', $response->getContent());
    }

    public function test_missing_or_deleted_collector_account_is_rejected_as_validation_error(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        DB::table('tbl_accounts')->where('id', $collector->account_id)->update(['deleted_at' => now()]);

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoices/{$invoice->id}/payments",
            ['expected_remaining' => '75.00'],
            ['Idempotency-Key' => (string) Str::uuid()],
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'collector_account_invalid');
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('tbl_revenues', ['invoice_id' => $invoice->id]);
    }

    public function test_legacy_unprotected_payment_route_is_not_available(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $response = $this->withToken(auth('api')->login($collector))->postJson(
            "/api/v1/invoice/{$invoice->id}/pay",
        );

        $response->assertNotFound();
        $this->assertDatabaseMissing('tbl_revenues', ['invoice_id' => $invoice->id]);
    }

    public function test_a_late_audit_failure_rolls_back_every_financial_write(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $event = 'eloquent.creating: '.\App\Models\Log::class;
        Event::listen($event, static function (): void {
            throw new \RuntimeException('synthetic-sensitive-database-detail');
        });

        try {
            $response = $this->withToken(auth('api')->login($collector))->postJson(
                "/api/v1/invoices/{$invoice->id}/payments",
                ['expected_remaining' => '75.00'],
                ['Idempotency-Key' => (string) Str::uuid()],
            );
        } finally {
            Event::forget($event);
        }

        $response
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'payment_internal_error');
        $this->assertStringNotContainsString(
            'synthetic-sensitive-database-detail',
            $response->getContent(),
        );
        $this->assertDatabaseHas('tbl_invoices', [
            'id' => $invoice->id,
            'paid_amount' => 25,
            'remaining_amount' => 75,
            'status' => 'partial',
        ]);
        $this->assertDatabaseMissing('mobile_payment_operations', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('tbl_revenues', ['invoice_id' => $invoice->id]);
        $this->assertSame(0, DB::table('tbl_financial_transactions')->count());
    }

    private function paymentFixture(bool $grantPermission = true): array
    {
        $accountId = DB::table('tbl_accounts')->insertGetId([
            'name' => 'Secure payment test account',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $collector = Admin::query()->create([
            'name' => 'Secure Payment Collector',
            'email' => 'secure-payment@example.test',
            'password' => bcrypt('test-password'),
            'status' => '1',
            'account_id' => $accountId,
        ]);

        if ($grantPermission) {
            Permission::findOrCreate('pay_invoice', 'admin');
            $collector->givePermissionTo('pay_invoice');
        }

        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'Secure Payment Test Client',
            'phone' => null,
            'subscription_id' => 1,
            'price' => 100,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->subMonth()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'PAY-'.Str::upper(Str::random(10)),
            'client_id' => $clientId,
            'subscription_id' => 1,
            'amount' => 100,
            'paid_amount' => 25,
            'remaining_amount' => 75,
            'enshaa_date' => now()->subMonth()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => 'partial',
        ]);

        return [$collector, $invoice];
    }
}
