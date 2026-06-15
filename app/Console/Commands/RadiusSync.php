<?php

namespace App\Console\Commands;
use Illuminate\Support\Str;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Client;

class RadiusSync extends Command
{
    protected $signature = 'radius:sync
        {--dry-run : Show what would be done without making changes}
        {--watch : Keep running and sync every 60 seconds}';

    protected $description = 'Synchronize Tahseel clients with FreeRADIUS database';

    /**
     * Attribute map: subscription name prefix → bandwidth limit
     */
    const BANDWIDTH_MAP = [
        '10M' => '10M/10M',
        '6M'  => '6M/6M',
        '3M'  => '3M/3M',
        '12M' => '12M/12M',
        '30$' => '30M/30M',
        '35$' => '35M/35M',
        '14M' => '14M/14M',
        '16M' => '16M/16M',
        'خط'  => '10M/10M', // default for unspecified
    ];

    const DEFAULT_BANDWIDTH = '10M/10M';

    public function handle()
    {
        do {
            $this->sync();
            if ($this->option('watch')) {
                $this->info('Watching... next sync in 60s');
                sleep(60);
            }
        } while ($this->option('watch'));

        return Command::SUCCESS;
    }

    protected function sync()
    {
        $dryRun = $this->option('dry-run');
        $changes = 0;

        // Get active clients with PPPoE credentials
        $clients = DB::connection('mysql')
            ->table('tbl_clients')
            ->where('is_active', '1')
            ->whereNotNull('sas_username')
            ->where('sas_username', '!=', '')
            ->get();

        $this->line("Found {$clients->count()} active clients with PPPoE credentials");

        // Get current RADIUS users
        $radiusUsers = DB::connection('radius')
            ->table('radcheck')
            ->where('attribute', 'Cleartext-Password')
            ->pluck('value', 'username');

        // 1. Sync passwords
        foreach ($clients as $client) {
            $username = trim($client->sas_username);
            if (!$username) continue;

            $radiusPassword = $client->radius_password ?? strtolower(Str::random(10));

            if (!isset($radiusUsers[$username])) {
                // New user - add to radcheck
                if (!$dryRun) {
                    DB::connection('radius')->table('radcheck')->insert([
                        'username' => $username,
                        'attribute' => 'Cleartext-Password',
                        'op' => ':=',
                        'value' => $radiusPassword,
                    ]);
                }
                $this->info("  + Added user: {$username}");
                $changes++;
            } elseif ($radiusUsers[$username] !== $radiusPassword) {
                // Password changed - update
                if (!$dryRun) {
                    DB::connection('radius')->table('radcheck')
                        ->where('username', $username)
                        ->where('attribute', 'Cleartext-Password')
                        ->update(['value' => $radiusPassword]);
                }
                $this->info("  ~ Updated password: {$username}");
                $changes++;
            }

            // 2. Sync bandwidth (radreply)
            $bandwidth = $this->resolveBandwidth($client);
            $this->syncBandwidth($username, $bandwidth, $dryRun, $changes);
        }

        // 3. Remove RADIUS users for inactive/deleted clients
        $activeUsernames = $clients->pluck('sas_username')->map(fn($v) => trim($v))->filter()->toArray();
        $radiusUsernames = $radiusUsers->keys()->toArray();

        foreach ($radiusUsernames as $username) {
            if (!in_array($username, $activeUsernames)) {
                if (!$dryRun) {
                    DB::connection('radius')->table('radcheck')
                        ->where('username', $username)
                        ->delete();
                    DB::connection('radius')->table('radreply')
                        ->where('username', $username)
                        ->delete();
                }
                $this->warn("  - Removed user: {$username}");
                $changes++;
            }
        }

        $this->line("Changes: {$changes}" . ($dryRun ? ' (dry-run)' : ''));
    }

    protected function resolveBandwidth($client): string
    {
        // Try from subscription name
        if ($client->subscription_id) {
            $sub = DB::connection('mysql')
                ->table('tbl_subscriptions')
                ->find($client->subscription_id);

            if ($sub) {
                foreach (self::BANDWIDTH_MAP as $prefix => $limit) {
                    if (str_starts_with($sub->name, $prefix)) {
                        return $limit;
                    }
                }
            }
        }

        return self::DEFAULT_BANDWIDTH;
    }

    protected function syncBandwidth(string $username, string $bandwidth, bool $dryRun, int &$changes)
    {
        $existing = DB::connection('radius')
            ->table('radreply')
            ->where('username', $username)
            ->where('attribute', 'Mikrotik-Rate-Limit')
            ->first();

        if (!$existing) {
            if (!$dryRun) {
                DB::connection('radius')->table('radreply')->insert([
                    'username' => $username,
                    'attribute' => 'Mikrotik-Rate-Limit',
                    'op' => ':=',
                    'value' => $bandwidth,
                ]);
            }
            $this->info("  + Added bandwidth: {$username} → {$bandwidth}");
            $changes++;
        } elseif ($existing->value !== $bandwidth) {
            if (!$dryRun) {
                DB::connection('radius')->table('radreply')
                    ->where('username', $username)
                    ->where('attribute', 'Mikrotik-Rate-Limit')
                    ->update(['value' => $bandwidth]);
            }
            $this->info("  ~ Updated bandwidth: {$username} → {$bandwidth}");
            $changes++;
        }
    }
}
