<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Queue;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * SAFETY GUARD (post-incident 2026-08-21, hardened after audit):
     * fake ALL WhatsApp queue jobs in EVERY test so no test can ever push a real
     * job to the whatsapp_database connection — regardless of mocks, worker state,
     * or transaction state.
     *
     * NOTE: Queue::fake()'s array argument is a list of JOB CLASS NAMES (checked via
     * instanceof), NOT connection names. The earlier Queue::fake(['whatsapp_database'])
     * was a silent NO-OP for that reason. Faking the concrete job class is what
     * actually intercepts SendWhatsAppMessage pushes (including pushOn('whatsapp', ...)).
     */
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake([\App\Jobs\SendWhatsAppMessage::class]);
    }
}
