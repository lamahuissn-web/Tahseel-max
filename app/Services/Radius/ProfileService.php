<?php

namespace App\Services\Radius;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Manage RADIUS profiles — the core network entity.
 *
 * tbl_profiles is the authoritative source for Pool Names, speeds,
 * and limits. Each profile maps directly to a MikroTik PPPoE Pool.
 *
 * New architecture:
 *   tbl_profiles     ← core network entity (Pool Name = name, speed, limits)
 *   tbl_subscriptions → billing only, linked via profile_id
 *   tbl_clients       → linked via profile_id (actual speed) + subscription_id (invoice)
 */
class ProfileService
{
    /**
     * Apply a RADIUS profile to a user (update radusergroup + radreply + CoA)
     */
    public function applyProfile(string $username, string $profileName, ?string $speed = null): bool
    {
        try {
            // 1. Remove old group assignment
            DB::connection('radius')
                ->table('radusergroup')
                ->where('username', $username)
                ->delete();

            // 2. Assign new group (groupname = Pool Name)
            DB::connection('radius')
                ->table('radusergroup')
                ->insert([
                    'username'  => $username,
                    'groupname' => $profileName,
                    'priority'  => 1,
                ]);

            // 3. Get speed from tbl_profiles if not provided
            if ($speed === null) {
                $speed = DB::table('tbl_profiles')
                    ->where('name', $profileName)
                    ->value('speed');
            }

            // 4. Update rate limit in radreply
            DB::connection('radius')
                ->table('radreply')
                ->where('username', $username)
                ->where('attribute', 'Mikrotik-Rate-Limit')
                ->delete();

            if ($speed) {
                DB::connection('radius')
                    ->table('radreply')
                    ->insert([
                        'username'  => $username,
                        'attribute' => 'Mikrotik-Rate-Limit',
                        'op'        => ':=',
                        'value'     => $speed,
                    ]);
            }

            // 5. CoA to apply immediately
            try {
                app(RadiusService::class)->coaChangeSpeed($username, $speed ?: '10M/10M');
            } catch (\Exception $e) {
                Log::info("CoA failed during profile change for {$username}: " . $e->getMessage());
            }

            // 6. Update both old and new fields for backward compat
            $profileId = DB::table('tbl_profiles')->where('name', $profileName)->value('id');
            $updateData = [
                'radius_profile' => $profileName,
            ];
            if ($profileId) {
                $updateData['profile_id'] = $profileId;
                $updateData['override_profile_id'] = null; // applied from source, not manual
            }
            DB::table('tbl_clients')
                ->where('sas_username', $username)
                ->update($updateData);

            Log::info("RADIUS profile applied: {$username} -> {$profileName}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to apply RADIUS profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply a profile by its tbl_profiles.id
     */
    public function applyProfileById(string $username, int $profileId): bool
    {
        $profile = DB::table('tbl_profiles')->find($profileId);
        if (!$profile) {
            Log::warning("Profile #{$profileId} not found when applying to {$username}");
            return false;
        }
        return $this->applyProfile($username, $profile->name, $profile->speed);
    }

    /**
     * Get all available profiles (from tbl_profiles)
     */
    public function getAvailableProfiles(): array
    {
        return DB::table('tbl_profiles')
            ->select('id', 'name', 'speed', 'simultaneous_use', 'description')
            ->get()
            ->toArray();
    }

    /**
     * Get the profile name assigned to a client (via profile_id or legacy radius_profile)
     */
    public function getClientProfile(string $username): ?string
    {
        $client = DB::table('tbl_clients')
            ->where('sas_username', $username)
            ->select('profile_id', 'radius_profile')
            ->first();

        if (!$client) return null;

        // Prefer profile_id FK, fall back to legacy radius_profile
        if ($client->profile_id) {
            return DB::table('tbl_profiles')->where('id', $client->profile_id)->value('name');
        }

        return $client->radius_profile;
    }

    /**
     * Sync RADIUS profile when a client's subscription changes.
     *
     * Compares profile_id between client and subscription.
     * If they differ → manual override, preserve existing profile.
     * If they match or client has no override → apply subscription's profile.
     *
     * Returns array with 'applied' (bool), 'message' (string), 'profile' (?string).
     */
    public function syncOnPlanChange(string $username, int $newSubscriptionId): array
    {
        $result = [
            'applied'    => false,
            'message'    => '',
            'profile'    => null,
        ];

        // Get the subscription's profile_id
        $subscription = DB::table('tbl_subscriptions')
            ->select('id', 'name', 'profile_id', 'radius_profile', 'radius_speed')
            ->find($newSubscriptionId);

        if (!$subscription) {
            $result['message'] = "الاشتراك #{$newSubscriptionId} غير موجود";
            return $result;
        }

        // Get the client's current profile info
        $client = DB::table('tbl_clients')
            ->where('sas_username', $username)
            ->select('profile_id', 'radius_profile')
            ->first();

        if (!$client) {
            $result['message'] = "الزبون ({$username}) غير موجود";
            return $result;
        }

        // Resolve the effective profile name from subscription (via profile_id FK or legacy text)
        $subProfileId = $subscription->profile_id;
        $subProfileName = null;
        $subProfileSpeed = null;

        if ($subProfileId) {
            $profile = DB::table('tbl_profiles')->find($subProfileId);
            $subProfileName = $profile->name ?? null;
            $subProfileSpeed = $profile->speed ?? null;
        } elseif (!empty($subscription->radius_profile)) {
            // Legacy fallback
            $subProfileName = $subscription->radius_profile;
            $subProfileSpeed = $subscription->radius_speed;
        }

        // Resolve the client's effective profile name
        $clientProfileName = null;
        if ($client->profile_id) {
            $profile = DB::table('tbl_profiles')->find($client->profile_id);
            $clientProfileName = $profile->name ?? null;
        } elseif (!empty($client->radius_profile)) {
            $clientProfileName = $client->radius_profile;
        }

        // CASE 1: No profile on subscription — preserve client's current profile
        if (!$subProfileName) {
            $result['profile'] = $clientProfileName;
            $result['applied'] = false;
            $result['message'] = $clientProfileName
                ? "الاشتراك الجديد ليس لديه باقة سرعة — تم الإبقاء على الباقة الحالية ({$clientProfileName})"
                : "الاشتراك الجديد ليس لديه باقة سرعة";
            Log::info("Plan change: {$username} — {$result['message']}");
            return $result;
        }

        // CASE 2: Client has manual override (profile differs from subscription's)
        if ($clientProfileName && $clientProfileName !== $subProfileName) {
            $result['profile'] = $clientProfileName;
            $result['applied'] = false;
            $result['message'] = "تم الإبقاء على الباقة اليدوية ({$clientProfileName}) — تختلف عن باقة الاشتراك ({$subProfileName})";
            Log::info("Plan change manual override: {$username} — {$result['message']}");
            return $result;
        }

        // CASE 3: Apply subscription's profile
        $applied = $this->applyProfile($username, $subProfileName, $subProfileSpeed);
        $result['applied'] = $applied;
        $result['profile'] = $subProfileName;
        $result['message'] = $applied
            ? "تم تطبيق باقة السرعة: {$subProfileName}"
            : "فشل تطبيق باقة السرعة: {$subProfileName}";

        if ($applied) {
            Log::info("RADIUS profile synced: {$username} -> {$subProfileName} (sub #{$newSubscriptionId})");
        }

        return $result;
    }

    /**
     * Legacy alias for syncOnPlanChange (backward compat)
     */
    public function updateClientOnPlanChange(string $username, int $newSubscriptionId): array
    {
        return $this->syncOnPlanChange($username, $newSubscriptionId);
    }

    /**
     * Bulk update speed for a profile — applies to ALL clients on that profile + CoA each.
     *
     * @param int $profileId tbl_profiles.id
     * @param string $newSpeed e.g. "25M/25M"
     * @return array ['updated_count' => int, 'coa_success' => int, 'coa_failed' => int]
     */
    public function bulkUpdateSpeed(int $profileId, string $newSpeed): array
    {
        $profile = DB::table('tbl_profiles')->find($profileId);
        if (!$profile) {
            Log::warning("bulkUpdateSpeed: Profile #{$profileId} not found");
            return ['updated_count' => 0, 'coa_success' => 0, 'coa_failed' => 0];
        }

        // 1. Update tbl_profiles.speed
        DB::table('tbl_profiles')->where('id', $profileId)->update(['speed' => $newSpeed]);

        // 2. Get all clients on this profile (via profile_id FK OR legacy radius_profile)
        $usernames = DB::table('tbl_clients')
            ->where(function ($q) use ($profile) {
                $q->where('profile_id', $profile->id)
                  ->orWhere('radius_profile', $profile->name);
            })
            ->whereNotNull('sas_username')
            ->where('sas_username', '!=', '')
            ->where('is_active', 1)
            ->pluck('sas_username');

        Log::info("bulkUpdateSpeed: Updating speed for profile '{$profile->name}' to {$newSpeed} — affecting {$usernames->count()} clients");

        // 3. Update radreply + CoA for each
        $coaSuccess = 0;
        $coaFailed = 0;

        foreach ($usernames as $username) {
            try {
                // Update radreply
                DB::connection('radius')
                    ->table('radreply')
                    ->where('username', $username)
                    ->where('attribute', 'Mikrotik-Rate-Limit')
                    ->delete();

                DB::connection('radius')
                    ->table('radreply')
                    ->insert([
                        'username'  => $username,
                        'attribute' => 'Mikrotik-Rate-Limit',
                        'op'        => ':=',
                        'value'     => $newSpeed,
                    ]);

                // CoA
                try {
                    app(RadiusService::class)->coaChangeSpeed($username, $newSpeed);
                    $coaSuccess++;
                } catch (\Exception $e) {
                    Log::info("CoA failed for {$username} during bulk update: " . $e->getMessage());
                    $coaFailed++;
                }

            } catch (\Exception $e) {
                Log::error("Bulk update failed for {$username}: " . $e->getMessage());
                $coaFailed++;
            }
        }

        Log::info("bulkUpdateSpeed: Done — {$usernames->count()} clients, CoA: {$coaSuccess} OK, {$coaFailed} failed");

        return [
            'updated_count' => $usernames->count(),
            'coa_success'   => $coaSuccess,
            'coa_failed'    => $coaFailed,
        ];
    }

    /**
     * Get client count per profile (for the profiles page aggregation)
     */
    public function getProfileStats(): array
    {
        return DB::table('tbl_profiles')
            ->leftJoin('tbl_clients', function ($join) {
                $join->on('tbl_profiles.id', '=', 'tbl_clients.profile_id')
                     ->orOn('tbl_profiles.name', '=', 'tbl_clients.radius_profile');
            })
            ->selectRaw('tbl_profiles.id, tbl_profiles.name, tbl_profiles.speed, tbl_profiles.simultaneous_use')
            ->selectRaw('COUNT(tbl_clients.id) as clients_count')
            ->selectRaw('SUM(CASE WHEN tbl_clients.is_active = 1 THEN 1 ELSE 0 END) as active_clients')
            ->groupBy('tbl_profiles.id', 'tbl_profiles.name', 'tbl_profiles.speed', 'tbl_profiles.simultaneous_use')
            ->orderBy('tbl_profiles.name')
            ->get()
            ->toArray();
    }

    /**
     * Get all clients on a specific profile (for UI display)
     */
    public function getProfileClients(int $profileId): array
    {
        $profile = DB::table('tbl_profiles')->find($profileId);
        if (!$profile) return [];

        return DB::table('tbl_clients')
            ->where(function ($q) use ($profile) {
                $q->where('profile_id', $profile->id)
                  ->orWhere('radius_profile', $profile->name);
            })
            ->whereNotNull('sas_username')
            ->where('sas_username', '!=', '')
            ->select('id', 'name', 'sas_username', 'is_active', 'phone')
            ->get()
            ->toArray();
    }
}
