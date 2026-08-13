<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\DB;

class WhatsAppQueueState
{
    public function paused(): bool
    {
        return DB::table('app_config')
            ->where('key', 'whatsapp_queue_paused')
            ->value('value') === '1';
    }

    public function setPaused(bool $paused): bool
    {
        DB::table('app_config')->updateOrInsert(
            ['key' => 'whatsapp_queue_paused'],
            ['value' => $paused ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]
        );

        return $paused;
    }

    public function toggle(): bool
    {
        return $this->setPaused(! $this->paused());
    }
}
