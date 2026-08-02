<?php

namespace Tests\Feature;

use App\Exceptions\SecurePaymentException;
use App\Models\Admin;
use App\Models\Admin\Invoice;
use App\Models\Admin\Revenue;
use App\Services\MobilePaymentReceiptReconciler;
use App\Services\SecureMobilePaymentService;
use App\Services\WhatsApp\PaymentReceiptNotifier;
use App\Services\WhatsApp\WhatsAppMessageDispatcher;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SecureMobilePaymentAfterCommitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! str_contains((string) config('database.connections.'.config('database.default').'.database'), '_test')) {
            throw new \RuntimeException('Refusing migrate:fresh outside a dedicated test database.');
        }
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    public function test_operation_specific_receipt_runs_only_after_commit(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $reference = null;
        $notifier = Mockery::mock(PaymentReceiptNotifier::class);
        $notifier->shouldReceive('notifyPayment')
            ->once()
            ->withArgs(function (Invoice $paidInvoice, Revenue $revenue, string $paymentReference) use (&$reference): bool {
                $reference = $paymentReference;

                return DB::transactionLevel() === 0
                    && $paidInvoice->status === 'paid'
                    && (string) $revenue->amount === '75.00';
            })
            ->andReturn('queued');
        $notifier->shouldNotReceive('notify');

        $result = ($this->paymentService($notifier))->collectFullRemaining(
            $invoice->id,
            $collector,
            '75.00',
            (string) Str::uuid(),
        );

        $this->assertSame($result['reference'], $reference);
        $this->assertSame('75.00', $result['amount']);
    }

    public function test_service_rejects_a_collector_without_payment_permission(): void
    {
        [$collector, $invoice] = $this->paymentFixture(null, false);
        $notifier = Mockery::mock(PaymentReceiptNotifier::class);
        $notifier->shouldNotReceive('notifyPayment');

        try {
            ($this->paymentService($notifier))->collectFullRemaining(
                $invoice->id,
                $collector,
                '75.00',
                (string) Str::uuid(),
            );
            $this->fail('The service accepted an unauthorized collector.');
        } catch (SecurePaymentException $exception) {
            $this->assertSame('payment_forbidden', $exception->errorCode);
            $this->assertSame(403, $exception->httpStatus);
        }
    }

    public function test_receipt_failure_never_turns_a_committed_payment_into_an_error(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $notifier = Mockery::mock(PaymentReceiptNotifier::class);
        $notifier->shouldReceive('notifyPayment')
            ->once()
            ->andThrow(new \RuntimeException('synthetic WhatsApp outage'));

        $result = ($this->paymentService($notifier))->collectFullRemaining(
            $invoice->id,
            $collector,
            '75.00',
            (string) Str::uuid(),
        );

        $this->assertSame('75.00', $result['amount']);
        $this->assertDatabaseHas('tbl_invoices', [
            'id' => $invoice->id,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('mobile_payment_operations', [
            'invoice_id' => $invoice->id,
            'status' => 'committed',
            'receipt_status' => 'retry',
        ]);
    }

    public function test_idempotent_payment_queues_one_reference_bound_receipt(): void
    {
        [$collector, $invoice] = $this->paymentFixture('+96170781562');
        $dispatcher = Mockery::mock(WhatsAppMessageDispatcher::class);
        $dispatcher->shouldReceive('dispatch')->once();
        $service = $this->paymentService(new PaymentReceiptNotifier($dispatcher));
        $key = (string) Str::uuid();

        $first = $service->collectFullRemaining($invoice->id, $collector, '75.00', $key);
        $replayed = $service->collectFullRemaining($invoice->id, $collector, '75.00', $key);

        $this->assertFalse($first['replayed']);
        $this->assertTrue($replayed['replayed']);
        $this->assertSame($first['reference'], $replayed['reference']);
        $this->assertDatabaseHas('whatsapp_message_logs', [
            'payment_reference' => $first['reference'],
            'invoice_id' => $invoice->id,
            'template_type' => 'receipt',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('mobile_payment_operations', [
            'reference' => $first['reference'],
            'receipt_status' => 'queued',
        ]);
        $this->assertSame(1, DB::table('whatsapp_message_logs')->count());
    }

    public function test_failed_receipt_is_recoverable_from_the_payment_outbox(): void
    {
        [$collector, $invoice] = $this->paymentFixture('+961****1562');
        $failed = Mockery::mock(PaymentReceiptNotifier::class);
        $failed->shouldReceive('notifyPayment')->once()->andReturn('retry');
        $result = $this->paymentService($failed)->collectFullRemaining(
            $invoice->id, $collector, '75.00', (string) Str::uuid(),
        );
        $operationId = DB::table('mobile_payment_operations')
            ->where('reference', $result['reference'])->value('id');

        $recovered = Mockery::mock(PaymentReceiptNotifier::class);
        $recovered->shouldReceive('notifyPayment')->once()->andReturn('queued');
        $outcome = (new MobilePaymentReceiptReconciler($recovered))->process($operationId);

        $this->assertSame('queued', $outcome);
        $this->assertDatabaseHas('mobile_payment_operations', [
            'id' => $operationId,
            'receipt_status' => 'queued',
            'receipt_attempts' => 2,
        ]);
    }

    public function test_pending_receipt_log_is_redispatched_by_whatsapp_recovery_command(): void
    {
        [$collector, $invoice] = $this->paymentFixture('+96170781562');
        $dispatcher = Mockery::mock(WhatsAppMessageDispatcher::class);
        $dispatcher->shouldReceive('dispatch')->twice();
        $this->app->instance(WhatsAppMessageDispatcher::class, $dispatcher);

        $this->paymentService(new PaymentReceiptNotifier($dispatcher))->collectFullRemaining(
            $invoice->id,
            $collector,
            '75.00',
            (string) Str::uuid(),
        );
        Artisan::call('whatsapp:recover-pending', ['--limit' => 100]);

        $this->assertDatabaseHas('whatsapp_message_logs', [
            'invoice_id' => $invoice->id,
            'status' => 'pending',
            'template_type' => 'receipt',
        ]);
    }

    public function test_receipt_enqueue_failure_does_not_log_exception_message_or_trace(): void
    {
        [$collector, $invoice] = $this->paymentFixture('+96170781562');
        $revenue = Revenue::query()->create([
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'amount' => '75.00',
            'collected_by' => $collector->id,
            'status' => 'paid',
            'remaining_amount' => '0.00',
            'received_at' => now(),
        ]);
        $dispatcher = Mockery::mock(WhatsAppMessageDispatcher::class);
        $dispatcher->shouldReceive('dispatch')
            ->once()
            ->andThrow(new \RuntimeException('sensitive-phone-and-message'));
        Log::spy();

        (new PaymentReceiptNotifier($dispatcher))->notifyPayment(
            $invoice->load('client'),
            $revenue,
            'PAY-SAFE-LOG-TEST',
        );

        Log::shouldHaveReceived('warning')->withArgs(
            fn (string $message, array $context): bool => str_contains($message, 'Failed to enqueue')
                && ! str_contains(json_encode($context), 'sensitive-phone-and-message'),
        );
    }

    private function paymentFixture(?string $phone = null, bool $grantPermission = true): array
    {
        $accountId = DB::table('tbl_accounts')->insertGetId([
            'name' => 'After commit account',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $collector = Admin::query()->create([
            'name' => 'After Commit Collector',
            'email' => 'after-commit@example.test',
            'password' => bcrypt('test-password'),
            'status' => '1',
            'account_id' => $accountId,
        ]);
        if ($grantPermission) {
            Permission::findOrCreate('pay_invoice', 'admin');
            $collector->givePermissionTo('pay_invoice');
        }
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'After Commit Client',
            'phone' => $phone,
            'subscription_id' => 1,
            'price' => 100,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->subMonth()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $invoice = Invoice::query()->create([
            'invoice_number' => 'AFTER-'.Str::upper(Str::random(10)),
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

    private function paymentService(PaymentReceiptNotifier $notifier): SecureMobilePaymentService
    {
        return new SecureMobilePaymentService(new MobilePaymentReceiptReconciler($notifier));
    }
}
