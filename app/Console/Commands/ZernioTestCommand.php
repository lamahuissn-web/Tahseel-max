<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\ZernioService;
use Illuminate\Console\Command;

/**
 * SPIKE: drive the Zernio WhatsApp adapter from the CLI.
 *
 *   php artisan zernio:test +96170781562 --message="Hello from spike"
 *   php artisan zernio:test +96170781562 --check-only
 *
 * Requires ZERNIO_API_KEY / ZERNIO_ACCOUNT_ID in .env (test key only).
 */
class ZernioTestCommand extends Command
{
    protected $signature = 'zernio:test {phone} {--message=Spike test from Tahseel via Zernio} {--check-only}';

    protected $description = 'SPIKE: test the Zernio WhatsApp adapter against the sandbox';

    public function handle(ZernioService $zernio): int
    {
        $status = $zernio->status();
        $this->line('Status: '.json_encode($status));

        if (! ($status['ok'] ?? false)) {
            $this->error('Zernio sandbox not active — check key/account or activate a sandbox session.');

            return 1;
        }

        $this->info('Sandbox connected ('.$status['sandboxNumber'].').');

        if ($this->option('check-only')) {
            return 0;
        }

        $result = $zernio->sendText($this->argument('phone'), (string) $this->option('message'));

        if ($result['ok']) {
            $this->info('Sent OK — messageId: '.$result['messageId']);

            return 0;
        }

        $this->error('Send failed: '.$result['error']);

        return 1;
    }
}
