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
        ]);
    }

    public function test_send_text_posts_to_the_inbox_messages_endpoint(): void
    {
        Http::fake([
            // Exact POST route first — the wildcard list pattern would swallow it.
            'https://zernio.com/api/v1/inbox/conversations/conv123/messages' => Http::response([
                'success' => true,
                'data' => ['messageId' => 'wamid.TEST123'],
            ], 200),
            'https://zernio.com/api/v1/inbox/conversations*' => Http::response([
                'data' => [[
                    'id' => 'conv123',
                    'participantId' => '96170781562',
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
}
