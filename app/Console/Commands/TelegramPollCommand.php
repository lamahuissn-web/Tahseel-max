<?php

namespace App\Console\Commands;

use App\Models\AppConfig;
use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Poll Telegram for bot updates continuously';

    protected $botService;
    protected $offset = 0;

    public function __construct(TelegramBotService $botService)
    {
        parent::__construct();
        $this->botService = $botService;
    }

    public function handle()
    {
        $this->info('Telegram poll bot started');

        while (true) {
            try {
                $enabled = AppConfig::where('key', 'telegram_enabled')->value('value');
                if ($enabled != '1') {
                    sleep(5);
                    continue;
                }

                $updates = getTelegramUpdates($this->offset);

                foreach ($updates as $update) {
                    $updateId = $update['update_id'] ?? null;
                    if ($updateId) {
                        $this->offset = $updateId + 1;
                    }

                    if (isset($update['inline_query'])) {
                        $this->botService->handleInlineQuery($update['inline_query']);
                    } elseif (isset($update['message']['text'])) {
                        $this->botService->handleMessage($update['message']);
                    }
                }
            } catch (\Exception $e) {
                $this->error('Poll error: ' . $e->getMessage());
                \Log::error('Telegram poll exception: ' . $e->getMessage());
            }

            sleep(2);
        }
    }
}
