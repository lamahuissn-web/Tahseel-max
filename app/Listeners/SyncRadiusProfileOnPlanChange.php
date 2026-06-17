<?php

namespace App\Listeners;

use App\Models\Clients;
use App\Services\Radius\ProfileService;
use Illuminate\Support\Facades\Log;

class SyncRadiusProfileOnPlanChange
{
    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Handle client update events
     * Triggered when subscription_id changes on a client
     */
    public function handle($event): void
    {
        $client = null;

        if (property_exists($event, 'client')) {
            $client = $event->client;
        } elseif (method_exists($event, 'getClient')) {
            $client = $event->getClient();
        }

        if (!$client || !($client instanceof Clients)) {
            if (property_exists($event, 'model') && $event->model instanceof Clients) {
                $client = $event->model;
            } else {
                return;
            }
        }

        if (!$client->sas_username || !$client->subscription_id) {
            return;
        }

        try {
            $result = $this->profileService->updateClientOnPlanChange(
                $client->sas_username,
                $client->subscription_id
            );

            if ($result['applied']) {
                Log::info("RADIUS profile synced for client {$client->id} ({$client->sas_username}): {$result['message']}");
            } else {
                Log::info("RADIUS profile check for client {$client->id} ({$client->sas_username}): {$result['message']}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync RADIUS profile for client {$client->id}: " . $e->getMessage());
        }
    }
}
