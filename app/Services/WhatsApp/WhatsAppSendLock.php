<?php

namespace App\Services\WhatsApp;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class WhatsAppSendLock
{
    private const NAME = 'whatsapp:global-send-lock';

    private const LEASE_SECONDS = 300;

    public function __construct(private readonly int $waitSeconds = 30) {}

    public function run(Closure $operation): mixed
    {
        $lock = Cache::lock(self::NAME, self::LEASE_SECONDS);

        if ($this->waitSeconds === 0) {
            if (! $lock->get()) {
                return null;
            }

            try {
                return $operation();
            } finally {
                $lock->release();
            }
        }

        try {
            return $lock->block($this->waitSeconds, $operation);
        } catch (LockTimeoutException) {
            return null;
        }
    }
}
