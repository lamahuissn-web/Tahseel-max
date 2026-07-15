<?php

namespace App\Console\Commands;

use App\Models\Admin\Invoice;
use App\Services\WhatsAppMessageBuilder;
use App\Services\WhatsAppService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use App\Services\WhatsApp\InvoiceEligibilityService;
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
        $ruleId = $this->option('rule');
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

        // ── Legacy settings (still read from old keys as fallback) ──
        // Client filter from rule config (overrides legacy key)
        $clientType = $rulesConfig['filter_client_type'] ?? DB::table('app_config')->where('key', 'whatsapp_auto_client_type')->value('value') ?? 'all';
        $minAmount = (float) (DB::table('app_config')->where('key', 'whatsapp_auto_min_amount')->value('value') ?? 0);
        $autoStatus = DB::table('app_config')->where('key', 'whatsapp_auto_status')->value('value') ?? 'unpaid,partial';
        $delay = (int) (DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value') ?? 10);
        $skipHours = (int) (DB::table('app_config')->where('key', 'whatsapp_auto_skip_hours')->value('value') ?? 24);

        $statuses = array_map('trim', explode(',', $autoStatus));
        
        // Min unpaid filter from rule config
        $minUnpaid = (int) ($rulesConfig['filter_min_unpaid'] ?? 0);
        
        $today = Carbon::today();
        $targetDates = [];

        // ── Days offset from rule config ──
        $daysOffset = (int) ($rulesConfig['days_offset'] ?? -3);
        if ($daysOffset < 0) {
            // Negative = before due date (reminder)
            $targetDates[] = $today->copy()->addDays(abs($daysOffset))->format('Y-m-d');
        } elseif ($daysOffset > 0) {
            // Positive = after due date (follow-up)
            $targetDates[] = $today->copy()->subDays($daysOffset)->format('Y-m-d');
        } else {
            // Zero = due today
            $targetDates[] = $today->format('Y-m-d');
        }

        // Also check legacy remind_on_due / remind_after (keep for backward compat)
        $onDue = DB::table('app_config')->where('key', 'whatsapp_remind_on_due')->value('value');
        if ($onDue == '1') {
            $targetDates[] = $today->format('Y-m-d');
        }

        $afterDays = DB::table('app_config')->where('key', 'whatsapp_remind_after')->value('value') ?? '';
        if (!empty($afterDays)) {
            $afterDaysArray = array_map('intval', array_filter(explode(',', $afterDays)));
            foreach ($afterDaysArray as $days) {
                if ($days > 0) {
                    $targetDates[] = $today->copy()->subDays($days)->format('Y-m-d');
                }
            }
        }

        $targetDates = array_unique($targetDates);

        // ── Build query ──
        $query = Invoice::with(['client'])
            ->selectRaw('*, (SELECT COUNT(*) FROM tbl_invoices inv2 WHERE inv2.client_id = tbl_invoices.client_id AND inv2.status IN ("unpaid","partial")) as total_unpaid')
            ->whereIn('due_date', $targetDates)
            ->where('due_date', '<=', Carbon::today())  // Only include due/overdue invoices
            ->whereIn('status', $statuses)
            ->whereHas('client', function ($q) use ($rulesConfig) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
                
                $fcType = $rulesConfig['filter_client_type'] ?? 'all';
                if ($fcType !== 'all') {
                    $q->where('client_type', $fcType);
                }
                
                $fcStatus = $rulesConfig['filter_client_status'] ?? 'all';
                if ($fcStatus !== 'all') {
                    $q->where('is_active', $fcStatus === 'active' ? 1 : 0);
                }
                
                $fcSub = $rulesConfig['filter_subscription_id'] ?? null;
                if ($fcSub) {
                    $q->where('subscription_id', $fcSub);
                }
            })
            ->orderBy('due_date')
            ->get()
            ->groupBy('client_id');

        if ($query->isEmpty()) {
            $this->info('No unpaid invoices found for the configured dates.');
            return Command::SUCCESS;
        }

        // ── Build preview ──
        $previewData = [];

        foreach ($query as $clientId => $clientInvoices) {
            $client = $clientInvoices->first()->client;
            if (!$client || !$client->phone) continue;

            $totalAmount = $clientInvoices->sum('remaining_amount');

            // Min unpaid filter from rule config
            $clientTotalUnpaid = $clientInvoices->first()->total_unpaid ?? $clientInvoices->count();
            if ($minUnpaid > 0 && $clientTotalUnpaid < $minUnpaid) {
                $this->info("Skipped {$client->name}: only {$clientTotalUnpaid} unpaid (min {$minUnpaid})");
                continue;
            }

            if ($minAmount > 0 && $totalAmount < $minAmount) {
                $this->info("Skipped {$client->name}: total \${$totalAmount} below minimum \${$minAmount}");
                continue;
            }

            $skipFrom = $today->copy()->subHours($skipHours);
            $alreadyNotified = Invoice::where('client_id', $clientId)
                ->whereNotNull('last_notified_at')
                ->where('last_notified_at', '>=', $skipFrom)
                ->exists();

            if ($alreadyNotified) {
                $this->info("Skipped {$client->name}: already notified within {$skipHours} hours");
                continue;
            }

            $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($clientInvoices);
            $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);
            $phone = preg_replace('/[^0-9]/', '', $client->phone);

            $invoiceSummary = $clientInvoices->map(function ($inv) {
                return Carbon::parse($inv->due_date)->format("m / Y");
            })->join(', ');

            $previewData[] = [
                'client_id' => $clientId,
                'client_name' => $client->name,
                'phone' => $phone,
                'total_amount' => $totalAmount,
                'invoice_details_list' => $invoiceDetailsList,
                'invoice_summary' => $invoiceSummary,
                'message' => $message,
                'invoices' => $clientInvoices,
            ];
        }

        if (empty($previewData)) {
            $this->info('No clients to notify (all filtered out).');
            return Command::SUCCESS;
        }

        $this->displayPreview($previewData);

        if (!$this->option('send')) {
            $this->info('');
            $this->info('Run with --send flag to actually send messages.');
            return Command::SUCCESS;
        }

        // ── Send ──
        $this->info('');
        $this->info('=== ' . $this->getTrans('sending') . ' ===');

        $total = count($previewData);
        foreach ($previewData as $index => $data) {
            $step = $index + 1;
            $this->info("[{$step}/{$total}] " . $this->getTrans('sending_to') . " {$data['client_name']} ({$data['phone']})...");

            $result = $this->whatsapp->send($data['phone'], $data['message']);

            $invoiceIds = $data['invoices']->pluck('id')->toArray();

            DB::table('whatsapp_message_logs')->insert([
                'client_id' => $data['client_id'],
                'client_name' => $data['client_name'],
                'invoice_id' => $data['invoices']->first()->id,
                'invoice_ids' => json_encode($invoiceIds),
                'phone' => $data['phone'],
                'message' => $data['message'],
                'template_type' => $templateType,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error' => $result['error'] ?? null,
                'sent_by' => 'system:cron',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($result['success']) {
                $this->info('✓ ' . $this->getTrans('sent_success'));
                Invoice::whereIn('id', $invoiceIds)->update(['last_notified_at' => now()]);
                $this->sentCount++;
            } else {
                $this->error('✗ ' . $this->getTrans('failed') . ": {$result['error']}");
                $this->failedCount++;
            }

            if ($index < $total - 1) {
                $this->info($this->getTrans('waiting') . ' (' . $delay . ' ' . $this->getTrans('seconds') . ')...');
                sleep($delay);
            }
        }

        $this->info('');
        $this->info('=== ' . $this->getTrans('done') . ' ===');
        $this->info($this->getTrans('summary', ['sent' => $this->sentCount, 'failed' => $this->failedCount]));

        return Command::SUCCESS;
    }

    /**
     * Get a single automation rule's config from the JSON blob.
     */
    protected function getAutomationRuleConfig(string $ruleId): ?array
    {
        $stored = DB::table('app_config')->where('key', 'whatsapp_automation_rules')->value('value');

        if (!$stored) {
            return null;
        }

        $rules = json_decode($stored, true);
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
            'filter_subscription_id' => $rule['filter_subscription_id'] ?? null,
            'filter_min_unpaid' => (int) ($rule['filter_min_unpaid'] ?? 0),
            'filter_client_status' => $rule['filter_client_status'] ?? 'all',
        ];
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
            $grandTotal += $data['total_amount'];
            $invoiceLines = $data['invoices']->map(function ($inv) {
                $monthFormatted = Carbon::parse($inv->due_date)->format("m / Y");
                $amt = number_format($inv->remaining_amount, 2);
                return "{$monthFormatted} - {$amt}\$";
            })->toArray();
            $rows[] = [
                $data['client_name'],
                $data['phone'],
                '$' . number_format($data['total_amount'], 2),
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
