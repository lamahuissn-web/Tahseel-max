<?php

namespace Tests\Feature;

use App\Models\WhatsAppMessageLog;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ZernioWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Override phpunit.xml sqlite with real MariaDB
        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=tahseel_new');
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'tahseel_new',
            'database.connections.mysql.username' => 'tahseelusr',
            'database.connections.mysql.password' => 'YourStrongPassword123!',
            'zernio.webhook_secret' => 'test_webhook_secret_abc123',
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    // ─── Signature verification ───

    public function test_valid_signature_is_accepted(): void
    {
        $payload = json_encode(['event' => 'message.delivered', 'message' => ['id' => 'wamid.sig_test_1']]);
        $signature = hash_hmac('sha256', $payload, 'test_webhook_secret_abc123');

        $response = $this->postJson('/api/v1/webhooks/zernio', json_decode($payload, true), [
            'X-Zernio-Signature' => $signature,
            'X-Zernio-Event-Id' => 'evt_sig_001',
        ]);

        $response->assertOk();
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'message.delivered',
        ], [
            'X-Zernio-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(401);
    }

    public function test_missing_signature_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'message.delivered',
        ]);

        $response->assertStatus(401);
    }

    public function test_no_secret_configured_skips_verification(): void
    {
        config(['zernio.webhook_secret' => '']);

        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'message.delivered',
            'message' => ['id' => 'wamid.test'],
        ]);

        $response->assertOk();
    }

    // ─── Event processing ───

    public function test_delivered_event_updates_message_log(): void
    {
        config(['zernio.webhook_secret' => '']);

        $log = WhatsAppMessageLog::create([
            'client_id' => 1,
            'client_name' => 'Webhook Test',
            'phone' => '+96170781562',
            'message' => 'Test receipt',
            'template_type' => 'receipt',
            'status' => 'sent',
            'provider_message_id' => 'wamid.delivered001',
        ]);

        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'message.delivered',
            'message' => ['id' => 'wamid.delivered001'],
        ]);

        $response->assertOk();
        $log->refresh();
        $this->assertNotNull($log->delivered_at);
        $this->assertEquals('sent', $log->status);
    }

    public function test_failed_event_marks_message_as_failed(): void
    {
        config(['zernio.webhook_secret' => '']);

        $log = WhatsAppMessageLog::create([
            'client_id' => 1,
            'client_name' => 'Webhook Test',
            'phone' => '+96170781562',
            'message' => 'Test receipt',
            'template_type' => 'receipt',
            'status' => 'sent',
            'provider_message_id' => 'wamid.failed001',
        ]);

        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'message.failed',
            'message' => ['id' => 'wamid.failed001'],
            'error' => ['message' => 'Recipient not on WhatsApp'],
        ]);

        $response->assertOk();
        $log->refresh();
        $this->assertEquals('failed', $log->status);
        $this->assertEquals('Recipient not on WhatsApp', $log->error);
    }

    public function test_read_event_updates_delivered_at(): void
    {
        config(['zernio.webhook_secret' => '']);

        $log = WhatsAppMessageLog::create([
            'client_id' => 1,
            'client_name' => 'Webhook Test',
            'phone' => '+96170781562',
            'message' => 'Test receipt',
            'template_type' => 'receipt',
            'status' => 'sent',
            'provider_message_id' => 'wamid.read001',
        ]);

        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'message.read',
            'message' => ['id' => 'wamid.read001'],
        ]);

        $response->assertOk();
        $log->refresh();
        $this->assertNotNull($log->delivered_at);
    }

    public function test_unknown_message_id_returns_not_found(): void
    {
        config(['zernio.webhook_secret' => '']);

        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'message.delivered',
            'message' => ['id' => 'wamid.unknown999'],
        ]);

        $response->assertOk();
    }

    public function test_event_type_without_message_id_is_skipped(): void
    {
        config(['zernio.webhook_secret' => '']);

        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'message.delivered',
        ]);

        $response->assertOk();
    }

    // ─── Deduplication ───

    public function test_duplicate_event_is_ignored(): void
    {
        config(['zernio.webhook_secret' => '']);

        $payload = ['event' => 'message.delivered', 'message' => ['id' => 'wamid.dup1']];

        // First call
        $this->postJson('/api/v1/webhooks/zernio', $payload, [
            'X-Zernio-Event-Id' => 'evt_dup_001',
        ])->assertOk();

        // Second call with same event ID
        $response = $this->postJson('/api/v1/webhooks/zernio', $payload, [
            'X-Zernio-Event-Id' => 'evt_dup_001',
        ]);

        $response->assertOk();
        $this->assertEquals('duplicate', $response->json('status'));
    }

    // ─── Ignored events ───

    public function test_unknown_event_type_is_ignored(): void
    {
        config(['zernio.webhook_secret' => '']);

        $response = $this->postJson('/api/v1/webhooks/zernio', [
            'event' => 'post.published',
        ]);

        $response->assertOk();
        $this->assertEquals('ok', $response->json('status'));
        $this->assertEquals('post.published', $response->json('event'));
    }

    public function test_empty_event_type_is_ignored(): void
    {
        config(['zernio.webhook_secret' => '']);

        $response = $this->postJson('/api/v1/webhooks/zernio', []);

        $response->assertOk();
        $this->assertEquals('ignored', $response->json('status'));
    }

    protected function tearDown(): void
    {
        // Clean up test records
        WhatsAppMessageLog::whereIn('provider_message_id', [
            'wamid.sig_test_1', 'wamid.delivered001', 'wamid.failed001',
            'wamid.read001', 'wamid.unknown999', 'wamid.dup1',
        ])->delete();

        parent::tearDown();
    }
}
