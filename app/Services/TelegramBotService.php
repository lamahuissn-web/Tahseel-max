<?php

namespace App\Services;

use App\Models\Clients;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected TelegramApiClient $api;

    public function __construct(?TelegramApiClient $api = null)
    {
        $this->api = $api ?? app(TelegramApiClient::class);
    }

    public function handleMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (!$chatId || !$text) return;

        $name = $this->extractMentionedClientName($text);
        $isMentionSearch = $name !== null;
        $isCommandSearch = preg_match('/^\/(?:client|عميل)(?:@[^\s]+)?(?:\s+(.+))?$/u', $text, $matches) === 1;

        if ($isMentionSearch || $isCommandSearch) {
            if (!TelegramConfig::isAllowedChatId($chatId)) {
                $this->sendUnauthorized($chatId);
                return;
            }

            $searchName = $isMentionSearch ? trim($name) : trim($matches[1] ?? '');
            $this->sendClientSearchResult($chatId, $searchName);
        } elseif (in_array($text, ['/start', '/help'])) {
            $this->sendHelpMessage($chatId);
        }
    }

    public function handleInlineQuery(array $inlineQuery)
    {
        $queryId = $inlineQuery['id'] ?? null;
        $query = trim($inlineQuery['query'] ?? '');
        $fromId = $inlineQuery['from']['id'] ?? null;

        if (!$queryId || strlen($query) < 1) return;

        if (!TelegramConfig::isAllowedChatId($fromId)) {
            $this->api->answerInlineQuery($queryId, []);
            return;
        }

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

        $this->api->answerInlineQuery($queryId, $results);
    }

    private function sendClientSearchResult($chatId, $name)
    {
        if (empty($name)) {
            $this->api->sendMessage($chatId, '❌ الرجاء إدخال اسم العميل. مثال: /client محمد');
            return;
        }

        $clients = Clients::where('name', 'like', '%' . $name . '%')
            ->whereNull('deleted_at')
            ->get();

        if ($clients->isEmpty()) {
            $this->api->sendMessage($chatId, "❌ لم يتم العثور على عميل بالاسم: {$this->escape($name)}");
            return;
        }

        if ($clients->count() > 5) {
            $this->api->sendMessage($chatId, "✅ تم العثور على {$clients->count()} عميل. استخدم البحث المضمن لمشاهدة المزيد.");
            return;
        }

        foreach ($clients as $client) {
            $this->api->sendMessage($chatId, $this->formatClientDetail($client));
        }
    }

    private function formatClientDetail($client)
    {
        $invoices = $client->invoices()
            ->orderByRaw("CASE status WHEN 'unpaid' THEN 1 WHEN 'partial' THEN 2 WHEN 'paid' THEN 3 ELSE 4 END")
            ->orderBy('due_date', 'desc')
            ->get();

        $currency = $this->getCurrency();
        $counts = [
            'paid' => 0,
            'partial' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'upcoming' => 0,
        ];

        $text = "👤 العميل: {$this->escape($client->name)}\n";
        $text .= "🆔 الرقم: {$this->escape($client->id)}\n";
        $text .= "📱 الهاتف: {$this->escape($client->phone)}\n";
        $text .= "💳 الاشتراك: {$this->escape($client->subscription->name ?? 'بدون اشتراك')}\n";
        $text .= "💰 السعر: " . number_format((float) $client->price, 2) . " {$this->escape($currency)}\n";
        $text .= "📍 العنوان: {$this->escape($client->address1)}\n";

        foreach ($invoices as $invoice) {
            $status = $invoice->status;
            $isOverdue = $status === 'unpaid' && $invoice->due_date && $invoice->due_date < now();
            if ($status === 'paid') $counts['paid']++;
            elseif ($status === 'partial') $counts['partial']++;
            elseif ($status === 'unpaid') {
                $counts['unpaid']++;
                $isOverdue ? $counts['overdue']++ : $counts['upcoming']++;
            }
        }

        $text .= "\n📊 حالة الفواتير:\n";
        $text .= "✅ مدفوعة: {$counts['paid']} | 🟠 جزئية: {$counts['partial']}\n";
        $text .= "🔴 متأخرة: {$counts['overdue']} | 🟡 غير مدفوعة/قادمة: {$counts['upcoming']}\n";

        if ($invoices->isNotEmpty()) {
            $text .= "\n📄 تفاصيل الفواتير:\n";
            foreach ($invoices as $invoice) {
                $text .= $this->formatInvoiceStatus($invoice, $currency);
            }
        }

        return $text;
    }

    private function extractMentionedClientName(string $text): ?string
    {
        $username = preg_quote('@' . TelegramConfig::botUsername(), '/');
        if (!preg_match('/' . $username . '(?:\s+(.+))?$/u', $text, $matches)) {
            return null;
        }

        return trim($matches[1] ?? '');
    }

    private function formatInvoiceStatus($invoice, string $currency): string
    {
        $status = $invoice->status;
        $isOverdue = $status === 'unpaid' && $invoice->due_date && $invoice->due_date < now();
        $label = match (true) {
            $status === 'paid' => '✅ مدفوعة',
            $status === 'partial' => '🟠 مدفوعة جزئياً',
            $isOverdue => '🔴 متأخرة',
            default => '🟡 غير مدفوعة',
        };
        $remaining = $invoice->remaining_amount;
        $amount = number_format((float) $invoice->amount, 2);
        $remainingText = $remaining !== null ? ' | المتبقي: ' . number_format((float) $remaining, 2) : '';
        $dueDate = $invoice->due_date ? $this->escape($invoice->due_date) : 'بدون تاريخ';

        return "▪ INV-{$this->escape($invoice->invoice_number)} — {$label} — {$amount} {$this->escape($currency)}{$remainingText} — 📅 {$dueDate}\n";
    }

    private function sendHelpMessage($chatId)
    {
        $botUsername = TelegramConfig::botUsername();

        $text = "🤖 مرحباً بك في بوت Tahseel!\n\n";
        $text .= "الأوامر المتاحة:\n";
        $text .= "/client <اسم> — البحث عن عميل\n";
        $text .= "/عميل <اسم> — البحث عن عميل (بالعربية)\n";
        $text .= "/start — عرض هذه الرسالة\n\n";
        $text .= "💡 يمكنك أيضاً استخدام البحث المضمن:\n";
        $text .= "اكتب @{$botUsername} <اسم> في أي محادثة";

        $this->api->sendMessage($chatId, $text);
    }

    private function sendUnauthorized($chatId): void
    {
        $this->api->sendMessage($chatId, '⛔ غير مصرح لك باستخدام بحث العملاء.');
    }

    private function escape($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function getCurrency()
    {
        return \App\Models\AppConfig::where('key', 'currency')->value('value') ?? 'ج.م';
    }
}
