<?php

namespace App\Console\Commands;

use App\Services\TelegramApiClient;
use App\Services\TelegramBotService;
use App\Services\TelegramConfig;
use Illuminate\Console\Command;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Poll Telegram for bot updates continuously';

    protected TelegramBotService $botService;
    protected TelegramApiClient $api;

    /** Offset persistence so restarts don't re-process old updates. */
    protected const OFFSET_FILE = 'app/telegram_poll_offset';

    public function __construct(TelegramBotService $botService, TelegramApiClient $api)
    {
        parent::__construct();
        $this->botService = $botService;
        $this->api = $api;
    }

    public function handle()
    {
        $this->info('Telegram poll bot started');

        $offset = $this->readOffset();

        while (true) {
            try {
                // The process is long-running; reload DB-backed settings each
                // cycle so enable/disable and token changes take effect.
                TelegramConfig::clearCache();
                if (!TelegramConfig::enabled()) {
                    sleep(5);
                    continue;
                }

                $updates = $this->api->getUpdates($offset);

                foreach ($updates as $update) {
                    $updateId = $update['update_id'] ?? null;
                    if (isset($update['inline_query'])) {
                        $this->botService->handleInlineQuery($update['inline_query']);
                    } elseif (isset($update['message']['text'])) {
                        $this->botService->handleMessage($update['message']);
                    } elseif (isset($update['channel_post']['text'])) {
                        // Telegram sends channel messages as channel_post,
                        // not message. Treat the post like a normal message
                        // so @botname Client works in channels too.
                        $this->botService->handleMessage($update['channel_post']);
                    }

                    // Acknowledge only after successful processing. If the
                    // handler throws, the update is retried after restart.
                    if ($updateId) {
                        $offset = $updateId + 1;
                        $this->writeOffset($offset);
                    }
                }
            } catch (\Throwable $e) {
                $this->error('Poll error: ' . $e->getMessage());
                \Log::error('Telegram poll exception: ' . $e->getMessage());
            }

            sleep(2);
        }
    }

    protected function readOffset(): int
    {
        $path = storage_path(self::OFFSET_FILE);
        if (!file_exists($path)) {
            return 0;
        }
        return max(0, (int) file_get_contents($path));
    }

    protected function writeOffset(int $offset): void
    {
        @file_put_contents(storage_path(self::OFFSET_FILE), (string) $offset);
    }
}
