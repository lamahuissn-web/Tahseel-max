<?php

namespace App\Services\Radius;

use App\Models\Clients;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusService
{
    /**
     * Sync a client to RADIUS tables (radcheck + radreply).
     */
    public function syncClient(Clients $client): bool
    {
        try {
            $username = $client->sas_username;
            if (!$username) {
                Log::warning("RadiusService: Client {$client->id} has no sas_username");
                return false;
            }

            $password = $client->radius_password ?? $this->generatePassword();

            if (!$client->radius_password) {
                $client->radius_password = $password;
                $client->save();
            }

            $this->deleteRadiusUser($username);

            // radcheck: authentication
            $checkEntries = [
                ["username" => $username, "attribute" => "Cleartext-Password", "op" => ":=", "value" => $password],
            ];

            if ($client->is_active == "0") {
                $checkEntries[] = ["username" => $username, "attribute" => "Auth-Type", "op" => ":=", "value" => "Reject"];
            }

            foreach ($checkEntries as $entry) {
                DB::table("radcheck")->insert($entry);
            }

            // radreply: speed limits
            $speed = $this->getSpeedForClient($client);
            if ($speed) {
                DB::table("radreply")->insert([
                    "username" => $username,
                    "attribute" => "Mikrotik-Rate-Limit",
                    "op" => ":=",
                    "value" => $speed,
                ]);
            }

            Log::info("RadiusService: Synced client {$client->name} ({$username})");
            return true;
        } catch (\Exception $e) {
            Log::error("RadiusService: Failed to sync client {$client->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable a client in RADIUS
     */
    public function enableClient(Clients $client): bool
    {
        try {
            $username = $client->sas_username;
            if (!$username) return false;

            DB::table("radcheck")
                ->where("username", $username)
                ->where("attribute", "Auth-Type")
                ->delete();

            Log::info("RadiusService: Enabled {$username}");
            return true;
        } catch (\Exception $e) {
            Log::error("RadiusService: Failed to enable {$client->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable a client in RADIUS
     */
    public function disableClient(Clients $client): bool
    {
        try {
            $username = $client->sas_username;
            if (!$username) return false;

            $exists = DB::table("radcheck")
                ->where("username", $username)
                ->where("attribute", "Auth-Type")
                ->where("value", "Reject")
                ->exists();

            if (!$exists) {
                DB::table("radcheck")->insert([
                    "username" => $username,
                    "attribute" => "Auth-Type",
                    "op" => ":=",
                    "value" => "Reject",
                ]);
            }

            $this->disconnectUser($username);
            Log::info("RadiusService: Disabled {$username}");
            return true;
        } catch (\Exception $e) {
            Log::error("RadiusService: Failed to disable {$client->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete all RADIUS entries for a username
     */
    public function deleteRadiusUser(string $username): void
    {
        DB::table("radcheck")->where("username", $username)->delete();
        DB::table("radreply")->where("username", $username)->delete();
        DB::table("radusergroup")->where("username", $username)->delete();
    }

    /**
     * Get active sessions from radacct
     */
    public function getActiveSessions(): array
    {
        return DB::table("radacct")
            ->whereNull("acctstoptime")
            ->orderBy("acctstarttime", "desc")
            ->get()
            ->toArray();
    }

    /**
     * Check if a username is currently online
     */
    public function isOnline(string $username): bool
    {
        return DB::table("radacct")
            ->where("username", $username)
            ->whereNull("acctstoptime")
            ->exists();
    }

    /**
     * Get traffic data for a username in a given month
     */
    public function getTraffic(string $username, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? (int)date("m");
        $year = $year ?? (int)date("Y");

        $sessions = DB::table("radacct")
            ->where("username", $username)
            ->whereYear("acctstarttime", $year)
            ->whereMonth("acctstarttime", $month)
            ->get();

        $totalInput = $sessions->sum("acctinputoctets") ?: 0;
        $totalOutput = $sessions->sum("acctoutputoctets") ?: 0;

        return [
            "username" => $username,
            "month" => $month,
            "year" => $year,
            "download_bytes" => (int)$totalInput,
            "upload_bytes" => (int)$totalOutput,
            "total_bytes" => (int)($totalInput + $totalOutput),
            "sessions" => $sessions->count(),
            "active" => $this->isOnline($username),
        ];
    }

    /**
     * Get daily traffic breakdown for a month
     */
    public function getDailyTraffic(string $username, int $month, int $year): array
    {
        $daysInMonth = (int)date("t", mktime(0, 0, 0, $month, 1, $year));
        $days = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf("%d-%02d-%02d", $year, $month, $d);
            $sessions = DB::table("radacct")
                ->where("username", $username)
                ->whereDate("acctstarttime", $date)
                ->get();

            $download = $sessions->sum("acctinputoctets") ?: 0;
            $upload = $sessions->sum("acctoutputoctets") ?: 0;

            $days[] = [
                "day" => $d,
                "date" => $date,
                "download" => (int)$download,
                "upload" => (int)$upload,
                "total" => (int)($download + $upload),
                "has_traffic" => ($download > 0 || $upload > 0),
            ];
        }

        $totalDownload = array_sum(array_column($days, "download"));
        $totalUpload = array_sum(array_column($days, "upload"));

        return [
            "username" => $username,
            "month" => $month,
            "year" => $year,
            "days" => $days,
            "summary" => [
                "total_download" => $totalDownload,
                "total_upload" => $totalUpload,
                "total_traffic" => $totalDownload + $totalUpload,
            ],
        ];
    }

    /**
     * Get client info from RADIUS data
     */
    public function getClientInfo(string $username): ?array
    {
        $online = $this->isOnline($username);

        $lastSession = DB::table("radacct")
            ->where("username", $username)
            ->orderBy("acctstarttime", "desc")
            ->first();

        return [
            "username" => $username,
            "online" => $online,
            "last_login" => $lastSession?->acctstarttime,
            "last_session" => $lastSession ? [
                "framed_ip" => $lastSession->framedipaddress,
                "nas" => $lastSession->nasipaddress,
                "started" => $lastSession->acctstarttime,
                "session_time" => $lastSession->acctsessiontime,
            ] : null,
        ];
    }

    /**
     * Disconnect a user
     */
    public function disconnectUser(string $username): bool
    {
        try {
            $activeSessions = DB::table("radacct")
                ->where("username", $username)
                ->whereNull("acctstoptime")
                ->get();

            if ($activeSessions->isEmpty()) {
                return true;
            }

            foreach ($activeSessions as $session) {
                DB::table("radacct")
                    ->where("radacctid", $session->radacctid)
                    ->update([
                        "acctstoptime" => now(),
                        "acctterminatecause" => "Admin-Reset",
                    ]);
            }

            Log::info("RadiusService: Disconnected {$username} ({$activeSessions->count()} sessions)");
            return true;
        } catch (\Exception $e) {
            Log::error("RadiusService: Failed to disconnect {$username}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update client plan/speed in radreply
     */
    public function updatePlan(Clients $client): bool
    {
        $username = $client->sas_username;
        if (!$username) return false;

        DB::table("radreply")
            ->where("username", $username)
            ->where("attribute", "Mikrotik-Rate-Limit")
            ->delete();

        $speed = $this->getSpeedForClient($client);
        if ($speed) {
            DB::table("radreply")->insert([
                "username" => $username,
                "attribute" => "Mikrotik-Rate-Limit",
                "op" => ":=",
                "value" => $speed,
            ]);
        }

        return true;
    }

    /**
     * Extract speed from subscription name
     */
    protected function getSpeedForClient(Clients $client): ?string
    {
        $subscription = $client->subscription;
        if (!$subscription || !$subscription->name) {
            return null;
        }

        $name = $subscription->name;

        preg_match("/(\d+)\s*M/i", $name, $matches);
        if (!empty($matches[1])) {
            $speed = (int)$matches[1];
            return "{$speed}M/{$speed}M";
        }

        if (stripos($name, "ساتلايت") !== false || stripos($name, "ستالايت") !== false) {
            return "4M/2M";
        }

        return null;
    }

    /**
     * Generate a random password for RADIUS
     */
    protected function generatePassword(int $length = 10): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
}
