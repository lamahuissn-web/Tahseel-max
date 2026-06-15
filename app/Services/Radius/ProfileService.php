<?php

namespace App\Services\Radius;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Manage RADIUS profiles linked to Tahseel subscriptions
 * 
 * Each subscription can have a radius_profile (e.g. "10M", "20M") and
 * a radius_speed (e.g. "10M/10M"). When the profile changes, this service
 * updates radusergroup + radreply + sends CoA.
 */
class ProfileService
{
    /**
     * Apply a RADIUS profile to a user
     */
    public function applyProfile(string $username, string $profileName): bool
    {
        try {
            // 1. Remove old group assignment
            DB::connection('radius')
                ->table('radusergroup')
                ->where('username', $username)
                ->delete();

            // 2. Assign new group
            DB::connection('radius')
                ->table('radusergroup')
                ->insert([
                    'username'  => $username,
                    'groupname' => $profileName,
                    'priority'  => 1,
                ]);

            // 3. Get speed from subscription
            $speed = DB::table('tbl_subscriptions')
                ->where('radius_profile', $profileName)
                ->value('radius_speed');

            if ($speed) {
                // Remove old speed attribute
                DB::connection('radius')
                    ->table('radreply')
                    ->where('username', $username)
                    ->where('attribute', 'Mikrotik-Rate-Limit')
                    ->delete();

                // Insert new speed
                DB::connection('radius')
                    ->table('radreply')
                    ->insert([
                        'username'  => $username,
                        'attribute' => 'Mikrotik-Rate-Limit',
                        'op'        => ':=',
                        'value'     => $speed,
                    ]);
            }

            // 4. CoA to apply changes immediately
            try {
                app(RadiusService::class)->coaChangeSpeed($username, $speed ?: '10M/10M');
            } catch (\Exception $e) {
                Log::info("CoA failed during profile change for {$username}: " . $e->getMessage());
            }

            // 5. Update client record
            DB::table('tbl_clients')
                ->where('sas_username', $username)
                ->update(['radius_profile' => $profileName]);

            Log::info("RADIUS profile applied: {$username} -> {$profileName}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to apply RADIUS profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all available RADIUS profiles from subscriptions
     */
    public function getAvailableProfiles(): array
    {
        return DB::table('tbl_subscriptions')
            ->whereNotNull('radius_profile')
            ->where('radius_profile', '!=', '')
            ->select('id', 'name', 'radius_profile', 'radius_speed', 'price')
            ->get()
            ->toArray();
    }

    /**
     * Get current profile for a user
     */
    public function getClientProfile(string $username): ?string
    {
        return DB::table('tbl_clients')
            ->where('sas_username', $username)
            ->value('radius_profile');
    }
}
