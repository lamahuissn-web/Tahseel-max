<?php

namespace App\Services\WhatsApp;

use App\Models\Clients;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsAppMessageBuilder;
use App\Services\WhatsApp\WhatsAppPhoneValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Monthly Reminder notifier — builds Meta template variables for monthly_reminder_v1
 * and enqueues a business-initiated reminder via the WhatsApp queue.
 *
 * Mirrors PaymentReceiptNotifier but for reminder scope (no just-paid exclusion).
 * Always applies `deleted_at IS NULL` (same lesson as payment_receipt_v4).
 *
 * Template monthly_reminder_v1 (UTILITY, ar, 5 params):
 *   {{1}} subscriber name
 *   {{2}} soonest unpaid subscription month as MM/YYYY
 *   {{3}} unpaid subscription months, comma-joined ("7, 8"); "لا يوجد" if none
 *   {{4}} unpaid services "desc ($amount)", comma-joined; "لا يوجد" if none
 *   {{5}} total outstanding across ALL non-deleted unpaid invoices
 */
class MonthlyReminderNotifier
{
    public function __construct(private readonly WhatsAppMessageDispatcher $dispatcher) {}

    /**
     * Enqueue a monthly reminder for a client.
     *
     * @return string 'queued' | 'not_applicable' | 'retry'
     */
    public function notify(int $clientId): string
    {
        // FAIL-CLOSED manual-only guard (Spec 018, post-incident hardening):
        // monthly reminders must never auto-fire. Even a manual send is blocked
        // unless the operator has explicitly enabled the feature (ZERNIO_MONTHLY_REMINDER_ENABLED=true).
        if (config('zernio.monthly_reminder_enabled') !== true) {
            Log::warning('[WhatsApp Reminder] Monthly reminders disabled (ZERNIO_MONTHLY_REMINDER_ENABLED not true) — refusing to enqueue', [
                'client_id' => $clientId,
            ]);

            return 'disabled';
        }

        try {
            $client = Clients::find($clientId);
            if (! $client) {
                Log::warning('[WhatsApp Reminder] Client not found', ['client_id' => $clientId]);

                return 'not_applicable';
            }

            if (empty($client->phone) || WhatsAppPhoneValidator::isUnsendable($client->phone)) {
                Log::info('[WhatsApp Reminder] Client has no usable phone — skipping', ['client_id' => $clientId]);

                return 'not_applicable';
            }

            // All non-deleted unpaid invoices for the client (subscription + service)
            $unpaid = \App\Models\Admin\Invoice::query()
                ->where('client_id', $client->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereNull('deleted_at')
                ->get();

            if ($unpaid->isEmpty()) {
                Log::info('[WhatsApp Reminder] Client has no unpaid invoices — skipping', ['client_id' => $clientId]);

                return 'not_applicable';
            }

            // IDEMPOTENCY GUARD (audit 2026-08-21): one monthly reminder per client per
            // window. Protects against double-clicks, double POSTs, and concurrent
            // requests enqueueing duplicates. 'pending'/'sending' rows block outright;
            // a recently 'sent' reminder blocks re-sends within the window.
            $recentDuplicate = WhatsAppMessageLog::query()
                ->where('client_id', $client->id)
                ->where('template_type', 'monthly_reminder')
                ->whereIn('status', ['pending', 'sending'])
                ->exists();

            if (! $recentDuplicate) {
                $recentDuplicate = WhatsAppMessageLog::query()
                    ->where('client_id', $client->id)
                    ->where('template_type', 'monthly_reminder')
                    ->where('status', 'sent')
                    ->where('updated_at', '>=', now()->subHours(12))
                    ->exists();
            }

            if ($recentDuplicate) {
                Log::info('[WhatsApp Reminder] Duplicate suppressed (recent reminder exists)', [
                    'client_id' => $client->id,
                ]);

                return 'duplicate';
            }

            // {{2}} + {{3}} — unpaid subscription months
            $unpaidSub = $unpaid->where('invoice_type', 'subscription');
            $subMonths = $unpaidSub
                ->pluck('due_date')
                ->map(fn ($d) => $d ? (int) date('m', strtotime($d)) : null)
                ->filter()
                ->sort()
                ->values();

            $soonestSub = $unpaidSub
                ->pluck('due_date')
                ->filter()
                ->sort()
                ->first();

            $soonestSubStr = $soonestSub
                ? date('m', strtotime($soonestSub)).'/'.date('Y', strtotime($soonestSub))
                : 'لا يوجد';
            $unpaidSubStr = $subMonths->isEmpty() ? 'لا يوجد' : $subMonths->implode(', ');

            // {{4}} — unpaid services (desc + amount)
            $unpaidServices = $unpaid->where('invoice_type', 'service');
            $svcParts = [];
            foreach ($unpaidServices as $svc) {
                $desc = ! empty($svc->notes) ? $svc->notes : 'خدمة';
                $svcParts[] = "{$desc} (\$".WhatsAppMessageBuilder::formatAmount($svc->amount).")";
            }
            $unpaidSvcStr = empty($svcParts) ? 'لا يوجد' : implode(', ', $svcParts);

            // {{5}} — total outstanding (all unpaid, includes both types)
            $totalOutstanding = (float) $unpaid->sum('amount');

            // Free-text fallback body (used for OpenWA driver / pre-approval safety)
            $message = $this->buildFallbackMessage(
                $client->name,
                $soonestSubStr,
                $unpaidSubStr,
                $unpaidSvcStr,
                $totalOutstanding
            );

            $templateVariables = [
                $client->name,           // {{1}}
                $soonestSubStr,          // {{2}}
                $unpaidSubStr,           // {{3}}
                $unpaidSvcStr,           // {{4}}
                WhatsAppMessageBuilder::formatAmount($totalOutstanding), // {{5}}
            ];

            try {
                $batchId = (string) Str::uuid();
                $messageLog = WhatsAppMessageLog::create([
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'phone' => $client->phone,
                    'message' => $message,
                    'template_type' => 'monthly_reminder',
                    'template_variables' => $templateVariables,
                    'status' => 'pending',
                    'error' => null,
                    'sent_by' => 'system:monthly_reminder|batch:'.$batchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->dispatcher->dispatch($messageLog);

                Log::info('[WhatsApp Reminder] Queued for delivery', [
                    'client_id' => $client->id,
                    'batch_id' => $batchId,
                    'unpaid_count' => $unpaid->count(),
                ]);

                return 'queued';
            } catch (\Exception $logErr) {
                Log::warning('[WhatsApp Reminder] Failed to enqueue message', [
                    'client_id' => $client->id,
                    'exception' => get_class($logErr),
                ]);

                return 'retry';
            }
        } catch (\Exception $e) {
            Log::error('[WhatsApp Reminder] Failed to build notification', [
                'client_id' => $clientId,
                'exception' => get_class($e),
            ]);

            return 'retry';
        }
    }

    /**
     * Build a free-text fallback reminder (used when not sending via Meta template).
     */
    protected function buildFallbackMessage(
        string $customerName,
        string $soonestSubStr,
        string $unpaidSubStr,
        string $unpaidSvcStr,
        float $totalOutstanding
    ): string {
        $message = "🌐 MegaNet — تذكير شهري\n\n";
        $message .= "👤 اسم المشترك: {$customerName}\n";
        $message .= "📅 الاشتراك المستحق: {$soonestSubStr}\n";
        $message .= "📊 الاشهر غير المدفوعة: {$unpaidSubStr}\n";
        $message .= "📊 الخدمات الغير مدفوعة: {$unpaidSvcStr}\n\n";
        $message .= "💵 المبلغ الإجمالي: \$".WhatsAppMessageBuilder::formatAmount($totalOutstanding)."\n\n";
        $message .= "شكراً لاختياركم MegaNet 🌹\n";
        $message .= "للمراجعة وللصيانة 70618897";

        return $message;
    }
}
