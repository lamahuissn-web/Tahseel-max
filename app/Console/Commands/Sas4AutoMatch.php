<?php

namespace App\Console\Commands;

use App\Models\Clients;
use App\Services\Sas4\Sas4ApiService;
use Illuminate\Console\Command;

class Sas4AutoMatch extends Command
{
    protected $signature = 'sas4:auto-match {--dry-run : Preview matches without applying} {--auto : Apply matches automatically}';

    protected $description = 'Auto-match Tahseel clients to SAS 4 users by name';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $auto = $this->option('auto');

        if (!$dryRun && !$auto) {
            $this->info('Use --dry-run to preview or --auto to apply matches.');
            return 0;
        }

        $sas4Service = app(Sas4ApiService::class);

        $this->info('Fetching SAS 4 users...');
        $sas4Result = $sas4Service->searchUsers('', 1, 5000);
        if (!$sas4Result || !isset($sas4Result['data'])) {
            $this->error('Failed to fetch SAS 4 users.');
            return 1;
        }

        $sas4Users = collect($sas4Result['data']);
        $this->info("Found {$sas4Users->count()} SAS 4 users.");

        $clients = Clients::whereNull('sas_username')->orWhere('sas_username', '')->get();
        $this->info("Found {$clients->count()} clients without SAS 4 username.");

        $matched = 0;
        $notMatched = 0;

        foreach ($clients as $client) {
            $normalizedClient = $this->normalizeName($client->name);

            foreach ($sas4Users as $sas4User) {
                $normalizedSas4 = $this->normalizeName($sas4User['firstname'] ?? $sas4User['username'] ?? '');

                if ($this->nameMatches($normalizedClient, $normalizedSas4)) {
                    if ($dryRun) {
                        $this->line("  <fg=green>Match:</> <fg=cyan>{$client->name}</> → <fg=cyan>{$sas4User['username']}</> ({$sas4User['firstname']})");
                    } elseif ($auto) {
                        $client->sas_username = $sas4User['username'];
                        $client->save();
                        $this->line("  <fg=green>Linked:</> <fg=cyan>{$client->name}</> → <fg=cyan>{$sas4User['username']}</>");
                    }
                    $matched++;
                    break;
                }
            }

            if (!$this->wasMatched) {
                $notMatched++;
            }
            $this->wasMatched = false;
        }

        $this->newLine();
        $this->info("Results: {$matched} matched, {$notMatched} not matched.");

        if ($dryRun) {
            $this->warn('This was a dry run. Use --auto to apply matches.');
        }

        return 0;
    }

    protected $wasMatched = false;

    protected function normalizeName($name)
    {
        $name = trim($name);
        // Remove Latin suffixes
        $name = preg_replace('/\s+(W\.Y|TF|T|W|Y|A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|U|V|W|X|Z)\.?$/i', '', $name);
        // Remove Arabic suffixes
        $name = preg_replace('/\s+(تذكير|تنبيه|جديد|قديم|مؤقت)$/u', '', $name);
        // Remove extra whitespace
        $name = preg_replace('/\s+/', ' ', $name);
        return mb_strtolower(trim($name), 'UTF-8');
    }

    protected function nameMatches($clientName, $sas4Name)
    {
        if ($clientName === $sas4Name) {
            $this->wasMatched = true;
            return true;
        }
        // Substring match
        if (mb_strlen($clientName) > 3 && mb_strpos($sas4Name, $clientName) !== false) {
            $this->wasMatched = true;
            return true;
        }
        if (mb_strlen($sas4Name) > 3 && mb_strpos($clientName, $sas4Name) !== false) {
            $this->wasMatched = true;
            return true;
        }
        return false;
    }
}
