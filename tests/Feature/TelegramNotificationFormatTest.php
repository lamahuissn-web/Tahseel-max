<?php

namespace Tests\Feature;

use App\Services\Telegram\TelegramMessage;
use App\Services\TelegramApiClient;
use App\Services\TelegramConfig;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

/**
 * Spec 023 — the send boundary: gating, escaping through the helper, and a
 * visible, bounded failure (one retry, one warning, false) that never reaches
 * the caller.
 *
 * No test may touch api.telegram.org: Http::fake() is a belt-and-braces guard
 * next to the bound fake client.
 *
 * Only `app_config` is created locally — the project's migrations cannot run on
 * sqlite in-memory (a `change()` column needs doctrine/dbal), so this suite
 * follows the WhatsAppStabilityTest convention instead of RefreshDatabase.
 */
class TelegramNotificationFormatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This suite owns its schema: run it on an isolated in-memory sqlite DB so
        // it neither depends on the project's MySQL test database nor collides
        // with the app_config rows other suites seed.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('app_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->unique()->nullable();
            $table->text('value')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Http::fake();
        TelegramConfig::clearCache(); // static memo survives between tests
        $this->seedTelegramConfig();
    }

    private function seedTelegramConfig(array $overrides = []): void
    {
        $values = array_merge([
            'telegram_enabled' => '1',
            'telegram_chat_id' => '-1004377540472',
            'telegram_bot_token' => 'test:token',
            'telegram_notify_invoice_paid' => '1',
        ], $overrides);

        foreach ($values as $key => $value) {
            DB::table('app_config')->updateOrInsert(['key' => $key], ['value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }

        TelegramConfig::clearCache();
    }

    private function message(): TelegramMessage
    {
        return TelegramMessage::make('invoice_paid')
            ->field('العميل', 'ابو محمود سلمون W.K')
            ->field('الفاتورة', '#19000')
            ->amount('25.00', '$')
            ->actor('قاسم محمد عامر');
    }

    public function test_helper_sends_the_built_payload_instead_of_a_raw_string(): void
    {
        $client = Mockery::mock(TelegramApiClient::class);
        $client->shouldReceive('sendMessage')
            ->once()
            ->with('-1004377540472', Mockery::on(fn ($text) => str_contains($text, 'دفعة عبر الويب')
                && str_contains($text, '<b>25.00 $</b>')), 'HTML')
            ->andReturn(['ok' => true, 'result' => ['message_id' => 1]]);
        $this->app->instance(TelegramApiClient::class, $client);

        $this->assertTrue(sendTelegramNotification($this->message(), 'invoice_paid'));
    }

    public function test_a_rejected_message_retries_once_logs_and_returns_false(): void
    {
        Log::spy();
        $client = Mockery::mock(TelegramApiClient::class);
        $client->shouldReceive('sendMessage')->twice()->andReturn(false);
        $this->app->instance(TelegramApiClient::class, $client);

        $this->assertFalse(sendTelegramNotification($this->message(), 'invoice_paid'));
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_send_is_skipped_when_the_master_switch_is_off(): void
    {
        $this->seedTelegramConfig(['telegram_enabled' => '0']);

        $client = Mockery::mock(TelegramApiClient::class);
        $client->shouldNotReceive('sendMessage');
        $this->app->instance(TelegramApiClient::class, $client);

        $this->assertFalse(sendTelegramNotification($this->message(), 'invoice_paid'));
    }

    public function test_send_is_skipped_when_the_event_switch_is_off(): void
    {
        $this->seedTelegramConfig(['telegram_notify_invoice_paid' => '0']);

        $client = Mockery::mock(TelegramApiClient::class);
        $client->shouldNotReceive('sendMessage');
        $this->app->instance(TelegramApiClient::class, $client);

        $this->assertFalse(sendTelegramNotification($this->message(), 'invoice_paid'));
    }

    public function test_an_unescaped_name_can_no_longer_break_the_send(): void
    {
        $client = Mockery::mock(TelegramApiClient::class);
        $client->shouldReceive('sendMessage')
            ->once()
            ->with('-1004377540472', Mockery::on(fn ($text) => str_contains($text, '&amp;') && ! str_contains($text, 'A & B')), 'HTML')
            ->andReturn(['ok' => true, 'result' => ['message_id' => 2]]);
        $this->app->instance(TelegramApiClient::class, $client);

        $message = TelegramMessage::make('client_added')
            ->field('العميل', 'A & B')
            ->amount('20.00', '$');

        $this->assertTrue(sendTelegramNotification($message, 'invoice_paid'));
    }

    /**
     * Guard: no call site may slip back to a raw sprintf string. A new alert
     * added with the old shape would silently reintroduce the parse failure.
     */
    public function test_every_call_site_passes_a_built_message(): void
    {
        $offenders = [];
        $total = 0;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path('app')));
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            if (! str_contains($source, 'sendTelegramNotification(')) {
                continue;
            }

            preg_match_all('/sendTelegramNotification\((.*?)(?:,\s*[\'"][a-z_]+[\'"]\s*\)|\))/s', $source, $m);

            foreach ($m[1] as $arg) {
                if (str_contains($file->getPathname(), 'notification_helper.php')
                    || str_contains($file->getPathname(), 'Services/Telegram/')) {
                    continue; // the definition and the builder's own pass-through
                }

                $total++;

                if (! str_contains($arg, 'TelegramMessage::make(') && ! str_contains($arg, '$this')) {
                    $offenders[] = str_replace(base_path().'/', '', $file->getPathname()) . ' → ' . trim(substr($arg, 0, 60));
                }
            }
        }

        $this->assertSame([], $offenders, "every call site must pass TelegramMessage:\n".implode("\n", $offenders));
        $this->assertSame(18, $total, 'the eighteen call sites must all still be present');
    }
}
