<?php

namespace App\Services\WhatsApp;

use App\Models\Admin\Invoice;
use App\Models\Admin\Revenue;
use App\Models\Admin;
use App\Models\Clients;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
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
            $paidDueDate = $invoice->due_date
                ? date('d/m/Y', strtotime($invoice->due_date))
                : $paymentDate;

            // 3a. Get collector name and payment time from Revenue record
            $revenue = Revenue::where('invoice_id', $invoice->id)->first();
            $collectorName = 'النظام';
            $paymentTime = $paymentDate;
            if ($revenue) {
                // Look up by Admin ID directly (collected_by = auth()->id())
                $adminUser = Admin::find($revenue->collected_by);
                if ($adminUser) {
                    $collectorName = $adminUser->name;
                } else {
                    $collectorName = $revenue->collected_by_name ?? 'النظام';
                }
                if ($revenue->received_at) {
                    $paymentTime = date('d/m/Y h:i A', strtotime($revenue->received_at));
                }
            }

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

            // 4. Get only currently due / overdue unpaid invoices for this client.
            // Do NOT include future invoices in the receipt message — they confuse customers.
            $unpaidInvoices = InvoiceEligibilityService::getEligibleInvoices($client->id);

            // 5. Calculate totals
            // Total due BEFORE this payment (the paid invoice plus other due invoices)
            // We exclude the just-paid invoice from remaining, but count its amount
            // in the "before" total so the customer sees the full picture.
            $paidAmountNumeric = (float)($invoice->paid_amount ?: $invoice->amount);
            $totalDue = 0;
            foreach ($unpaidInvoices as $unpaid) {
                $totalDue += (float)$unpaid->remaining_amount;
            }
            $totalBeforePayment = $totalDue + $paidAmountNumeric;

            // 6. Build the message
            $customerName = $client->name ?? 'عميل';
            $message = $this->buildMessage(
                $customerName,
                $paidMonth,
                $paidYear,
                $paidAmount,
                $paidDueDate,
                $collectorName,
                $paymentTime,
                $lastPaidMonth,
                $lastPaidYear,
                $unpaidInvoices,
                $totalDue,
                $totalBeforePayment
            );

            // 7. Enqueue as pending so it appears in Queue
            try {
                $batchId = (string) \Illuminate\Support\Str::uuid();
                DB::table('whatsapp_message_logs')->insert([
                    'client_id' => $client->id,
                    'client_name' => $client->name ?? $customerName,
                    'phone' => $phone,
                    'message' => $message,
                    'template_type' => 'receipt',
                    'status' => 'pending',
                    'error' => null,
                    'sent_by' => 'system:autoreceipt|batch:' . $batchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Start background batch processor
                $phpBinary = is_executable('/usr/bin/php') ? '/usr/bin/php' : PHP_BINARY;
                $php = escapeshellarg($phpBinary);
                $artisan = escapeshellarg(base_path('artisan'));
                $cmd = "{$php} {$artisan} whatsapp:process-pending {$batchId} --delay=0 > /dev/null 2>&1 &";
                exec($cmd);

                Log::info('[WhatsApp Receipt] Queued for delivery', [
                    'client_id' => $client->id,
                    'invoice_id' => $invoice->id,
                    'batch_id' => $batchId,
                    'unpaid_count' => $unpaidInvoices->count(),
                ]);
            } catch (\Exception $logErr) {
                Log::warning('[WhatsApp Receipt] Failed to enqueue message', ['error' => $logErr->getMessage()]);
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
        string $paidDueDate,
        string $collectorName,
        string $paymentTime,
        string $lastPaidMonth,
        string $lastPaidYear,
        $unpaidInvoices,
        float $totalDue,
        float $totalBeforePayment
    ): string {
        $message = "🌐 MegaNet\n\n";
        $message .= "🧾 إيصال اشتراك الإنترنت\n\n";
        $message .= "👤 اسم المشترك: {$customerName}\n\n";
        $message .= "✅ تم تسجيل عملية الدفع بنجاح في النظام.\n\n";
        $message .= "📅 الاشتراك المسدد: {$paidMonth} / {$paidYear}\n";
        $message .= "🗓 تاريخ الاستحقاق: {$paidDueDate}\n";
        $message .= "💵 المبلغ المدفوع: \${$paidAmount}\n";
        $message .= "📊 إجمالي المستحق قبل الدفع: \$" . number_format($totalBeforePayment, 2) . "\n";
        $message .= "🧑 قبضت بواسطة: {$collectorName}\n";
        $message .= "⏱ وقت الدفع: {$paymentTime}\n";

        $message .= "\n━━━━━━━━━━━━━━━━━━\n";

        $message .= "\n📊 حالة الحساب (حتى تاريخ اليوم)\n\n";
        $message .= "🟢 آخر شهر مدفوع: {$lastPaidMonth} / {$lastPaidYear}\n";

        if ($unpaidInvoices->count() > 0) {
            $message .= "\n📌 الفواتير غير المسددة:\n";

            foreach ($unpaidInvoices as $unpaid) {
                $uMonth = $unpaid->due_date ? date('m', strtotime($unpaid->due_date)) : '??';
                $uYear = $unpaid->due_date ? date('Y', strtotime($unpaid->due_date)) : '??';
                $uDate = $unpaid->due_date ? date('d/m/Y', strtotime($unpaid->due_date)) : '??/??/????';
                $uAmount = number_format((float)$unpaid->remaining_amount, 2);

                $message .= "❌ {$uMonth} / {$uYear} — {$uDate}      \${$uAmount}\n";
            }
        } else {
            $message .= "\n🟢 لا توجد أي فواتير غير مدفوعة.\n";
        }

        $message .= "\n💰 إجمالي المستحق: \$" . number_format($totalDue, 2) . "\n";

        $message .= "\n━━━━━━━━━━━━━━━━━━\n";

        $message .= "\n⚠️ ملاحظة:\n";
        $message .= "هذا الإشعار يُعتبر إثبات دفع إلكتروني مسجل في النظام.\n\n";
        $message .= "شكراً لاختياركم MegaNet 🌹\n";

        return $message;
    }
}