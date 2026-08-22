<?php

namespace App\Services\WhatsApp;

use App\Models\Admin;
use App\Models\Admin\Invoice;
use App\Models\Admin\Revenue;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsAppMessageBuilder;
use App\Services\WhatsApp\WhatsAppPhoneValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentReceiptNotifier
{
    public function __construct(private readonly WhatsAppMessageDispatcher $dispatcher) {}

    public function notifyPayment(
        Invoice $invoice,
        Revenue $revenue,
        string $paymentReference,
    ): string {
        return $this->notify($invoice, $revenue, $paymentReference);
    }

    /**
     * Send a WhatsApp receipt notification after invoice payment.
     * Non-blocking — payment always succeeds regardless of WhatsApp delivery.
     */
    public function notify(
        Invoice $invoice,
        ?Revenue $paymentRevenue = null,
        ?string $paymentReference = null,
    ): string {
        try {
            // 1. Get client with phone number
            $client = $invoice->client;
            if (! $client) {
                Log::warning('[WhatsApp Receipt] Invoice has no client', [
                    'invoice_id' => $invoice->id,
                ]);

                return 'not_applicable';
            }

            $phone = $client->phone ?? null;
            if (empty($phone)) {
                Log::info('[WhatsApp Receipt] Client has no phone number — skipping', [
                    'client_id' => $client->id,
                    'invoice_id' => $invoice->id,
                ]);

                return 'not_applicable';
            }

            // Spec 019: fail fast on unsendable numbers (e.g. 961000000).
            // The payment itself is never blocked; we log a failed row so the
            // admin can SEE why no receipt arrived, and skip the provider call.
            $normalized = WhatsAppPhoneValidator::normalize($phone);

            // 2. Get paid invoice details
            $paidMonth = $invoice->due_date
                ? date('m', strtotime($invoice->due_date))
                : date('m');
            $paidYear = $invoice->due_date
                ? date('Y', strtotime($invoice->due_date))
                : date('Y');
            $paymentDate = $invoice->paid_date
                ? date('d/m/Y h:i A', strtotime($invoice->paid_date))
                : date('d/m/Y h:i A');
            $paidDueDate = $invoice->due_date
                ? date('d/m/Y', strtotime($invoice->due_date))
                : $paymentDate;

            // 2b. For service invoices, show description instead of date in {{2}}
            $isService = $invoice->invoice_type === 'service';
            $paidDescription = $isService
                ? ($invoice->notes ?: 'خدمة')
                : "{$paidMonth} / {$paidYear}";

            // 3a. Get collector name and payment time from Revenue record
            $revenue = $paymentRevenue
                ?? Revenue::where('invoice_id', $invoice->id)->latest('id')->first();
            $paidAmountNumeric = (float) ($revenue?->amount ?: $invoice->paid_amount ?: $invoice->amount);
            $paidAmount = WhatsAppMessageBuilder::formatAmount($paidAmountNumeric);
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

            // 4b. Query ALL non-deleted unpaid invoices for total outstanding + breakdown
            $allUnpaid = \App\Models\Admin\Invoice::where('client_id', $client->id)
                ->where('status', 'unpaid')
                ->where('id', '!=', $invoice->id)
                ->whereNull('deleted_at')
                ->get();

            // Unpaid subscription months (for {{8}}) — always show regardless of invoice type
            $unpaidSubMonths = $allUnpaid->where('invoice_type', 'subscription')
                ->pluck('due_date')
                ->map(fn ($d) => (int) date('m', strtotime($d)))
                ->sort()
                ->values();
            $unpaidSubStr = $unpaidSubMonths->isEmpty() ? 'لا يوجد' : $unpaidSubMonths->implode(', ');

            // Unpaid services with notes fallback (for {{9}}) — exclude the just-paid invoice
            $unpaidServices = $allUnpaid->where('invoice_type', 'service');
            $svcParts = [];
            foreach ($unpaidServices as $svc) {
                $desc = ! empty($svc->notes) ? $svc->notes : 'خدمة';
                $svcParts[] = "{$desc} (\$".WhatsAppMessageBuilder::formatAmount($svc->amount).")";
            }
            $unpaidSvcStr = empty($svcParts) ? 'لا يوجد' : implode(', ', $svcParts);

            // Total outstanding across ALL unpaid invoices (for {{5}})
            $totalOutstanding = (float) $allUnpaid->sum('amount');

            // 5. Calculate totals
            // Total due BEFORE this payment (the paid invoice plus other due invoices)
            // We exclude the just-paid invoice from remaining, but count its amount
            // in the "before" total so the customer sees the full picture.
            $totalDue = 0;
            foreach ($unpaidInvoices as $unpaid) {
                $totalDue += (float) $unpaid->remaining_amount;
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
            if ($paymentReference !== null) {
                $message .= "\n🔖 مرجع القبض: {$paymentReference}\n";
            }

            // 6b. Build template variables for Zernio (stored for sendTemplate)
            $templateVariables = $this->buildTemplateVariables(
                $customerName,
                $paidDescription,
                $paidAmount,
                $paidDueDate,
                $collectorName,
                $paymentTime,
                $totalOutstanding,
                $unpaidSubStr,
                $unpaidSvcStr,
            );

            // 7. Enqueue as pending so it appears in Queue
            $messageLog = null;
            try {
                // Spec 019: invalid phone -> create a FAILED row (visible reason),
                // never dispatch to the provider, never block the payment.
                if (! $normalized['valid']) {
                    WhatsAppMessageLog::query()->create([
                        'client_id' => $client->id,
                        'client_name' => $client->name ?? $customerName,
                        'invoice_id' => $invoice->id,
                        'payment_reference' => $paymentReference,
                        'phone' => $phone,
                        'message' => $message,
                        'template_type' => 'receipt',
                        'template_variables' => $templateVariables,
                        'status' => 'failed',
                        'error' => 'Invalid phone number ('.$normalized['reason'].'): '.$phone,
                        'sent_by' => 'system:autoreceipt|invalid-phone',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    Log::warning('[WhatsApp Receipt] Invalid phone — receipt NOT sent', [
                        'client_id' => $client->id,
                        'invoice_id' => $invoice->id,
                        'phone' => $phone,
                        'reason' => $normalized['reason'],
                    ]);

                    return 'not_applicable';
                }

                $batchId = (string) Str::uuid();
                $messageData = [
                    'client_id' => $client->id,
                    'client_name' => $client->name ?? $customerName,
                    'invoice_id' => $invoice->id,
                    'payment_reference' => $paymentReference,
                    'phone' => $normalized['e164'],
                    'message' => $message,
                    'template_type' => 'receipt',
                    'template_variables' => $templateVariables,
                    'status' => 'pending',
                    'error' => null,
                    'sent_by' => 'system:autoreceipt|payment:'.($paymentReference ?? 'legacy').'|batch:'.$batchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $messageLog = $paymentReference === null
                    ? WhatsAppMessageLog::query()->create($messageData)
                    : WhatsAppMessageLog::query()->firstOrCreate(
                        ['payment_reference' => $paymentReference],
                        $messageData,
                    );
                if (! $messageLog->wasRecentlyCreated) {
                    return 'queued';
                }
                $this->dispatcher->dispatch($messageLog);

                Log::info('[WhatsApp Receipt] Queued for delivery', [
                    'client_id' => $client->id,
                    'invoice_id' => $invoice->id,
                    'batch_id' => $batchId,
                    'unpaid_count' => $unpaidInvoices->count(),
                ]);

                return 'queued';
            } catch (\Exception $logErr) {
                Log::warning('[WhatsApp Receipt] Failed to enqueue message', [
                    'invoice_id' => $invoice->id,
                    'exception' => get_class($logErr),
                ]);

                return $messageLog?->exists ? 'queued' : 'retry';
            }
        } catch (\Exception $e) {
            Log::error('[WhatsApp Receipt] Failed to send notification', [
                'invoice_id' => $invoice->id,
                'exception' => get_class($e),
            ]);

            return 'retry';
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
        $message .= '📊 إجمالي المستحق قبل الدفع: $'.WhatsAppMessageBuilder::formatAmount($totalBeforePayment)."\n";
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
                $uAmount = WhatsAppMessageBuilder::formatAmount($unpaid->remaining_amount);

                $message .= "❌ {$uMonth} / {$uYear} — {$uDate}      \${$uAmount}\n";
            }
        } else {
            $message .= "\n🟢 لا توجد أي فواتير غير مدفوعة.\n";
        }

        $message .= "\n💰 إجمالي المستحق: \$".WhatsAppMessageBuilder::formatAmount($totalDue)."\n";

        $message .= "\n━━━━━━━━━━━━━━━━━━\n";

        $message .= "\n⚠️ ملاحظة:\n";
        $message .= "هذا الإشعار يُعتبر إثبات دفع إلكتروني مسجل في النظام.\n\n";
        $message .= "شكراً لاختياركم MegaNet 🌹\n";

        return $message;
    }

    /**
     * Build template variables array for Zernio/Meta template sends.
     *
     * Maps to Option A template:
     *  {{1}} client name
     *  {{2}} paid subscription month/year
     *  {{3}} due date
     *  {{4}} amount paid
     *  {{5}} total due before payment
     *  {{6}} collected by
     *  {{7}} payment time
     *  {{8}} last paid month/year
     *  {{9}} remaining balance
     *  {{10}} payment reference
     *
     * @return array<int, string>
     */
    public function buildTemplateVariables(
        string $customerName,
        string $paidDescription,
        string $paidAmount,
        string $paidDueDate,
        string $collectorName,
        string $paymentTime,
        float $totalOutstanding,
        string $unpaidSubStr,
        string $unpaidSvcStr,
    ): array {
        return [
            $customerName,                                    // {{1}}
            $paidDescription,                                 // {{2}} — date for subscription, description for service
            $paidDueDate,                                     // {{3}}
            $paidAmount,                                      // {{4}}
            WhatsAppMessageBuilder::formatAmount($totalOutstanding),  // {{5}}
            $collectorName,                                   // {{6}}
            $paymentTime,                                     // {{7}}
            $unpaidSubStr,                                    // {{8}}
            $unpaidSvcStr,                                    // {{9}}
        ];
    }
}
