<?php

namespace App\Services;

use App\Models\Admin\Invoice;
use App\Models\Admin\MobilePaymentOperation;
use App\Models\Admin\Revenue;
use App\Services\WhatsApp\PaymentReceiptNotifier;
use Illuminate\Support\Facades\Log;
use Throwable;

class MobilePaymentReceiptReconciler
{
    public function __construct(private readonly PaymentReceiptNotifier $notifier) {}

    public function process(int $operationId): string
    {
        $operation = MobilePaymentOperation::query()->find($operationId);
        if (! $operation || $operation->status !== 'committed') {
            return 'ignored';
        }

        if (in_array($operation->receipt_status, ['queued', 'not_applicable'], true)) {
            return $operation->receipt_status;
        }

        $outcome = 'retry';

        try {
            $invoice = Invoice::withTrashed()->with('client')->find($operation->invoice_id);
            $revenue = Revenue::withTrashed()->find($operation->revenue_id);

            if ($invoice && $revenue) {
                $outcome = $this->notifier->notifyPayment(
                    $invoice,
                    $revenue,
                    $operation->reference,
                );
            }
        } catch (Throwable $exception) {
            Log::warning('Mobile payment receipt reconciliation failed', [
                'payment_operation_id' => $operationId,
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile() . ':' . $exception->getLine(),
            ]);
        }

        if (! in_array($outcome, ['queued', 'not_applicable', 'retry'], true)) {
            $outcome = 'retry';
        }

        MobilePaymentOperation::query()->whereKey($operationId)->update([
            'receipt_status' => $outcome,
            'receipt_attempts' => $operation->receipt_attempts + 1,
            'receipt_last_attempt_at' => now(),
        ]);

        return $outcome;
    }

    public function reconcilePending(int $limit = 100): array
    {
        $counts = ['queued' => 0, 'not_applicable' => 0, 'retry' => 0, 'ignored' => 0];

        MobilePaymentOperation::query()
            ->where('status', 'committed')
            ->whereIn('receipt_status', ['pending', 'retry'])
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id')
            ->each(function (int $operationId) use (&$counts): void {
                $outcome = $this->process($operationId);
                $counts[$outcome] = ($counts[$outcome] ?? 0) + 1;
            });

        return $counts;
    }
}
