<?php

namespace App\Services\WhatsApp;

use App\Models\AppConfig;
use Illuminate\Support\Facades\DB;

class WhatsAppTemplateService
{
    /**
     * Available template types with their defaults.
     */
    public static function getDefaults(): array
    {
        return [
            'reminder' => [
                'label' => 'تذكير فاتورة',
                'label_en' => 'Invoice Reminder',
                'body' => "🌐 MegaNet\n\n👤 اسم المشترك: {name}\n\n📋 لديك فواتير مستحقة بإجمالي \${total_amount}.\n\n📄 تفاصيل الفواتير المستحقة:\n{invoice_details_list}\n\n━━━━━━━━━━━━━━━━━━\n\n⚠️ يرجى التكرم بتسديد الرصيد المستحق في أقرب وقت ممكن.\nإذا كنت قد سددت هذا المبلغ مؤخراً، يرجى تجاهل هذه الرسالة.\n\nشكراً لاختياركم MegaNet 🌹",
                'variables' => ['{name}', '{total_amount}', '{invoice_details_list}'],
            ],
            'receipt' => [
                'label' => 'إيصال دفع',
                'label_en' => 'Payment Receipt',
                'body' => "🌐 MegaNet\n\n🧾 إيصال اشتراك الإنترنت\n👤 اسم المشترك: {name}\n\n📅 الاشتراك المسدد: {month} / {year}\n💵 المبلغ المدفوع: \${amount}\n🧑 قبضت بواسطة: {collector}\n⏱ وقت الدفع: {datetime}\n\n━━━━━━━━━━━━━━━━━━\n📊 حالة الحساب: {balance_status}\n\nشكراً لاختياركم MegaNet 🌹",
                'variables' => ['{name}', '{amount}', '{month}', '{year}', '{collector}', '{datetime}', '{balance_status}'],
            ],
            'disconnection' => [
                'label' => 'إنذار قطع الخدمة',
                'label_en' => 'Disconnection Warning',
                'body' => "🌐 MegaNet\n\n👤 اسم المشترك: {name}\n\n⚠️ نود إعلامكم أنه لم يتم سداد فواتيركم المستحقة حتى تاريخه.\n\n📋 المبلغ المطلوب: \${total_amount}\n📅 تاريخ الاستحقاق: {due_date}\n\nيرجى التكرم بتسديد المبلغ في أقرب وقت ممكن لتجنب قطع الخدمة.\n\nللتواصل: {support_phone}\n\nشكراً لاختياركم MegaNet 🌹",
                'variables' => ['{name}', '{total_amount}', '{due_date}', '{support_phone}'],
            ],
            'custom' => [
                'label' => 'رسالة مخصصة',
                'label_en' => 'Custom Message',
                'body' => "🌐 MegaNet\n\n👤 عزيزي المشترك: {name}\n\n{message_body}\n\nشكراً لاختياركم MegaNet 🌹",
                'variables' => ['{name}', '{message_body}'],
            ],
        ];
    }

    /**
     * Get a template body by type.
     */
    public static function getBody(string $type): ?string
    {
        // Try DB first
        $dbKey = "whatsapp_template_{$type}";
        $body = DB::table('app_config')->where('key', $dbKey)->value('value');

        if ($body) {
            return $body;
        }

        // Fallback to defaults
        $defaults = self::getDefaults();
        return $defaults[$type]['body'] ?? null;
    }

    /**
     * Save a template body by type.
     */
    public static function saveBody(string $type, string $body, int $adminId = null): void
    {
        $dbKey = "whatsapp_template_{$type}";

        $existing = DB::table('app_config')->where('key', $dbKey)->first();

        if ($existing) {
            DB::table('app_config')->where('key', $dbKey)->update([
                'value' => $body,
                'updated_by' => $adminId,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('app_config')->insert([
                'key' => $dbKey,
                'value' => $body,
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Get all templates with their labels and bodies.
     */
    public static function getAll(): array
    {
        $defaults = self::getDefaults();
        $templates = [];

        foreach ($defaults as $type => $config) {
            $body = self::getBody($type);
            $templates[$type] = [
                'type' => $type,
                'label' => $config['label'],
                'label_en' => $config['label_en'],
                'body' => $body,
                'variables' => $config['variables'],
            ];
        }

        return $templates;
    }
}
