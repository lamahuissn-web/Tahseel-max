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
 * 
 * Manual overrides: if a client's radius_profile differs from the
 * subscription's radius_profile, the change is considered a manual override
 * and is preserved during plan changes.
 */
class ProfileService
{
    /**
     * Apply a RADIUS profile to a user
     */
    public function applyProfile(string $username, string $profileName, ?string $speed = null): bool
    {
        try {
            DB::connection('radius')
                ->table('radusergroup')
                ->where('username', $username)
                ->delete();

            DB::connection('radius')
                ->table('radusergroup')
                ->insert([
                    'username'  => $username,
                    'groupname' => $profileName,
                    'priority'  => 1,
                ]);

            if ($speed === null) {
                $speed = DB::table('tbl_subscriptions')
                    ->where('radius_profile', $profileName)
                    ->value('radius_speed');
            }

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

            try {
                app(RadiusService::class)->coaChangeSpeed($username, $speed ?: '10M/10M');
            } catch (\Exception $e) {
                Log::info("CoA failed during profile change for {$username}: " . $e->getMessage());
            }

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

    public function getAvailableProfiles(): array
    {
        return DB::table('tbl_subscriptions')
            ->whereNotNull('radius_profile')
            ->where('radius_profile', '!=', '')
            ->select('id', 'name', 'radius_profile', 'radius_speed', 'price')
            ->get()
            ->toArray();
    }

    public function getClientProfile(string $username): ?string
    {
        return DB::table('tbl_clients')
            ->where('sas_username', $username)
            ->value('radius_profile');
    }

    /**
     * Auto-update RADIUS profile when a client's subscription changes.
     *
     * Checks for manual overrides: if the client's current radius_profile
     * differs from the subscription's radius_profile, the manual override
     * is preserved and the subscription profile is not applied.
     *
     * Returns an array with 'applied' (bool), 'message' (string),
     * and 'profile' (?string).
     */
    public function updateClientOnPlanChange(string $username, int $newSubscriptionId): array
    {
        $result = [
            'applied' => false,
            'message' => '',
            'profile' => null,
        ];

        $currentProfile = DB::table('tbl_clients')
            ->where('sas_username', $username)
            ->value('radius_profile');

        $subscription = DB::table('tbl_subscriptions')->find($newSubscriptionId);

        if (!$subscription || empty($subscription->radius_profile)) {
            $result['message'] = $currentProfile
                ? "الاشتراك الجديد ليس لديه باقة سرعة — تم الإبقاء على الباقة الحالية ({$currentProfile})"
                : "الاشتراك الجديد ليس لديه باقة سرعة";
            $result['profile'] = $currentProfile;
            Log::info("Plan change: {$username} — {$result['message']}");
            return $result;
        }

        if ($currentProfile && $currentProfile !== $subscription->radius_profile) {
            $result['message'] = "تم الإبقاء على الباقة اليدوية ({$currentProfile}) — تختلف عن باقة الاشتراك ({$subscription->radius_profile})";
            $result['profile'] = $currentProfile;
            Log::info("Plan change manual override: {$username} — {$result['message']}");
            return $result;
        }

        $applied = $this->applyProfile($username, $subscription->radius_profile, $subscription->radius_speed);
        $result['applied'] = $applied;
        $result['profile'] = $subscription->radius_profile;
        $result['message'] = $applied
            ? "تم تطبيق باقة السرعة: {$subscription->radius_profile}"
            : "فشل تطبيق باقة السرعة: {$subscription->radius_profile}";

        if ($applied) {
            Log::info("RADIUS profile synced: {$username} -> {$subscription->radius_profile} (plan change to subscription #{$newSubscriptionId})");
        }

        return $result;
    }
}
