<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Admin\Invoice;
use App\Notifications\InvoiceReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendOverdueReminders extends Command
{
    protected $signature = 'overdue:remind';
    protected $description = 'Send overdue invoice reminders to admins and Telegram';

    public function handle()
    {
        $today = Carbon::today();

        if (Carbon::parse(Cache::get('last_invoice_notification_date')) == $today) {
            $this->info('Already sent today');
            return 0;
        }

        $admins = Admin::where('status', '1')
            ->whereNull('deleted_at')
            ->whereHas('roles', function ($query) {
                $query->whereIn('id', [1, 7]);
            })
            ->get();

        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->where(function ($query) use ($today) {
                $query->whereNull('last_notified_at')
                    ->orWhereRaw("COALESCE(DATE_FORMAT(last_notified_at, '%Y-%m-%d'), '2000-01-01') < due_date");
            })
            ->get();

        $sentCount = 0;

        foreach ($overdueInvoices as $invoice) {
            if ($today->toDateString() >= Carbon::parse($invoice->due_date)->toDateString()) {
                $currency = get_app_config_data('currency') ?? 'جنيه';

                $notificationMessage = sprintf(
                    '⏰ فاتورة متأخرة: #%s | العميل: %s | المبلغ: %s %s',
                    $invoice->invoice_number,
                    $invoice->client->name ?? 'غير معروف',
                    number_format($invoice->amount, 2),
                    $currency
                );

                foreach ($admins as $admin) {
                    $admin->notify(new InvoiceReminderNotification($invoice));
                }

                if (!empty($admins)) {
                    sendOneSignalNotification1(
                        $admins,
                        $notificationMessage,
                        [
                            'invoice_id' => $invoice->id,
                            'type' => 'overdue_invoice',
                            'amount' => $invoice->amount,
                            'due_date' => $invoice->due_date,
                            'client' => $invoice->client->name ?? 'غير معروف',
                            'days_overdue' => $today->diffInDays($invoice->due_date),
                        ],
                        null
                    );
                }

                sendTelegramNotification($notificationMessage, 'overdue_reminder');

                $invoice->updateQuietly(['last_notified_at' => $today]);
                $sentCount++;
            }
        }

        Cache::put('last_invoice_notification_date', $today, now()->endOfDay());

        $this->info("Sent {$sentCount} overdue reminders");
        Log::info("Overdue reminders sent: {$sentCount} invoices");

        return 0;
    }
}
