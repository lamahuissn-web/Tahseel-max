<?php

namespace Tests\Feature;

use App\Services\WhatsApp\WhatsAppRateLimiter;
use App\Services\WhatsApp\WhatsAppSafetySettings;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\WrapsMysqlTransaction;

/**
 * Audit fix #4 (2026-08-21): the rate limiter enable flag was DEAD —
 * WhatsAppSafetySettings::settings() hardcoded 'enabled' => true and never read
 * app_config.whatsapp_rate_limiter_enabled, so the UI toggle lied.
 *
 * These tests prove the flag is now REAL:
 *  - default ON when the key is absent (fail-safe)
 *  - '0' in app_config disables the limiter end-to-end (waitBeforeSend allows)
 *  - setEnabled() persists + audits the change
 *  - preset saves preserve the current flag instead of forcing it back on
 */
class RateLimiterEnabledFlagTest extends TestCase
{
    use WrapsMysqlTransaction;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=tahseel_new');
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'tahseel_new']);
        $this->beginTahseelTransaction();

        // The limiter stores its "next allowed send" timestamp in the FILE cache,
        // which persists across runs. A stale marker makes waitBeforeSend() sleep
        // up to 120s INSIDE this test's DB transaction (→ InnoDB lock timeouts).
        // Flush the limiter state so every test starts from a clean slate.
        foreach ([
            'whatsapp_rate_limiter_next_allowed_at',
            'whatsapp_rate_limiter_batch_pause_until',
            'whatsapp_rate_limiter_last_batch_pause_count',
        ] as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }
    }

    protected function tearDown(): void
    {
        // CRITICAL: without this rollback each test leaves its transaction open,
        // holding row locks on app_config — the NEXT test's UPDATE then times out
        // (InnoDB 1205) after 50s.
        $this->rollbackTahseelTransaction();
        parent::tearDown();
    }

    private function limiter(): WhatsAppRateLimiter
    {
        return app(WhatsAppRateLimiter::class);
    }

    private function setFlag(?string $value): void
    {
        if ($value === null) {
            DB::table('app_config')->where('key', 'whatsapp_rate_limiter_enabled')->delete();

            return;
        }
        DB::table('app_config')->updateOrInsert(
            ['key' => 'whatsapp_rate_limiter_enabled'],
            ['value' => $value, 'updated_at' => now()]
        );
    }

    public function test_flag_defaults_to_enabled_when_key_absent(): void
    {
        $this->setFlag(null);
        $settings = app(WhatsAppSafetySettings::class)->settings();

        $this->assertTrue($settings['enabled'], 'absent key must default to ON (fail-safe)');
        $result = $this->limiter()->waitBeforeSend([]);
        // allowed=true here means "not blocked by caps" — but crucially it must NOT
        // report 'Rate limiter disabled'.
        $this->assertNotSame('Rate limiter disabled', $result['reason'] ?? '');
    }

    public function test_flag_off_disables_limiter_end_to_end(): void
    {
        $this->setFlag('0');
        $settings = app(WhatsAppSafetySettings::class)->settings();
        $this->assertFalse($settings['enabled']);

        $result = $this->limiter()->waitBeforeSend([]);
        $this->assertTrue($result['allowed']);
        $this->assertSame('Rate limiter disabled', $result['reason'] ?? '');
    }

    public function test_set_enabled_persists_and_audits(): void
    {
        $this->setFlag('1');

        app(WhatsAppSafetySettings::class)->setEnabled(false, [
            'admin_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);

        $row = DB::table('app_config')->where('key', 'whatsapp_rate_limiter_enabled')->first();
        $this->assertSame('0', $row->value);
        $this->assertFalse(app(WhatsAppSafetySettings::class)->settings()['enabled']);

        $audit = DB::table('logs')
            ->where('action', 'whatsapp_safety_settings_updated')
            ->latest('id')
            ->first();
        $this->assertNotNull($audit, 'toggle must write an audit row');
        $this->assertStringContainsString('"enabled":false', $audit->new_data);

        // And the limiter actually stops limiting:
        $this->assertSame('Rate limiter disabled', $this->limiter()->waitBeforeSend([])['reason']);
    }

    public function test_preset_save_preserves_current_flag(): void
    {
        $this->setFlag('0'); // admin disabled the limiter

        app(WhatsAppSafetySettings::class)->save(
            ['preset' => 'balanced'],
            ['admin_id' => null, 'ip_address' => '127.0.0.1', 'user_agent' => 'test']
        );

        // Preset saved, but flag must remain OFF — not silently re-enabled.
        $settings = app(WhatsAppSafetySettings::class)->settings();
        $this->assertFalse($settings['enabled'], 'preset save must not resurrect a disabled limiter');
        $this->assertSame('balanced', $settings['preset']);
    }
}
