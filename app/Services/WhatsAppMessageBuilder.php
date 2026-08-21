<?php

namespace App\Services;

use Carbon\Carbon;

class WhatsAppMessageBuilder
{
    /**
     * Build a formatted list of unpaid invoices.
     * Each line: ❌ MM / YYYY      $amount
     * Grouped by subscription invoices and service invoices.
     */
    public static function buildInvoiceDetailsList($clientInvoices): string
    {
        $subscriptionLines = [];
        $serviceLines = [];

        foreach ($clientInvoices as $invoice) {
            $amount = WhatsAppMessageBuilder::formatAmount($invoice->remaining_amount);
            $monthNum = Carbon::parse($invoice->due_date)->format("m");
            $yearNum = Carbon::parse($invoice->due_date)->format("Y");
            $formattedMonth = str_pad($monthNum, 2, "0", STR_PAD_LEFT);

            if ($invoice->invoice_type === "service") {
                $label = !empty($invoice->notes)
                    ? preg_replace("/\s+/", " ", trim($invoice->notes))
                    : "خدمة";
                $line = "❌ {$formattedMonth} / {$yearNum}      \${$amount}  🔧 {$label}";
                $serviceLines[] = $line;
            } else {
                $line = "❌ {$formattedMonth} / {$yearNum}      \${$amount}";
                if (!empty($invoice->notes)) {
                    $note = preg_replace("/\s+/", " ", trim($invoice->notes));
                    $line .= "  ({$note})";
                }
                $subscriptionLines[] = $line;
            }
        }

        $sections = [];
        if (!empty($subscriptionLines)) {
            $sections[] = "🌐 فواتير الاشتراك:\n" . implode("\n", $subscriptionLines);
        }
        if (!empty($serviceLines)) {
            $sections[] = "🔧 فواتير الخدمات:\n" . implode("\n", $serviceLines);
        }

        return implode("\n\n", $sections);
    }

    /**
     * Build the final message by replacing placeholders.
     */
    public static function buildMessage(string $template, string $clientName, float $totalAmount, string $invoiceDetailsList): string
    {
        $message = str_replace("{name}", $clientName, $template);
        $message = str_replace("{total_amount}", WhatsAppMessageBuilder::formatAmount($totalAmount), $message);
        $message = str_replace("{invoice_details_list}", $invoiceDetailsList, $message);
        return $message;
    }

    /**
     * Format a money amount without unnecessary decimals.
     * 25.00 -> 25, 10.50 -> 10.50, 105.00 -> 105.
     */
    public static function formatAmount(mixed $value): string
    {
        $amount = (float) $value;
        // If it's a whole number, drop the decimals; otherwise keep 2.
        return (string) (abs($amount - round($amount)) < 0.0000001
            ? number_format($amount, 0)
            : number_format($amount, 2));
    }

    /**
     * Default reminder template with MegaNet branding.
     */
    public static function defaultTemplate(): string
    {
        return "🌐 MegaNet\n\n👤 اسم المشترك: {name}\n\n📋 لديك فواتير مستحقة بإجمالي \${total_amount}.\n\n📄 تفاصيل الفواتير المستحقة:\n{invoice_details_list}\n\n━━━━━━━━━━━━━━━━━━\n\n⚠️ يرجى التكرم بتسديد الرصيد المستحق في أقرب وقت ممكن.\nإذا كنت قد سددت هذا المبلغ مؤخراً، يرجى تجاهل هذه الرسالة.\n\nشكراً لاختياركم MegaNet 🌹";
    }
}
