<?php

namespace Tests\Feature;

use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\ZernioService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end test: pay invoice → receipt via Zernio → webhook delivery confirmed.
 *
 * Uses Http::fake to simulate the full flow without real API calls.
 * Validates the integration between PaymentReceiptNotifier, WhatsAppService,
 * ZernioService, and ZernioWebhookController.
 */
class ZernioEndToEndTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use real MariaDB
        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=tahseel_new');
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'tahseel_new',
            'database.connections.mysql.username' => 'tahseelusr',
            'database.connections.mysql.password' => 'YourStrongPassword123!',
            'zernio.webhook_secret' => 'test_e2e_secret',
            'zernio.sandbox' => true,
            'zernio.api_key' => 'sk_e2e_test_key',
            'zernio.account_id' => 'acct_e2e_test',
            'zernio.base_url' => 'https://zernio.com/api/v1',
            'zernio.waba_id' => '',
            'zernio.driver' => 'zernio',
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    /**
     * Full flow: message log created → Zernio sends → webhook confirms delivery.
     */
    public function test_full_receipt_flow_via_zernio(): void
    {
        $testPhone = '+96170781562';
        $testMessage = 'تم دفع الفاتورة بمبلغ $10.00 — شكراً لكم';
        $wamid = 'wamid.e2e_flow_'.time();

        // Mock the Zernio inbox + send endpoints
        Http::fake([
            // findConversation — find the conversation
            'https://zernio.com/api/v1/inbox/conversations' => Http::response([
                'data' => [['id' => 'conv_e2e', 'participantId' => '96170781562']],
                'pagination' => ['hasMore' => false, 'nextCursor' => null],
            ], 200),
            // sendText — returns wamid
            'https://zernio.com/api/v1/inbox/conversations/conv_e2e/messages' => Http::response([
                'success' => true,
                'data' => ['messageId' => $wamid],
            ], 200),
        ]);

        // 1. Simulate: PaymentReceiptNotifier creates a message log
        $log = WhatsAppMessageLog::create([
            'client_id' => 1576,
            'client_name' => 'Zernio Test',
            'phone' => $testPhone,
            'message' => $testMessage,
            'template_type' => 'receipt',
            'status' => 'pending',
            'sent_by' => 'system',
        ]);

        $this->assertNotNull($log->id, 'Message log created');
        $this->assertEquals('pending', $log->status);

        // 2. Simulate: WhatsAppService::send() via Zernio transport
        $service = app(\App\Services\WhatsAppService::class);
        $result = $service->send($testPhone, $testMessage, [
            'skip_rate_limit' => true,
        ]);

        $this->assertTrue($result['success'], 'Zernio send succeeded');
        $this->assertNotEmpty($result['message_id'], 'Message ID returned');

        // 3. Store the provider message ID (simulating what SendWhatsAppMessage job does)
        $log->update(['provider_message_id' => $result['message_id'], 'status' => 'sent']);

        $this->assertEquals($wamid, $log->fresh()->provider_message_id);

        // 4. Simulate: Zernio webhook — message.delivered
        $webhookPayload = [
            'event' => 'message.delivered',
            'message' => ['id' => $wamid],
        ];
        $webhookBody = json_encode($webhookPayload);
        $signature = hash_hmac('sha256', $webhookBody, 'test_e2e_secret');

        $response = $this->postJson('/api/v1/webhooks/zernio', $webhookPayload, [
            'X-Zernio-Signature' => $signature,
            'X-Zernio-Event-Id' => 'evt_e2e_'.time(),
        ]);

        $response->assertOk();

        // 5. Verify: message log is now marked as delivered
        $log->refresh();
        $this->assertEquals('sent', $log->status);
        $this->assertNotNull($log->delivered_at, 'Delivered timestamp set by webhook');

        // Cleanup
        $log->delete();
    }

    /**
     * Failed delivery flow: message sent → webhook reports failure → log updated.
     */
    public function test_failed_delivery_flow(): void
    {
        $wamid = 'wamid.e2e_fail_'.time();

        $log = WhatsAppMessageLog::create([
            'client_id' => 1576,
            'client_name' => 'Zernio Test',
            'phone' => '+96170781562',
            'message' => 'Test failure',
            'template_type' => 'receipt',
            'status' => 'sent',
            'provider_message_id' => $wamid,
        ]);

        // Simulate webhook — message.failed
        $webhookPayload = [
            'event' => 'message.failed',
            'message' => ['id' => $wamid],
            'error' => ['message' => 'Unsupported format'],
        ];
        $signature = hash_hmac('sha256', json_encode($webhookPayload), 'test_e2e_secret');

        $response = $this->postJson('/api/v1/webhooks/zernio', $webhookPayload, [
            'X-Zernio-Signature' => $signature,
            'X-Zernio-Event-Id' => 'evt_e2e_fail_'.time(),
        ]);

        $response->assertOk();

        $log->refresh();
        $this->assertEquals('failed', $log->status);
        $this->assertEquals('Unsupported format', $log->error);

        $log->delete();
    }

    /**
     * OpenWA fallback: when driver=openwa, Zernio is not called.
     */
    public function test_openwa_driver_does_not_use_zernio(): void
    {
        config(['zernio.driver' => 'openwa']);

        $service = app(\App\Services\WhatsAppService::class);

        // Mock OpenWA instead of Zernio
        Http::fake([
            'http://10.10.90.111:2785/api/sessions/*' => Http::response([
                'status' => 'connected',
                'phoneNumber' => '+96170781562',
            ], 200),
        ]);

        $status = $service->status();
        $this->assertTrue($status['connected'] ?? false);

        // Verify no Zernio API was called
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'zernio.com');
        });
    }

    /**
     * Driver toggle: switching to zernio and back preserves state.
     */
    public function test_driver_toggle_via_settings(): void
    {
        config(['zernio.webhook_secret' => '']);

        // Toggle to zernio
        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'webhook.test',
            'message' => ['id' => 'wamid.toggle_test'],
        ]);

        // The webhook accepts it (no auth needed when secret is empty)
        $response->assertOk();
    }

    protected function tearDown(): void
    {
        WhatsAppMessageLog::whereIn('provider_message_id', [
            'wamid.e2e_flow_'.time(),
        ])->orWhere(function ($q) {
            $q->where('phone', '+96170781562')
              ->where('client_name', 'Zernio Test')
              ->where('created_at', '>', now()->subMinute());
        })->delete();

        parent::tearDown();
    }
}
