<?php

namespace App\Services\WhatsApp;

use App\Models\Clients;
use App\Models\Admin\Invoice;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsAppMessageBuilder;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Unified reminder engine for WhatsApp automation.
 *
 * One source of truth for:
 * - before-disconnection preview
 * - overdue preview
 * - send scope selection
 * - message rendering
 */
class ReminderService
{
    public function getBeforeDisconnectionPreview(int $days = 3, array $filters = []): array
    {
        $days = max(0, $days);
        $invoices = $this->getInvoicesForRule('whatsapp_remind_before', $filters, ['days' => $days]);

        return $this->buildPreview(
            $invoices,
            "Send to clients with invoices due within the last {$days} day(s) before disconnection",
            'whatsapp_remind_before'
        );
    }

    public function getOverduePreview(array $filters = []): array
    {
        $invoices = $this->getInvoicesForRule('whatsapp_overdue', $filters);

        return $this->buildPreview(
            $invoices,
            'Send to clients with overdue invoices only',
            'whatsapp_overdue'
        );
    }

    public function sendReminders(
        string $ruleId,
        array $clientIds,
        string $template = 'reminder',
        array $options = []
    ): array {
        $ruleId = $this->normalizeRuleId($ruleId);
        $clientIds = array_values(array_unique(array_map('intval', $clientIds)));

        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $details = [];
        $delaySeconds = (int) ($options['delay_seconds'] ?? 10);
        $sentBy = $options['sent_by'] ?? 'admin:automation';

        foreach ($clientIds as $index => $clientId) {
            $client = Clients::find($clientId);
            if (!$client || empty($client->phone)) {
                $failed++;
                $details[] = [
                    'client_id' => $clientId,
                    'client_name' => $client->name ?? 'Unknown',
                    'phone' => $client->phone ?? '',
                    'status' => 'failed',
                    'error' => 'Client missing or phone number empty',
                ];
                continue;
            }

            $invoices = $this->getClientInvoicesForRule($ruleId, $clientId, $options);
            if ($invoices->isEmpty()) {
                $skipped++;
                $details[] = [
                    'client_id' => $clientId,
                    'client_name' => $client->name,
                    'phone' => $client->phone,
                    'status' => 'skipped',
                    'error' => 'No eligible invoices for this rule',
                ];
                continue;
            }

            $message = $this->buildMessage($client, $invoices, $template);
            $service = app(WhatsAppService::class);
            $result = $service->send($client->phone, $message);
            $success = isset($result['success']) && $result['success'] === true;

            $this->logMessage($client, $invoices, $message, $template, $success, $result['error'] ?? null, $sentBy);

            if ($success) {
                Invoice::query()->whereIn('id', $invoices->pluck('id')->all())->update(['last_notified_at' => now()]);
                $sent++;
            } else {
                $failed++;
            }

            $details[] = [
                'client_id' => $clientId,
                'client_name' => $client->name,
                'phone' => $client->phone,
                'status' => $success ? 'sent' : 'failed',
                'error' => $success ? null : ($result['error'] ?? 'Unknown'),
                'invoice_count' => $invoices->count(),
                'total_amount' => $this->sumInvoiceAmounts($invoices),
            ];

            if ($index < count($clientIds) - 1 && $delaySeconds > 0) {
                sleep($delaySeconds);
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'skipped' => $skipped,
            'total' => count($clientIds),
            'details' => $details,
        ];
    }

    public function enqueueReminders(
        string $ruleId,
        array $clientIds,
        string $template = 'reminder',
        array $options = []
    ): array {
        $ruleId = $this->normalizeRuleId($ruleId);
        $clientIds = array_values(array_unique(array_map('intval', $clientIds)));

        $queued = 0;
        $failed = 0;
        $skipped = 0;
        $details = [];
        $sentBy = $options['sent_by'] ?? 'admin:automation';
        $batchId = (string) ($options['batch_id'] ?? Str::uuid());
        $batchLabel = $sentBy . '|batch:' . $batchId;

        foreach ($clientIds as $clientId) {
            $client = Clients::find($clientId);
            if (!$client || empty($client->phone)) {
                $failed++;
                $details[] = [
                    'client_id' => $clientId,
                    'client_name' => $client->name ?? 'Unknown',
                    'phone' => $client->phone ?? '',
                    'status' => 'failed',
                    'error' => 'Client missing or phone number empty',
                ];
                continue;
            }

            $invoices = $this->getClientInvoicesForRule($ruleId, $clientId, $options);
            if ($invoices->isEmpty()) {
                $skipped++;
                $details[] = [
                    'client_id' => $clientId,
                    'client_name' => $client->name,
                    'phone' => $client->phone,
                    'status' => 'skipped',
                    'error' => 'No eligible invoices for this rule',
                ];
                continue;
            }

            $message = $this->buildMessage($client, $invoices, $template);

            $log = WhatsAppMessageLog::create([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'invoice_id' => $invoices->first()->id ?? null,
                'invoice_ids' => $invoices->pluck('id')->values()->toArray(),
                'phone' => $client->phone,
                'message' => $message,
                'template_type' => $template,
                'status' => 'pending',
                'error' => null,
                'sent_by' => $batchLabel,
            ]);

            $queued++;
            $details[] = [
                'queue_log_id' => $log->id,
                'client_id' => $clientId,
                'client_name' => $client->name,
                'phone' => $client->phone,
                'status' => 'pending',
                'invoice_count' => $invoices->count(),
                'total_amount' => $this->sumInvoiceAmounts($invoices),
            ];
        }

        return [
            'batch_id' => $batchId,
            'batch_label' => $batchLabel,
            'queued' => $queued,
            'failed' => $failed,
            'skipped' => $skipped,
            'total' => count($clientIds),
            'details' => $details,
        ];
    }

    public function getClientInvoicesForRule(string $ruleId, int $clientId, array $options = []): Collection
    {
        $ruleId = $this->normalizeRuleId($ruleId);
        $query = Invoice::query()
            ->where('client_id', $clientId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNull('deleted_at')
            ->orderBy('due_date', 'asc');

        if ($ruleId === 'whatsapp_overdue') {
            $query->whereDate('due_date', '<', Carbon::today());
        } else {
            $days = max(0, (int) ($options['days'] ?? 3));
            $query->whereDate('due_date', '>=', Carbon::today()->copy()->subDays($days))
                ->whereDate('due_date', '<=', Carbon::today());
        }

        return $query->get();
    }

    private function getInvoicesForRule(string $ruleId, array $filters = [], array $options = []): Collection
    {
        $ruleId = $this->normalizeRuleId($ruleId);
        $query = Invoice::query()
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNull('deleted_at')
            ->with('client');

        if ($ruleId === 'whatsapp_overdue') {
            $query->whereDate('due_date', '<', Carbon::today());
        } else {
            $days = max(0, (int) ($options['days'] ?? 3));
            $query->whereDate('due_date', '>=', Carbon::today()->copy()->subDays($days))
                ->whereDate('due_date', '<=', Carbon::today());
        }

        $query->whereHas('client', function ($q) use ($filters) {
            $q->whereNotNull('phone')->where('phone', '!=', '');

            if (!empty($filters['client_type']) && $filters['client_type'] !== 'all') {
                $q->where('client_type', $filters['client_type']);
            }

            if (!empty($filters['subscription_id']) && $filters['subscription_id'] !== 'all') {
                $q->where('subscription_id', $filters['subscription_id']);
            }

            if (!empty($filters['client_status']) && $filters['client_status'] !== 'all') {
                $q->where('is_active', $filters['client_status'] === 'active' ? 1 : 0);
            }
        });

        $invoices = $query->orderBy('due_date', 'asc')->get();

        return $this->applyMinUnpaidFilter($invoices, $filters);
    }

    private function applyMinUnpaidFilter(Collection $invoices, array $filters): Collection
    {
        $minUnpaid = (int) ($filters['min_unpaid'] ?? 0);
        if ($minUnpaid <= 0) {
            return $invoices;
        }

        return $invoices->filter(function ($invoice) use ($minUnpaid) {
            return Invoice::query()
                ->where('client_id', $invoice->client_id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereNull('deleted_at')
                ->count() >= $minUnpaid;
        })->values();
    }

    private function buildPreview(Collection $invoices, string $description, string $ruleId): array
    {
        $grouped = $invoices->groupBy('client_id');

        $clientList = $grouped->map(function ($clientInvoices, $clientId) {
            $client = $clientInvoices->first()->client;

            return [
                'id' => (int) $clientId,
                'name' => $client->name ?? 'Unknown',
                'phone' => $client->phone ?? '',
                'invoices' => $clientInvoices->map(function ($inv) {
                    return [
                        'id' => $inv->id,
                        'due_date' => $inv->due_date,
                        'total' => $this->invoiceAmount($inv),
                    ];
                })->values()->toArray(),
                'total_amount' => $this->sumInvoiceAmounts($clientInvoices),
            ];
        })->values()->toArray();

        $dateRange = null;
        if ($invoices->isNotEmpty()) {
            $dateRange = [
                'from' => Carbon::parse($invoices->min('due_date'))->format('Y-m-d'),
                'to' => Carbon::parse($invoices->max('due_date'))->format('Y-m-d'),
            ];
        }

        return [
            'rule_id' => $ruleId,
            'rule_label' => $ruleId === 'whatsapp_overdue' ? 'Overdue Reminder' : 'Reminder Before Disconnection',
            'description' => $description,
            'client_count' => $grouped->count(),
            'invoice_count' => $invoices->count(),
            'total_amount' => $this->sumInvoiceAmounts($invoices),
            'date_range' => $dateRange,
            'clients' => $clientList,
        ];
    }

    private function buildMessage($client, Collection $invoices, string $templateType): string
    {
        $template = WhatsAppTemplateService::getBody($templateType);
        if (empty($template)) {
            $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
                ?? WhatsAppMessageBuilder::defaultTemplate();
        }

        $totalAmount = $this->sumInvoiceAmounts($invoices);
        $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($invoices);
        $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);

        $latestDueDate = $invoices->max('due_date');
        $supportPhone = DB::table('app_config')->where('key', 'support_phone')->value('value') ?? '70781562';

        $message = str_replace('{amount}', number_format($totalAmount, 2), $message);
        $message = str_replace('{due_date}', $latestDueDate ? Carbon::parse($latestDueDate)->format('Y-m-d') : Carbon::today()->format('Y-m-d'), $message);
        $message = str_replace('{support_phone}', $supportPhone, $message);
        $message = str_replace('{month}', now()->format('m'), $message);
        $message = str_replace('{year}', now()->format('Y'), $message);
        $message = str_replace('{datetime}', now()->format('Y-m-d h:i A'), $message);
        $message = str_replace('{balance_status}', 'الرصيد الحالي: $' . number_format($totalAmount, 2), $message);

        return $message;
    }

    private function logMessage($client, Collection $invoices, string $message, string $templateType, bool $success, ?string $error = null, string $sentBy = 'admin:automation'): void
    {
        WhatsAppMessageLog::create([
            'client_id' => $client->id,
            'client_name' => $client->name,
            'invoice_id' => $invoices->first()->id ?? null,
            'invoice_ids' => $invoices->pluck('id')->values()->toArray(),
            'phone' => $client->phone,
            'message' => $message,
            'template_type' => $templateType,
            'status' => $success ? 'sent' : 'failed',
            'error' => $success ? null : $error,
            'sent_by' => $sentBy,
        ]);
    }

    private function invoiceAmount($invoice): float
    {
        return (float) ($invoice->remaining_amount ?? $invoice->amount ?? 0);
    }

    private function sumInvoiceAmounts(Collection $invoices): float
    {
        return round($invoices->sum(fn ($invoice) => $this->invoiceAmount($invoice)), 2);
    }

    private function normalizeRuleId(string $ruleId): string
    {
        return $ruleId === 'whatsapp_custom' ? 'whatsapp_overdue' : $ruleId;
    }
}
