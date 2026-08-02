<?php

namespace Tests\Feature;

use App\Exceptions\SecurePaymentException;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Models\Admin;
use App\Models\Admin\Invoice;
use App\Services\InvoiceService;
use App\Services\SecureMobilePaymentService;
use App\Services\WhatsApp\PaymentReceiptNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Mockery;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Throwable;

class SecureMobilePaymentConcurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl is required for the concurrency test.');
        }
        if (config('database.default') !== 'mysql'
            || ! str_contains((string) config('database.connections.mysql.database'), '_test')) {
            throw new \RuntimeException('Concurrency tests require a dedicated MySQL test database.');
        }
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    public function test_two_different_keys_cannot_collect_the_same_invoice_twice(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $results = $this->runConcurrently(
            $collector->id,
            $invoice->id,
            [(string) Str::uuid(), (string) Str::uuid()],
        );

        $this->assertSame(['invoice_already_paid', 'success'], $this->resultKinds($results));
        $this->assertFinancialCounts($invoice->id);
    }

    public function test_two_simultaneous_requests_with_the_same_key_commit_once_and_replay_once(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        $key = (string) Str::uuid();

        $results = $this->runConcurrently(
            $collector->id,
            $invoice->id,
            [$key, $key],
        );

        $this->assertSame(['replay', 'success'], $this->resultKinds($results));
        $this->assertFinancialCounts($invoice->id);
        $this->assertSame(
            $results[0]['reference'],
            $results[1]['reference'],
            'Idempotent responses must return the same payment reference.',
        );
    }

    public function test_mobile_and_admin_channels_cannot_collect_the_same_balance_twice(): void
    {
        [$collector, $invoice] = $this->paymentFixture();

        $results = $this->runMobileAndAdminConcurrently($collector->id, $invoice->id);

        $this->assertCount(1, array_filter($results, fn (array $result): bool => str_ends_with($result['kind'], '_success')));
        $this->assertDatabaseHas('tbl_invoices', [
            'id' => $invoice->id,
            'paid_amount' => 100,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);
        $this->assertSame(1, DB::table('tbl_revenues')->where('invoice_id', $invoice->id)->count());
        $this->assertSame(1, DB::table('tbl_financial_transactions')->count());
    }

    public function test_admin_controller_loser_emits_no_false_payment_side_effects(): void
    {
        [$collector, $invoice] = $this->paymentFixture();
        app(SecureMobilePaymentService::class)->collectFullRemaining(
            $invoice->id,
            $collector,
            '75.00',
            (string) Str::uuid(),
        );
        Notification::fake();
        $notifier = Mockery::mock(PaymentReceiptNotifier::class);
        $notifier->shouldNotReceive('notify');
        $this->app->instance(PaymentReceiptNotifier::class, $notifier);
        auth('admin')->login($collector);

        $request = Request::create('/admin/payment-test', 'POST', [
            'invoice_amount' => '100.00',
            'paid_amount' => '75.00',
            'paid_date' => now()->toDateString(),
        ]);
        $response = app(AdminInvoiceController::class)->pay_invoice($invoice->id, $request);

        $this->assertTrue($response->getSession()?->has('error') ?? false);
        Notification::assertNothingSent();
        $this->assertSame(1, DB::table('tbl_revenues')->where('invoice_id', $invoice->id)->count());
        $this->assertSame(1, DB::table('tbl_financial_transactions')->count());
        $this->assertSame(1, DB::table('logs')->where('action', 'mobile_invoice_paid')->count());
        $this->assertSame(0, DB::table('logs')->where('action', 'invoice_paid')->count());
        $this->assertSame(0, DB::table('whatsapp_message_logs')->count());
    }

    private function runMobileAndAdminConcurrently(int $collectorId, int $invoiceId): array
    {
        $directory = sys_get_temp_dir().'/tahseel-cross-channel-'.Str::uuid();
        mkdir($directory, 0700, true);
        $startFile = $directory.'/start';
        $pids = [];

        DB::disconnect();
        foreach (['mobile', 'admin'] as $index => $channel) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->fail('Unable to fork cross-channel payment worker.');
            }
            if ($pid === 0) {
                while (! file_exists($startFile)) {
                    usleep(1000);
                }
                DB::purge();
                DB::reconnect();
                try {
                    $collector = Admin::query()->findOrFail($collectorId);
                    if ($channel === 'mobile') {
                        app(SecureMobilePaymentService::class)->collectFullRemaining(
                            $invoiceId,
                            $collector,
                            '75.00',
                            (string) Str::uuid(),
                        );
                        $kind = 'mobile_success';
                    } else {
                        auth('admin')->login($collector);
                        $request = Request::create('/admin/payment-test', 'POST', [
                            'invoice_amount' => '100.00',
                            'paid_amount' => '75.00',
                            'paid_date' => now()->toDateString(),
                        ]);
                        $response = app(InvoiceService::class)->payInvoice($invoiceId, $request);
                        $kind = $response->getSession()?->has('error')
                            ? 'admin_rejected'
                            : 'admin_success';
                    }
                } catch (SecurePaymentException $exception) {
                    $kind = $channel.'_rejected';
                } catch (Throwable $exception) {
                    $kind = $channel.'_unexpected:'.get_class($exception);
                }
                file_put_contents($directory.'/result-'.$index.'.json', json_encode(['kind' => $kind]));
                exit(0);
            }
            $pids[] = $pid;
        }

        touch($startFile);
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
            $this->assertTrue(pcntl_wifexited($status));
            $this->assertSame(0, pcntl_wexitstatus($status));
        }
        DB::purge();
        DB::reconnect();

        $results = [];
        foreach ([0, 1] as $index) {
            $results[] = json_decode(file_get_contents($directory.'/result-'.$index.'.json'), true, flags: JSON_THROW_ON_ERROR);
        }
        foreach (glob($directory.'/*') as $file) {
            unlink($file);
        }
        rmdir($directory);

        return $results;
    }

    private function runConcurrently(int $collectorId, int $invoiceId, array $keys): array
    {
        $directory = sys_get_temp_dir().'/tahseel-payment-'.Str::uuid();
        mkdir($directory, 0700, true);
        $startFile = $directory.'/start';
        $pids = [];

        DB::disconnect();
        foreach ($keys as $index => $key) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->fail('Unable to fork payment worker.');
            }
            if ($pid === 0) {
                while (! file_exists($startFile)) {
                    usleep(1000);
                }
                DB::purge();
                DB::reconnect();
                try {
                    $collector = Admin::query()->findOrFail($collectorId);
                    $result = app(SecureMobilePaymentService::class)->collectFullRemaining(
                        $invoiceId,
                        $collector,
                        '75.00',
                        $key,
                    );
                    $payload = [
                        'kind' => $result['replayed'] ? 'replay' : 'success',
                        'reference' => $result['reference'],
                    ];
                } catch (SecurePaymentException $exception) {
                    $payload = ['kind' => $exception->errorCode, 'reference' => null];
                } catch (Throwable $exception) {
                    $payload = ['kind' => 'unexpected:'.get_class($exception), 'reference' => null];
                }
                file_put_contents($directory.'/result-'.$index.'.json', json_encode($payload));
                exit(0);
            }
            $pids[] = $pid;
        }

        touch($startFile);
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
            $this->assertTrue(pcntl_wifexited($status));
            $this->assertSame(0, pcntl_wexitstatus($status));
        }

        DB::purge();
        DB::reconnect();
        $results = [];
        foreach (array_keys($keys) as $index) {
            $results[] = json_decode(
                file_get_contents($directory.'/result-'.$index.'.json'),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        }
        foreach (glob($directory.'/*') as $file) {
            unlink($file);
        }
        rmdir($directory);

        return $results;
    }

    private function resultKinds(array $results): array
    {
        $kinds = array_column($results, 'kind');
        sort($kinds);

        return $kinds;
    }

    private function assertFinancialCounts(int $invoiceId): void
    {
        $this->assertDatabaseHas('tbl_invoices', [
            'id' => $invoiceId,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);
        $this->assertSame(1, DB::table('mobile_payment_operations')->count());
        $this->assertSame(1, DB::table('tbl_revenues')->count());
        $this->assertSame(1, DB::table('tbl_financial_transactions')->count());
        $this->assertSame(1, DB::table('logs')->where('action', 'mobile_invoice_paid')->count());
    }

    private function paymentFixture(): array
    {
        $accountId = DB::table('tbl_accounts')->insertGetId([
            'name' => 'Concurrency account',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $collector = Admin::query()->create([
            'name' => 'Concurrency Collector',
            'email' => 'concurrency@example.test',
            'password' => bcrypt('test-password'),
            'status' => '1',
            'account_id' => $accountId,
        ]);
        Permission::findOrCreate('pay_invoice', 'admin');
        $collector->givePermissionTo('pay_invoice');
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'Concurrency Client',
            'phone' => null,
            'subscription_id' => 1,
            'price' => 100,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->subMonth()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $invoice = Invoice::query()->create([
            'invoice_number' => 'RACE-'.Str::upper(Str::random(10)),
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
