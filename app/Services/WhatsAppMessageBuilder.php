<?php

namespace App\Services;

use Carbon\Carbon;

class WhatsAppMessageBuilder
{
    /**
     * الأشهر العربية (بلدان الشام)
     */
    protected static array $arabicMonths = [
        1 => "كانون الثاني", 2 => "شباط", 3 => "آذار", 4 => "نيسان",
        5 => "أيار", 6 => "حزيران", 7 => "تموز", 8 => "آب",
        9 => "أيلول", 10 => "تشرين الأول", 11 => "تشرين الثاني", 12 => "كانون الأول",
    ];

    /**
     * بناء قائمة الفواتير المفصّلة
     * تقسم الفواتير إلى اشتراكات وخدمات - notes تظهر بين قوسين بنهاية السطر
     */
    public static function buildInvoiceDetailsList($clientInvoices): string
    {
        $subscriptionLines = [];
        $serviceLines = [];

        foreach ($clientInvoices as $invoice) {
            $amount = number_format($invoice->remaining_amount, 2);
            $monthNum = (int) Carbon::parse($invoice->due_date)->format("n");
            $yearNum = Carbon::parse($invoice->due_date)->format("Y");
            $monthName = self::$arabicMonths[$monthNum] ?? Carbon::parse($invoice->due_date)->format("F");

            if ($invoice->invoice_type === "service") {
                $label = !empty($invoice->notes)
                    ? preg_replace("/\s+/", " ", trim($invoice->notes))
                    : "خدمة";
                $line = "🔧 " . $label . " (" . str_pad($monthNum, 2, "0", STR_PAD_LEFT) . "-" . $yearNum . ") — " . $amount . "\$";
                $serviceLines[] = $line;
            } else {
                $base = "📅 " . $monthName . " (" . str_pad($monthNum, 2, "0", STR_PAD_LEFT) . "-" . $yearNum . ") — " . $amount . "\$";
                if (!empty($invoice->notes)) {
                    $note = preg_replace("/\s+/", " ", trim($invoice->notes));
                    $base .= " (" . $note . ")";
                }
                $subscriptionLines[] = $base;
            }
        }

        $sections = [];
        if (!empty($subscriptionLines)) {
            $sections[] = "🌐 فواتير الاشتراك:
" . implode("
", $subscriptionLines);
        }
        if (!empty($serviceLines)) {
            $sections[] = "🔧 فواتير الخدمات:
" . implode("
", $serviceLines);
        }

        return implode("

", $sections);
    }

    /**
     * بناء الرسالة النهائية
     */
    public static function buildMessage(string $template, string $clientName, float $totalAmount, string $invoiceDetailsList): string
    {
        $message = str_replace("{name}", $clientName, $template);
        $message = str_replace("{total_amount}", number_format($totalAmount, 2), $message);
        $message = str_replace("{invoice_details_list}", $invoiceDetailsList, $message);
        return $message;
    }

    /**
     * القالب الافتراضي
     */
    public static function defaultTemplate(): string
    {
        return "👋 مرحباً {name}،

📋 لديك فواتير مستحقة بإجمالي {total_amount}$.

📄 تفاصيل الفواتير المستحقة:
{invoice_details_list}

💳 يرجى التكرم بتسوية الرصيد المستحق في أقرب وقت ممكن.
إذا كنت قد سددت هذا المبلغ مؤخراً، يرجى تجاهل هذه الرسالة.

🙏 شكراً لتفهمك.";
    }
}
