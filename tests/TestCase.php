<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Queue;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * SAFETY GUARD (post-incident 2026-08-21): any test that resolves the real
     * `whatsapp_database` queue must never push a real job that a live worker could
     * pick up and send as a real WhatsApp message. This fakes that queue globally so
     * no feature test can dispatch a real send, regardless of mocks or worker state.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake(['whatsapp_database']);
    }
}
