<?php

namespace App\Console\Commands;

use App\Services\MobilePaymentReceiptReconciler;
use Illuminate\Console\Command;

class ReconcileMobilePaymentReceipts extends Command
{
    protected $signature = 'payments:reconcile-receipts {--limit=100 : Maximum operations to process}';

    protected $description = 'Recover committed mobile payments whose WhatsApp receipt was not queued';

    public function handle(MobilePaymentReceiptReconciler $reconciler): int
    {
        $limit = max(1, min((int) $this->option('limit'), 1000));
        $counts = $reconciler->reconcilePending($limit);

        $this->line(json_encode($counts, JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
