<?php

namespace App\Services\WhatsApp;

use App\Models\Admin\Client;
use App\Models\Admin\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 🔔 Reminder Service — Central reminder logic for WhatsApp automation.
 *
 * Handles preview and sending for:
 * - Before Disconnection (invoices due in X days)
 * - Overdue Reminder (invoices past due date)
 */
class ReminderService
{
    /**
     * 📋 Preview: Before Disconnection.
     * Returns clients with invoices due within $days from now.
     */
    public function getBeforeDisconnectionPreview(int $days = 3, array $filters = []): array
    {
        $dueDate = Carbon::today()->addDays($days);

        $invoices = Invoice::query()
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<=', $dueDate)
            ->whereDate('due_date', '>=', Carbon::today())
            ->whereNull('deleted_at')
            ->with('client')
            ->get();

        $invoices = $this->applyFilters($invoices, $filters);

        return $this->buildPreview($invoices, "Send {$days} days before due date");
    }

    /**
     * 📋 Preview: Overdue Reminder.
     * Returns clients with invoices past due date.
     */
    public function getOverduePreview(array $filters = []): array
    {
        $invoices = Invoice::query()
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', Carbon::today())
            ->whereNull('deleted_at')
            ->with('client')
            ->get();

        $invoices = $this->applyFilters($invoices, $filters);

        return $this->buildPreview($invoices, "Send for invoices past due date");
    }

    /**
     * 📨 Send reminders to given client IDs.
     */
    public function sendReminders(array $clientIds, string $template = 'payment_reminder'): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($clientIds as $index => $clientId) {
            $client = Client::find($clientId);
            if (!$client || empty($client->phone)) {
                $failed++;
                continue;
            }

            $invoices = Invoice::query()
                ->where('client_id', $clientId)
                ->where('status', 'unpaid')
                ->whereNull('deleted_at')
                ->get();

            if ($invoices->isEmpty()) {
                continue;
            }

            // Build message
            $message = $this->buildMessage($client, $invoices, $template);

            // Send via WhatsApp
            try {
                $whatsapp = app(\App\Services\WhatsAppService::class);
                $result = $whatsapp->sendText($client->phone, $message);

                if ($result['success']) {
                    $sent++;
                    // Log the message
                    DB::table('whatsapp_message_log')->insert([
                        'client_id' => $clientId,
                        'phone' => $client->phone,
                        'message' => $message,
                        'status' => 'sent',
                        'type' => 'reminder',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }

            // Delay 10 seconds between messages (anti-ban)
            if ($index < count($clientIds) - 1) {
                sleep(10);
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($clientIds),
        ];
    }

    /**
     * 🔍 Apply filters to invoice collection.
     */
    private function applyFilters($invoices, array $filters): \Illuminate\Support\Collection
    {
        return $invoices->filter(function ($invoice) use ($filters) {
            $client = $invoice->client;
            if (!$client) return false;

            // Client type filter
            if (!empty($filters['client_type']) && $filters['client_type'] !== 'all') {
                if (($client->client_type ?? '') !== $filters['client_type']) return false;
            }

            // Subscription filter
            if (!empty($filters['subscription_id']) && $filters['subscription_id'] !== 'all') {
                if (($client->subscription_id ?? 0) != $filters['subscription_id']) return false;
            }

            // Min unpaid filter
            if (!empty($filters['min_unpaid']) && $filters['min_unpaid'] > 0) {
                $unpaidCount = Invoice::where('client_id', $client->id)
                    ->where('status', 'unpaid')
                    ->whereNull('deleted_at')
                    ->count();
                if ($unpaidCount < $filters['min_unpaid']) return false;
            }

            // Client status filter
            if (!empty($filters['client_status']) && $filters['client_status'] !== 'all') {
                $isActive = ($client->is_active ?? '1') == '1';
                if ($filters['client_status'] === 'active' && !$isActive) return false;
                if ($filters['client_status'] === 'inactive' && $isActive) return false;
            }

            return true;
        });
    }

    /**
     * 📊 Build preview data from invoices.
     */
    private function buildPreview($invoices, string $description): array
    {
        $grouped = $invoices->groupBy('client_id');

        $clientList = $grouped->map(function ($clientInvoices, $clientId) {
            $client = $clientInvoices->first()->client;
            return [
                'id' => $clientId,
                'name' => $client->name ?? 'Unknown',
                'phone' => $client->phone ?? '',
                'invoices' => $clientInvoices->map(fn($inv) => [
                    'id' => $inv->id,
                    'due_date' => $inv->due_date,
                    'total' => $inv->amount,
                ])->toArray(),
                'total_amount' => $clientInvoices->sum('amount'),
            ];
        })->values();

        return [
            'description' => $description,
            'client_count' => $grouped->count(),
            'invoice_count' => $invoices->count(),
            'total_amount' => $invoices->sum('amount'),
            'clients' => $clientList,
        ];
    }

    /**
     * 📝 Build message from template.
     */
    private function buildMessage($client, $invoices, string $template): string
    {
        $total = $invoices->sum('amount');
        $invoiceDetails = $invoices->map(function ($inv) {
            $due = Carbon::parse($inv->due_date)->format('Y-m-d');
            return "❌ {$due}      \${$inv->amount}";
        })->implode("\n");

        $supportPhone = DB::table('app_config')
            ->where('key', 'support_phone')
            ->value('value') ?? '70781562';

        $message = "مرحباً {$client->name}\n";
        $message .= "لديك فواتير مستحقة:\n";
        $message .= "{$invoiceDetails}\n";
        $message .= "الإجمالي: \${$total}\n";
        $message .= "يرجى الدفع في أقرب وقت.\n";
        $message .= "للاستفسار: {$supportPhone}";

        return $message;
    }
}
