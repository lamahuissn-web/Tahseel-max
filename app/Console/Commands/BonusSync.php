<?php

namespace App\Console\Commands;

use App\Services\Bonus\BonusLedger;
use App\Services\Bonus\BonusProgressService;
use App\Services\Bonus\BonusSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Feature 021 — the only automation in the bonus program.
 *
 * It looks for customers who completed the required on-time months and records a
 * `pending` row so the administration sees them. It never approves, never issues
 * an invoice and never touches money: the free month is an admin decision.
 */
class BonusSync extends Command
{
    protected $signature = 'bonus:sync {--dry-run : اعرض المستحقين بدون كتابة أي سجل} {--chunk=200 : عدد الزبائن في كل دفعة}';

    protected $description = 'اكتشاف الزبائن المستحقين للشهر المجاني وتسجيلهم «بانتظار القرار» (لا إصدار)';

    public function handle(): int
    {
        if (! BonusSettings::enabled()) {
            $this->warn('برنامج البونص متوقف (bonus_enabled = 0) — لا شيء ليُنفَّذ.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $chunk  = max(50, (int) $this->option('chunk'));
        $ledger = app(BonusLedger::class);
        $types  = BonusSettings::clientTypes();

        $recorded = [];
        $skipped  = 0;

        DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->whereIn('client_type', $types)
            ->select('id', 'name')
            ->orderBy('id')
            ->chunkById($chunk, function ($clients) use ($ledger, $dryRun, &$recorded, &$skipped) {
                /** @var BonusProgressService $service */
                $service  = app(BonusProgressService::class);
                $progress = $service->forClients($clients->pluck('id')->all());

                foreach ($clients as $client) {
                    $p = $progress[(int) $client->id] ?? null;

                    if (! $p || ! $p->eligible) {
                        continue;
                    }

                    if ($dryRun) {
                        $recorded[] = $client->name;
                        continue;
                    }

                    try {
                        $ledger->earn((int) $client->id);
                        $recorded[] = $client->name;
                    } catch (\RuntimeException $e) {
                        // an open bonus already exists — nothing to record
                        $skipped++;
                    }
                }
            }, 'id');

        $label = $dryRun ? 'سيُسجَّل' : 'سُجِّل';
        $this->info(sprintf('%s %d زبون مستحق للشهر المجاني%s.', $label, count($recorded), $skipped ? sprintf(' (تخطّي %d)', $skipped) : ''));

        if ($recorded !== []) {
            $preview = implode(' · ', array_slice($recorded, 0, 10));
            $this->line($preview.(count($recorded) > 10 ? ' …' : ''));

            if (! $dryRun) {
                sendTelegramNotification(
                    sprintf('🏆 %d زبون استحق الشهر المجاني — بانتظار قرار الإدارة: %s', count($recorded), $preview),
                    'bonus_earned'
                );
            }
        }

        return self::SUCCESS;
    }
}
