<?php

namespace App\Services\WhatsApp;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CollectorReminderService
{
    public static function normalizeRules(array $rules): array
    {
        $normalized = [];

        foreach ($rules as $rule) {
            $adminId = $rule['admin_id'] ?? $rule['collector_user_id'] ?? null;
            $name = trim((string) ($rule['name'] ?? ''));
            $phone = trim((string) ($rule['phone'] ?? ''));
            $markers = $rule['markers'] ?? [];

            if (is_string($markers)) {
                $markers = self::parseMarkerString($markers);
            }

            $markers = collect($markers)
                ->map(fn ($marker) => trim((string) $marker))
                ->filter()
                ->unique(fn ($marker) => mb_strtolower($marker))
                ->values()
                ->all();

            if ($name === '' && $phone === '' && empty($markers)) {
                continue;
            }

            $normalized[] = [
                'admin_id' => is_numeric($adminId) ? (int) $adminId : null,
                'name' => $name,
                'phone' => $phone,
                'markers' => $markers,
                'active' => filter_var($rule['active'] ?? true, FILTER_VALIDATE_BOOL),
            ];
        }

        return $normalized;
    }

    public static function buildPreview(array $rules): array
    {
        $rules = self::normalizeRules($rules);
        $activeRules = collect($rules)->filter(fn ($rule) => ($rule['active'] ?? false) && !empty($rule['markers']))->values();

        $groups = [];
        foreach ($activeRules as $index => $rule) {
            $groups[$index] = [
                'rule_index' => $index,
                'admin_id' => $rule['admin_id'] ?? null,
                'name' => $rule['name'],
                'phone' => $rule['phone'],
                'markers' => $rule['markers'],
                'customers' => [],
                'customer_count' => 0,
                'invoice_count' => 0,
                'total_amount' => 0.0,
            ];
        }

        $unmatched = [];
        $conflicts = [];

        $dueCustomers = self::dueCustomerRows();

        foreach ($dueCustomers as $client) {
            $matches = self::matchingRules($client->name, $activeRules);
            $customerData = self::customerDataFromAggregate($client);

            if ($matches->isEmpty()) {
                $unmatched[] = $customerData;
                continue;
            }

            if ($matches->count() > 1) {
                $customerData['matched_collectors'] = $matches->pluck('name')->values()->all();
                $conflicts[] = $customerData;
                continue;
            }

            $match = $matches->first();
            $ruleIndex = $match['rule_index'];
            $groups[$ruleIndex]['customers'][] = $customerData;
            $groups[$ruleIndex]['customer_count']++;
            $groups[$ruleIndex]['invoice_count'] += $customerData['invoice_count'];
            $groups[$ruleIndex]['total_amount'] += $customerData['total_amount'];
        }

        $groups = collect($groups)->values()->all();

        return [
            'rules' => $rules,
            'groups' => $groups,
            'unmatched' => $unmatched,
            'conflicts' => $conflicts,
            'summary' => [
                'collectors' => count($groups),
                'collectors_with_customers' => collect($groups)->where('customer_count', '>', 0)->count(),
                'customers' => collect($groups)->sum('customer_count'),
                'invoices' => collect($groups)->sum('invoice_count'),
                'total_amount' => round((float) collect($groups)->sum('total_amount'), 2),
                'unmatched' => count($unmatched),
                'conflicts' => count($conflicts),
            ],
        ];
    }

    public static function buildMessage(array $group): string
    {
        return self::buildMessages($group)[0] ?? '';
    }

    public static function buildMessages(array $group, int $maxCustomersPerMessage = 15): array
    {
        $customers = array_values($group['customers'] ?? []);
        if (empty($customers)) {
            return [];
        }

        $chunks = array_chunk($customers, max(1, $maxCustomersPerMessage));
        $totalParts = count($chunks);
        $messages = [];

        foreach ($chunks as $chunkIndex => $chunkCustomers) {
            $chunkGroup = $group;
            $chunkGroup['customers'] = $chunkCustomers;
            $chunkGroup['chunk_customer_count'] = count($chunkCustomers);
            $chunkGroup['chunk_total_amount'] = collect($chunkCustomers)->sum('total_amount');
            $chunkGroup['part_number'] = $chunkIndex + 1;
            $chunkGroup['total_parts'] = $totalParts;
            $chunkGroup['number_offset'] = $chunkIndex * max(1, $maxCustomersPerMessage);

            $messages[] = self::buildMessageChunk($chunkGroup);
        }

        return $messages;
    }

    private static function buildMessageChunk(array $group): string
    {
        $collectorName = $group['name'] ?: 'Collector';
        $customerCount = (int) ($group['customer_count'] ?? count($group['customers'] ?? []));
        $chunkCustomerCount = (int) ($group['chunk_customer_count'] ?? count($group['customers'] ?? []));
        $total = number_format((float) ($group['total_amount'] ?? 0), 2);
        $chunkTotal = number_format((float) ($group['chunk_total_amount'] ?? $group['total_amount'] ?? 0), 2);
        $partNumber = (int) ($group['part_number'] ?? 1);
        $totalParts = (int) ($group['total_parts'] ?? 1);
        $numberOffset = (int) ($group['number_offset'] ?? 0);

        $lines = [];
        $lines[] = "صباح الخير {$collectorName}";
        $lines[] = '';
        $lines[] = "📋 لديك اليوم تحصيل من {$customerCount} زبائن";
        $lines[] = "💰 الإجمالي المتوقع: \${$total}";

        if ($totalParts > 1) {
            $lines[] = "📦 الرسالة {$partNumber}/{$totalParts} — هذه الدفعة: {$chunkCustomerCount} زبائن / \${$chunkTotal}";
        }

        $lines[] = '';

        foreach (($group['customers'] ?? []) as $idx => $customer) {
            $number = $numberOffset + $idx + 1;
            $lines[] = "{$number}. {$customer['name']}";
            $lines[] = '   💵 $' . number_format((float) $customer['total_amount'], 2);
            $lines[] = '   📅 ' . ($customer['first_due_date_formatted'] ?? $customer['first_due_date'] ?? '-');
            if (!empty($customer['phone'])) {
                $lines[] = '   📞 ' . $customer['phone'];
            }
            if (($customer['oldest_overdue_days'] ?? 0) > 0) {
                $lines[] = '   ⚠️ متأخر منذ ' . $customer['oldest_overdue_days'] . ' يوم';
            }
            $lines[] = '';
        }

        $lines[] = 'يرجى المتابعة والتحصيل اليوم.';

        return trim(implode("\n", $lines));
    }

    private static function parseMarkerString(string $markers): array
    {
        $parts = preg_split('/[,،;؛\n]+/', $markers) ?: [];
        $parsed = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }

            preg_match_all('/(?<![\p{L}\p{N}.])([A-Z]{1,4}(?:\.[A-Z]{1,4}){0,4})(?![\p{L}\p{N}.])/iu', $part, $matches);

            if (count($matches[1] ?? []) > 1) {
                foreach ($matches[1] as $marker) {
                    $parsed[] = $marker;
                }
                continue;
            }

            $parsed[] = $part;
        }

        return $parsed;
    }

    private static function matchingRules(string $clientName, Collection $rules): Collection
    {
        return $rules->map(function ($rule, $index) use ($clientName) {
                $rule['rule_index'] = $index;
                return $rule;
            })
            ->filter(function ($rule) use ($clientName) {
                foreach (($rule['markers'] ?? []) as $marker) {
                    if (self::containsMarker($clientName, $marker)) {
                        return true;
                    }
                }
                return false;
            })
            ->values();
    }

    private static function containsMarker(string $clientName, string $marker): bool
    {
        $marker = trim($marker);
        if ($marker === '') {
            return false;
        }

        $escaped = preg_quote($marker, '/');

        return preg_match('/(^|[^\p{L}\p{N}.])' . $escaped . '($|[^\p{L}\p{N}.])/iu', $clientName) === 1;
    }

    private static function dueCustomerRows(): Collection
    {
        return DB::table('tbl_clients as c')
            ->join('tbl_invoices as i', 'i.client_id', '=', 'c.id')
            ->whereNull('c.deleted_at')
            ->whereNull('i.deleted_at')
            ->whereNotNull('c.name')
            ->whereIn('i.status', ['unpaid', 'partial'])
            ->where('i.due_date', '<=', Carbon::today())
            ->groupBy('c.id', 'c.name', 'c.phone')
            ->orderBy('c.name')
            ->get([
                'c.id',
                'c.name',
                'c.phone',
                DB::raw('COUNT(i.id) as invoice_count'),
                DB::raw('COALESCE(SUM(i.remaining_amount), 0) as total_amount'),
                DB::raw('MIN(i.due_date) as first_due_date'),
            ]);
    }

    private static function customerDataFromAggregate(object $client): array
    {
        $firstDueDate = $client->first_due_date ?? null;
        $oldestDate = $firstDueDate ? Carbon::parse($firstDueDate) : null;

        return [
            'id' => $client->id,
            'name' => $client->name,
            'phone' => $client->phone,
            'invoice_count' => (int) $client->invoice_count,
            'total_amount' => round((float) $client->total_amount, 2),
            'first_due_date' => $firstDueDate,
            'first_due_date_formatted' => $firstDueDate ? Carbon::parse($firstDueDate)->format('d/m/Y') : '-',
            'oldest_overdue_days' => $oldestDate ? max(0, $oldestDate->diffInDays(Carbon::today(), false)) : 0,
        ];
    }

    private static function customerData(object $client, Collection $invoices): array
    {
        $firstDueDate = optional($invoices->first())->due_date;
        $oldestDate = $firstDueDate ? Carbon::parse($firstDueDate) : null;

        return [
            'id' => $client->id,
            'name' => $client->name,
            'phone' => $client->phone,
            'invoice_count' => $invoices->count(),
            'total_amount' => round((float) $invoices->sum('remaining_amount'), 2),
            'first_due_date' => $firstDueDate,
            'first_due_date_formatted' => $firstDueDate ? Carbon::parse($firstDueDate)->format('d/m/Y') : '-',
            'oldest_overdue_days' => $oldestDate ? max(0, $oldestDate->diffInDays(Carbon::today(), false)) : 0,
        ];
    }
}
