<?php

namespace App\Console\Commands;

use App\Models\Admin\Invoice;
use App\Models\Clients;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WhatsAppRemindersCommand extends Command
{
    protected $signature = 'whatsapp:reminders';
    protected $description = 'Send WhatsApp reminders for unpaid invoices';

    protected $whatsapp;
    protected $sentCount = 0;
    protected $failedCount = 0;

    public function __construct(WhatsAppService $whatsapp)
    {
        parent::__construct();
        $this->whatsapp = $whatsapp;
    }

    public function handle()
    {
        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        if ($enabled != '1') {
            $this->info('WhatsApp reminders are disabled.');
            return;
        }

        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            $this->error('WhatsApp is not connected.');
            return;
        }

        $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
            ?? $this->defaultTemplate();

        $today = Carbon::today();

        // Before due date
        $beforeDays = (int) (DB::table('app_config')->where('key', 'whatsapp_remind_before')->value('value') ?? 3);
        if ($beforeDays > 0) {
            $beforeDate = $today->copy()->addDays($beforeDays);
            $this->sendRemindersForDate($beforeDate, $template);
        }

        // On due date
        $onDue = DB::table('app_config')->where('key', 'whatsapp_remind_on_due')->value('value');
        if ($onDue == '1') {
            $this->sendRemindersForDate($today, $template);
        }

        // After due date (overdue)
        $afterDays = DB::table('app_config')->where('key', 'whatsapp_remind_after')->value('value') ?? '1,3,7';
        $afterDaysArray = array_map('intval', array_filter(explode(',', $afterDays)));
        foreach ($afterDaysArray as $days) {
            if ($days > 0) {
                $afterDate = $today->copy()->subDays($days);
                $this->sendRemindersForDate($afterDate, $template);
            }
        }

        $this->info("WhatsApp reminders completed: {$this->sentCount} sent, {$this->failedCount} failed.");
    }

    protected function sendRemindersForDate($date, $template)
    {
        $dateStr = $date->format('Y-m-d');

        $invoices = Invoice::with(['client'])
            ->whereDate('due_date', $dateStr)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereHas('client', function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->get();

        foreach ($invoices as $invoice) {
            if (!$invoice->client || !$invoice->client->phone) continue;

            $message = $this->buildMessage($template, $invoice);
            $phone = preg_replace('/[^0-9]/', '', $invoice->client->phone);

            $result = $this->whatsapp->send($phone, $message);

            DB::table('whatsapp_message_logs')->insert([
                'client_id' => $invoice->client_id,
                'invoice_id' => $invoice->id,
                'phone' => $invoice->client->phone,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error' => $result['error'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($result['success']) {
                $invoice->update(['last_notified_at' => now()]);
                $this->sentCount++;
                $this->info("Sent to {$invoice->client->name} ({$phone})");
            } else {
                $this->failedCount++;
                $this->error("Failed for {$invoice->client->name} ({$phone}): {$result['error']}");
            }
        }
    }

    protected function buildMessage($template, $invoice)
    {
        $client = $invoice->client;
        $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->format('Y-m-d') : 'غير محدد';

        $message = str_replace('{name}', $client->name ?? 'العميل', $template);
        $message = str_replace('{amount}', number_format($invoice->remaining_amount, 2), $message);
        $message = str_replace('{due_date}', $dueDate, $message);
        $message = str_replace('{invoice_number}', $invoice->invoice_number ?? $invoice->id, $message);

        return $message;
    }

    protected function defaultTemplate()
    {
        return "مرحباً {name}،\n\nنود تذكيرك بأن فاتورتك رقم {invoice_number}\nبمبلغ {amount} مستحقة في {due_date}.\n\nيرجى السداد في أقرب وقت. شكراً لك.";
    }
}
