<?php

namespace App\Services\WhatsApp;

use App\Models\Admin\Invoice;
use App\Models\Clients;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class PaymentReceiptNotifier
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Send a WhatsApp receipt notification after invoice payment.
     * Non-blocking — payment always succeeds regardless of WhatsApp delivery.
     */
    public function notify(Invoice $invoice): void
    {
        try {
            // 1. Get client with phone number
            $client = $invoice->client;
            if (!$client) {
                Log::warning('[WhatsApp Receipt] Invoice has no client', [
                    'invoice_id' => $invoice->id,
                ]);
                return;
            }

            $phone = $client->phone ?? null;
            if (empty($phone)) {
                Log::info('[WhatsApp Receipt] Client has no phone number — skipping', [
                    'client_id' => $client->id,
                    'invoice_id' => $invoice->id,
                ]);
                return;
            }

            // 2. Get paid invoice details
            $paidMonth = $invoice->due_date
                ? date('m', strtotime($invoice->due_date))
                : date('m');
            $paidYear = $invoice->due_date
                ? date('Y', strtotime($invoice->due_date))
                : date('Y');
            $paidAmount = number_format((float)($invoice->paid_amount ?: $invoice->amount), 2);
            $paymentDate = $invoice->paid_date
                ? date('d/m/Y', strtotime($invoice->paid_date))
                : date('d/m/Y');

            // 3. Get the latest paid invoice overall (for "آخر شهر مدفوع")
            $lastPaidInvoice = Invoice::where('client_id', $client->id)
                ->where('status', 'paid')
                ->orderBy('due_date', 'desc')
                ->first();

            $lastPaidMonth = $lastPaidInvoice && $lastPaidInvoice->due_date
                ? date('m', strtotime($lastPaidInvoice->due_date))
                : $paidMonth;
            $lastPaidYear = $lastPaidInvoice && $lastPaidInvoice->due_date
                ? date('Y', strtotime($lastPaidInvoice->due_date))
                : $paidYear;

            // 4. Get unpaid invoices for this client (sorted ascending)
            $unpaidInvoices = Invoice::where('client_id', $client->id)
                ->where('id', '!=', $invoice->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->orderBy('due_date', 'asc')
                ->get();

            // 5. Calculate total outstanding
            $totalDue = 0;
            foreach ($unpaidInvoices as $unpaid) {
                $totalDue += (float)$unpaid->remaining_amount;
            }

            // 6. Build the message
            $customerName = $client->name ?? 'عميل';
            $message = $this->buildMessage(
                $customerName,
                $paidMonth,
                $paidYear,
                $paidAmount,
                $paymentDate,
                $lastPaidMonth,
                $lastPaidYear,
                $unpaidInvoices,
                $totalDue
            );

            // 7. Send via WhatsApp
            $result = $this->whatsapp->send($phone, $message);

            if ($result['success'] ?? false) {
                Log::info('[WhatsApp Receipt] Sent successfully', [
                    'client_id' => $client->id,
                    'invoice_id' => $invoice->id,
                    'phone' => $phone,
                    'unpaid_count' => $unpaidInvoices->count(),
                ]);
            } else {
                Log::warning('[WhatsApp Receipt] Send returned failure', [
                    'client_id' => $client->id,
                    'invoice_id' => $invoice->id,
                    'error' => $result['error'] ?? 'Unknown',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[WhatsApp Receipt] Failed to send notification', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Build the WhatsApp receipt message.
     *
     * Arabic body with Western numerals. Follows the exact template structure.
     */
    protected function buildMessage(
        string $customerName,
        string $paidMonth,
        string $paidYear,
        string $paidAmount,
        string $paymentDate,
        string $lastPaidMonth,
        string $lastPaidYear,
        $unpaidInvoices,
        float $totalDue
    ): string {
        $message = "🌐 MegaNet\n\n";
        $message .= "🧾 إيصال اشتراك الإنترنت\n\n";
        $message .= "👤 اسم المشترك: {$customerName}\n\n";
        $message .= "✅ تم تسجيل عملية الدفع بنجاح في النظام.\n\n";
        $message .= "📅 الاشتراك المسدد: {$paidMonth} / {$paidYear}\n";
        $message .= "💵 المبلغ المدفوع: \${$paidAmount}\n";

        $message .= "\n━━━━━━━━━━━━━━━━━━\n";

        $message .= "\n📊 حالة الحساب (حتى تاريخ اليوم)\n\n";
        $message .= "🟢 آخر شهر مدفوع: {$lastPaidMonth} / {$lastPaidYear}\n";

        if ($unpaidInvoices->count() > 0) {
            $message .= "\n📌 الفواتير غير المسددة:\n";

            foreach ($unpaidInvoices as $unpaid) {
                $uMonth = $unpaid->due_date ? date('m', strtotime($unpaid->due_date)) : '??';
                $uYear = $unpaid->due_date ? date('Y', strtotime($unpaid->due_date)) : '??';
                $uAmount = number_format((float)$unpaid->remaining_amount, 2);

                $message .= "❌ {$uMonth} / {$uYear}      \${$uAmount}\n";
            }
        } else {
            $message .= "\n🟢 لا توجد أي فواتير غير مدفوعة.\n";
        }

        $message .= "\n💰 إجمالي المستحق: \$" . number_format($totalDue, 2) . "\n";

        $message .= "\n━━━━━━━━━━━━━━━━━━\n";

        $message .= "\n⚠️ ملاحظة:\n";
        $message .= "هذا الإشعار يُعتبر إثبات دفع إلكتروني مسجل في النظام.\n\n";
        $message .= "🗓 تاريخ الدفع: {$paymentDate}\n\n";
        $message .= "شكراً لاختياركم MegaNet 🌹\n";

        return $message;
    }
}
