<?php

namespace App\Console\Commands;

use App\Models\Admin\Invoice;
use App\Services\WhatsAppMessageBuilder;
use App\Services\WhatsAppService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use App\Services\WhatsApp\InvoiceEligibilityService;
use App\Services\WhatsApp\ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WhatsAppRemindersCommand extends Command
{
    protected $signature = 'whatsapp:reminders {--send : Actually send messages}' . "\n"
        . '                           {--rule=whatsapp_remind_before : Automation rule ID to use}';
    protected $description = 'Send WhatsApp reminders using automation rule config';

    protected $whatsapp;
    protected $sentCount = 0;
    protected $failedCount = 0;

    public function handle()
    {
        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        $this->whatsapp = app(WhatsAppService::class);

        if ($enabled != '1') {
            $this->error('WhatsApp reminders are disabled (whatsapp_enabled).');
            return Command::FAILURE;
        }

        // ── Read per-rule config from new JSON ──
        $ruleId = $this->normalizeAutomationRuleId($this->option('rule'));
        $rulesConfig = $this->getAutomationRuleConfig($ruleId);

        if (!$rulesConfig) {
            $this->error("Rule '{$ruleId}' not found in automation config.");
            return Command::FAILURE;
        }

        if (!$rulesConfig['enabled']) {
            $this->error("Rule '{$ruleId}' is disabled in automation settings.");
            return Command::FAILURE;
        }

        // Check day of week (our config: 0=سبت, 1=أحد, ..., 6=جمعة)
        $ourDay = (now()->dayOfWeek + 1) % 7; // Convert Carbon (0=Sun) to our (0=Sat)
        if (!in_array($ourDay, $rulesConfig['days'])) {
            $this->info('Today is not in the configured days for this rule. Skipping.');
            return Command::SUCCESS;
        }

        // ── Connection check ──
        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            $this->error('WhatsApp is not connected.');
            return Command::FAILURE;
        }

        // ── Template from rule config (fallback to old key) ──
        $templateType = $rulesConfig['template'] ?? 'reminder';
        $template = WhatsAppTemplateService::getBody($templateType);
        if (empty($template)) {
            $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
                ?? WhatsAppMessageBuilder::defaultTemplate();
        }

        $filters = [
            'client_type' => $rulesConfig['filter_client_type'] ?? 'all',
            'subscription_id' => $rulesConfig['filter_subscription_id'] ?? null,
            'min_unpaid' => (int) ($rulesConfig['filter_min_unpaid'] ?? 0),
            'client_status' => $rulesConfig['filter_client_status'] ?? 'all',
        ];

        $delay = (int) (DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value') ?? 10);
        $days = abs((int) ($rulesConfig['days_offset'] ?? 3));

        $reminderService = app(ReminderService::class);
        $preview = $ruleId === 'whatsapp_overdue'
            ? $reminderService->getOverduePreview($filters)
            : $reminderService->getBeforeDisconnectionPreview($days, $filters);

        if (empty($preview['clients'])) {
            $this->info('No clients to notify for this rule.');
            return Command::SUCCESS;
        }

        $this->displayPreview($preview['clients']);

        if (!$this->option('send')) {
            $this->info('');
            $this->info('Run with --send flag to actually send messages.');
            return Command::SUCCESS;
        }

        $this->info('');
        $this->info('=== ' . $this->getTrans('sending') . ' ===');

        $clientIds = array_map(fn ($client) => (int) $client['id'], $preview['clients']);
        $result = $reminderService->sendReminders($ruleId, $clientIds, $templateType, [
            'days' => $days,
            'delay_seconds' => $delay,
            'sent_by' => 'system:cron',
        ]);

        foreach ($result['details'] as $index => $detail) {
            $step = $index + 1;
            $this->info("[{$step}/{$result['total']}] " . $this->getTrans('sending_to') . " {$detail['client_name']} ({$detail['phone']})...");

            if ($detail['status'] === 'sent') {
                $this->info('✓ ' . $this->getTrans('sent_success'));
            } elseif ($detail['status'] === 'skipped') {
                $this->info('- Skipped: ' . ($detail['error'] ?? 'No eligible invoices'));
            } else {
                $this->error('✗ ' . $this->getTrans('failed') . ': ' . ($detail['error'] ?? 'Unknown'));
            }
        }

        $this->sentCount = (int) $result['sent'];
        $this->failedCount = (int) $result['failed'];

        $this->info('');
        $this->info('=== ' . $this->getTrans('done') . ' ===');
        $this->info($this->getTrans('summary', ['sent' => $this->sentCount, 'failed' => $this->failedCount]));

        if (!empty($result['skipped'])) {
            $this->info('Skipped: ' . $result['skipped']);
        }

        return Command::SUCCESS;
    }

    /**
     * Get a single automation rule's config from the JSON blob.
     */
    protected function getAutomationRuleConfig(string $ruleId): ?array
    {
        $ruleId = $this->normalizeAutomationRuleId($ruleId);
        $stored = DB::table('app_config')->where('key', 'whatsapp_automation_rules')->value('value');

        if (!$stored) {
            return null;
        }

        $rules = json_decode($stored, true);
        if (isset($rules['whatsapp_custom']) && !isset($rules['whatsapp_overdue'])) {
            $rules['whatsapp_overdue'] = $rules['whatsapp_custom'];
        }

        $rule = $rules[$ruleId] ?? null;

        if (!$rule) {
            return null;
        }

        // Ensure defaults
        return [
            'enabled' => $rule['enabled'] ?? false,
            'time' => $rule['time'] ?? '09:00',
            'days' => $rule['days'] ?? [0, 1, 2, 3, 4, 5, 6],
            'template' => $rule['template'] ?? 'reminder',
            'days_offset' => (int) ($rule['days_offset'] ?? -3),
            // Filter settings
            'filter_client_type' => $rule['filter_client_type'] ?? 'all',
            'filter_subscription_id' => $rule['filter_subscription_id'] ?? ($rule['filter_subscription'] ?? null),
            'filter_min_unpaid' => (int) ($rule['filter_min_unpaid'] ?? 0),
            'filter_client_status' => $rule['filter_client_status'] ?? ($rule['filter_status'] ?? 'all'),
        ];
    }

    protected function normalizeAutomationRuleId(string $ruleId): string
    {
        return $ruleId === 'whatsapp_custom' ? 'whatsapp_overdue' : $ruleId;
    }

    protected function displayPreview($previewData)
    {
        $isArabic = app()->getLocale() === 'ar';

        $headers = $isArabic
            ? [$this->getTrans('client'), $this->getTrans('phone'), $this->getTrans('total_due'), $this->getTrans('invoices')]
            : ['Client', 'Phone', 'Total Due', 'Invoices'];

        $rows = [];
        $grandTotal = 0;

        foreach ($previewData as $data) {
            $invoiceCollection = collect($data['invoices'] ?? []);
            $totalAmount = (float) ($data['total_amount'] ?? 0);
            $grandTotal += $totalAmount;
            $invoiceLines = $invoiceCollection->map(function ($inv) {
                $dueDate = is_array($inv) ? ($inv['due_date'] ?? null) : ($inv->due_date ?? null);
                $amount = is_array($inv)
                    ? (float) ($inv['total'] ?? $inv['amount'] ?? 0)
                    : (float) ($inv->remaining_amount ?? $inv->amount ?? 0);
                $monthFormatted = $dueDate ? Carbon::parse($dueDate)->format('m / Y') : '-';
                return $monthFormatted . ' - ' . number_format($amount, 2) . '$';
            })->toArray();
            $rows[] = [
                $data['client_name'] ?? $data['name'] ?? 'Unknown',
                $data['phone'] ?? '',
                '$' . number_format($totalAmount, 2),
                implode("\n", $invoiceLines),
            ];
        }

        $this->table($headers, $rows);

        $clientCount = count($previewData);
        if ($isArabic) {
            $this->info("العملاء المراد إرسالهم: {$clientCount} | الإجمالي المستحق: \$" . number_format($grandTotal, 2));
        } else {
            $this->info("Clients to notify: {$clientCount} | Total outstanding: \$" . number_format($grandTotal, 2));
        }
    }

    protected function getTrans($key, $replace = [])
    {
        $translations = [
            'client' => 'العميل',
            'phone' => 'الهاتف',
            'total_due' => 'الإجمالي',
            'invoices' => 'الفواتير',
            'sending' => 'جاري الإرسال',
            'sending_to' => 'جاري إرسال الرسالة إلى',
            'sent_success' => 'تم الإرسال بنجاح',
            'failed' => 'فشل الإرسال',
            'waiting' => 'انتظار',
            'seconds' => 'ثانية',
            'done' => 'تم الانتهاء',
            'summary' => 'تم الإرسال: :sent | فشل: :failed',
        ];

        $text = $translations[$key] ?? $key;
        foreach ($replace as $placeholder => $value) {
            $text = str_replace(":{$placeholder}", $value, $text);
        }
        return $text;
    }
}
