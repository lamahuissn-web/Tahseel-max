<?php

namespace App\Services;

use App\Models\AppConfig;
use App\Models\Clients;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function handleMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (!$chatId || !$text) return;

        if (str_starts_with($text, '/client') || str_starts_with($text, '/عميل')) {
            $name = trim(substr($text, strpos($text, ' ') ?: strlen($text)));
            $this->sendClientSearchResult($chatId, $name);
        } elseif (in_array($text, ['/start', '/help'])) {
            $this->sendHelpMessage($chatId);
        }
    }

    public function handleInlineQuery(array $inlineQuery)
    {
        $queryId = $inlineQuery['id'] ?? null;
        $query = trim($inlineQuery['query'] ?? '');

        if (!$queryId || strlen($query) < 1) return;

        $clients = Clients::where('name', 'like', '%' . $query . '%')
            ->whereNull('deleted_at')
            ->limit(50)
            ->get();

        $results = [];
        foreach ($clients as $client) {
            $overdueCount = $client->invoices()->where('status', 'unpaid')
                ->where('due_date', '<', now())
                ->count();

            $description = ($client->subscription->name ?? 'بدون اشتراك')
                . ' | ' . number_format($client->price, 2) . ' ' . ($this->getCurrency())
                . ($overdueCount > 0 ? " | ⏰ {$overdueCount} متأخرة" : '');

            $results[] = [
                'type' => 'article',
                'id' => (string) $client->id,
                'title' => $client->name,
                'description' => $description,
                'input_message_content' => [
                    'message_text' => $this->formatClientDetail($client),
                    'parse_mode' => 'HTML',
                ],
            ];
        }

        sendTelegramAnswerInlineQuery($queryId, $results);
    }

    private function sendClientSearchResult($chatId, $name)
    {
        if (empty($name)) {
            $this->sendMessage($chatId, '❌ الرجاء إدخال اسم العميل. مثال: /client محمد');
            return;
        }

        $clients = Clients::where('name', 'like', '%' . $name . '%')
            ->whereNull('deleted_at')
            ->get();

        if ($clients->isEmpty()) {
            $this->sendMessage($chatId, "❌ لم يتم العثور على عميل بالاسم: {$name}");
            return;
        }

        if ($clients->count() > 5) {
            $this->sendMessage($chatId, "✅ تم العثور على {$clients->count()} عميل. استخدم البحث المضمن لمشاهدة المزيد.");
            return;
        }

        foreach ($clients as $client) {
            $this->sendMessage($chatId, $this->formatClientDetail($client));
        }
    }

    private function formatClientDetail($client)
    {
        $overdueInvoices = $client->invoices()
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'desc')
            ->get();

        $upcomingInvoices = $client->invoices()
            ->where('status', 'unpaid')
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->get();

        $currency = $this->getCurrency();

        $text = "👤 العميل: {$client->name}\n";
        $text .= "🆔 الرقم: {$client->id}\n";
        $text .= "📱 الهاتف: {$client->phone}\n";
        $text .= "💳 الاشتراك: {$client->subscription->name}\n";
        $text .= "💰 السعر: " . number_format($client->price, 2) . " {$currency}\n";
        $text .= "📍 العنوان: {$client->address1}\n";

        if ($overdueInvoices->isNotEmpty()) {
            $text .= "\n📄 الفواتير المتأخرة ({$overdueInvoices->count()}):\n";
            foreach ($overdueInvoices as $inv) {
                $text .= "▪ INV-{$inv->invoice_number} — " . number_format($inv->amount, 2) . " {$currency} — 📅 {$inv->due_date}\n";
            }
        }

        if ($upcomingInvoices->isNotEmpty()) {
            $text .= "\n📅 الفواتير القادمة ({$upcomingInvoices->count()}):\n";
            foreach ($upcomingInvoices as $inv) {
                $text .= "▪ INV-{$inv->invoice_number} — " . number_format($inv->amount, 2) . " {$currency} — 📅 {$inv->due_date}\n";
            }
        }

        return $text;
    }

    private function sendHelpMessage($chatId)
    {
        $text = "🤖 مرحباً بك في بوت Tahseel!\n\n";
        $text .= "الأوامر المتاحة:\n";
        $text .= "/client <اسم> — البحث عن عميل\n";
        $text .= "/عميل <اسم> — البحث عن عميل (بالعربية)\n";
        $text .= "/start — عرض هذه الرسالة\n\n";
        $text .= "💡 يمكنك أيضاً استخدام البحث المضمن:\n";
        $text .= "اكتب @mikr313bot <اسم> في أي محادثة";

        $this->sendMessage($chatId, $text);
    }

    private function sendMessage($chatId, $text)
    {
        $token = AppConfig::where('key', 'telegram_bot_token')->value('value');
        if (!$token) return;

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
    }

    private function getCurrency()
    {
        return AppConfig::where('key', 'currency')->value('value') ?? 'ج.م';
    }
}
