<?php

namespace Tests\Feature;

use App\Services\WhatsApp\ZernioService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZernioSpikeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'zernio.api_key' => 'sk_test_key',
            'zernio.account_id' => 'acct123',
            'zernio.base_url' => 'https://zernio.com/api/v1',
            'zernio.waba_id' => 'waba_test_123',
            'zernio.sandbox' => true,
        ]);
    }

    // ─── sendText (inbox-based, 24h window) ───

    public function test_send_text_posts_to_the_inbox_messages_endpoint(): void
    {
        $testPhone = '96170781562';

        Http::fake([
            // Exact POST route FIRST — wildcard list pattern would swallow it
            'https://zernio.com/api/v1/inbox/conversations/conv123/messages' => Http::response([
                'success' => true,
                'data' => ['messageId' => 'wamid.TEST123'],
            ], 200),
            // Wildcard for GET /inbox/conversations (list) — must come AFTER exact POST
            'https://zernio.com/api/v1/inbox/conversations?*' => Http::response([
                'data' => [[
                    'id' => 'conv123',
                    'participantId' => $testPhone,
                ]],
                'pagination' => ['hasMore' => false, 'nextCursor' => null],
            ], 200),
            'https://zernio.com/api/v1/inbox/conversations' => Http::response([
                'data' => [[
                    'id' => 'conv123',
                    'participantId' => $testPhone,
                ]],
                'pagination' => ['hasMore' => false, 'nextCursor' => null],
            ], 200),
        ]);

        $service = new ZernioService();
        $result = $service->sendText('+96170781562', 'Test receipt');

        $this->assertTrue($result['ok']);
        $this->assertEquals('wamid.TEST123', $result['messageId']);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/inbox/conversations/conv123/messages')
                && $request['message'] === 'Test receipt'
                && $request['accountId'] === 'acct123';
        });
    }

    public function test_send_text_without_open_window_returns_clear_error(): void
    {
        Http::fake([
            'https://zernio.com/api/v1/inbox/conversations*' => Http::response([
                'data' => [],
                'pagination' => ['hasMore' => false, 'nextCursor' => null],
            ], 200),
        ]);

        $service = new ZernioService();
        $result = $service->sendText('+96170781562', 'Hi');

        $this->assertFalse($result['ok']);
        $this->assertNull($result['messageId']);
        $this->assertStringContainsString('template', strtolower((string) $result['error']));
    }

    // ─── sendTemplate (real WABA only) ───

    public function test_send_template_rejected_in_sandbox_mode(): void
    {
        config(['zernio.sandbox' => true]);

        $service = new ZernioService();
        $result = $service->sendTemplate('+96170781562', 'payment_receipt', 'ar', ['$10']);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('sandbox', strtolower((string) $result['error']));
    }

    public function test_send_template_posts_to_inbox_conversations_endpoint(): void
    {
        config(['zernio.sandbox' => false]);

        Http::fake([
            'https://zernio.com/api/v1/inbox/conversations' => Http::response([
                'success' => true,
                'data' => ['messageId' => 'wamid.TPL456'],
            ], 201),
        ]);

        $service = new ZernioService();
        $result = $service->sendTemplate('+96170781562', 'payment_receipt', 'ar', ['$10', '2026-08-17']);

        $this->assertTrue($result['ok']);
        $this->assertEquals('wamid.TPL456', $result['messageId']);

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $request->method() === 'POST'
                && str_contains($request->url(), '/inbox/conversations')
                && $body['participantId'] === '96170781562'
                && $body['templateName'] === 'payment_receipt'
                && $body['templateLanguage'] === 'ar'
                && $body['templateParams'] === ['$10', '2026-08-17'];
        });
    }

    public function test_send_template_without_waba_id_returns_error(): void
    {
        config(['zernio.sandbox' => false, 'zernio.waba_id' => '']);

        $service = new ZernioService();
        $result = $service->sendTemplate('+96170781562', 'payment_receipt');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('WABA_ID', (string) $result['error']);
    }

    public function test_send_template_handles_api_error(): void
    {
        config(['zernio.sandbox' => false]);

        Http::fake([
            'https://zernio.com/api/v1/inbox/conversations' => Http::response([
                'error' => 'Template not found',
                'type' => 'invalid_request_error',
                'code' => 'not_found',
            ], 404),
        ]);

        $service = new ZernioService();
        $result = $service->sendTemplate('+96170781562', 'nonexistent_template');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Template not found', (string) $result['error']);
    }

    // ─── sendSmart (template priority, text fallback) ───

    public function test_send_smart_uses_template_when_template_provided(): void
    {
        config(['zernio.sandbox' => false]);

        Http::fake([
            'https://zernio.com/api/v1/inbox/conversations' => Http::response([
                'success' => true,
                'data' => ['messageId' => 'wamid.SMART1'],
            ], 201),
        ]);

        $service = new ZernioService();
        $result = $service->sendSmart('+96170781562', 'Your receipt', 'payment_receipt');

        $this->assertTrue($result['ok']);
        $this->assertEquals('template', $result['method']);
        $this->assertEquals('wamid.SMART1', $result['messageId']);
    }

    public function test_send_smart_falls_back_to_text_when_template_fails(): void
    {
        config(['zernio.sandbox' => false]);

        Http::fake([
            // Same URL serves GET (findConversation) and POST (template send)
            'https://zernio.com/api/v1/inbox/conversations' => function ($request) {
                if ($request->method() === 'POST') {
                    // Template send fails
                    return Http::response(['error' => 'Template not found'], 404);
                }

                // GET — open conversation exists for free-form text fallback
                return Http::response([
                    'data' => [['id' => 'conv999', 'participantId' => '96170781562']],
                    'pagination' => ['hasMore' => false, 'nextCursor' => null],
                ], 200);
            },
            // Free-form reply succeeds
            'https://zernio.com/api/v1/inbox/conversations/conv999/messages' => Http::response([
                'success' => true,
                'data' => ['messageId' => 'wamid.SMART2'],
            ], 200),
        ]);

        $service = new ZernioService();
        $result = $service->sendSmart(
            '+96170781562',
            'Your receipt for $10',
            'payment_receipt',
            'ar',
            ['$10']
        );

        $this->assertTrue($result['ok']);
        $this->assertEquals('text', $result['method']);
        $this->assertEquals('wamid.SMART2', $result['messageId']);
    }

    public function test_send_smart_fails_when_no_conversation_and_no_template(): void
    {
        config(['zernio.sandbox' => true]);

        Http::fake([
            'https://zernio.com/api/v1/inbox/conversations*' => Http::response([
                'data' => [],
                'pagination' => ['hasMore' => false, 'nextCursor' => null],
            ], 200),
        ]);

        $service = new ZernioService();
        $result = $service->sendSmart('+96170781562', 'Hi');

        $this->assertFalse($result['ok']);
        $this->assertEquals('none', $result['method']);
    }

    // ─── status ───

    public function test_status_sandbox_checks_sandbox_sessions(): void
    {
        config(['zernio.sandbox' => true]);

        Http::fake([
            'https://zernio.com/api/v1/whatsapp/sandbox/sessions' => Http::response([
                'sessions' => [['status' => 'active']],
                'sandboxNumber' => '+12025551234',
            ], 200),
        ]);

        $service = new ZernioService();
        $result = $service->status();

        $this->assertTrue($result['ok']);
        $this->assertEquals('+12025551234', $result['sandboxNumber']);
    }

    public function test_status_real_waba_checks_number_info(): void
    {
        config(['zernio.sandbox' => false]);

        Http::fake([
            'https://zernio.com/api/v1/whatsapp/number-info*' => Http::response([
                'phone' => [
                    'status' => 'CONNECTED',
                    'display_phone_number' => '+96170781562',
                ],
                'waba' => ['id' => 'waba_test_123'],
            ], 200),
        ]);

        $service = new ZernioService();
        $result = $service->status();

        $this->assertTrue($result['ok']);
        $this->assertEquals('+96170781562', $result['phone']);
    }
}
