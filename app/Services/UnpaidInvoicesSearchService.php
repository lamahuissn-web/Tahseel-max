<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Read-only, paginated search over non-deleted unpaid/partial invoices.
 *
 * Feature 005 contract guarantees:
 *  - server-authoritative scope: status IN (unpaid, partial) AND deleted_at IS NULL
 *    AND remaining_amount > 0 (zero/negative stale rows carry no money owed and
 *    are excluded, matching the strict mobile parser)
 *  - grouped search limited to invoice_number OR client name (never phone,
 *    address, notes, collector or account data)
 *  - stable order: unpaid first, then due_date ASC, then id ASC
 *  - dedicated minimal rows (no PII beyond client name)
 *  - strict money strings with exactly two decimals
 *  - bounded queries: one LEFT JOIN, one count + one page query, no N+1
 */
class UnpaidInvoicesSearchService
{
    /**
     * @return array{invoices: array<int, array<string, mixed>>, currency: string, pagination: array<string, mixed>}
     */
    public function search(string $search, int $page, int $perPage): array
    {
        $query = DB::table('tbl_invoices as i')
            ->leftJoin('tbl_clients as c', 'c.id', '=', 'i.client_id')
            ->select([
                'i.id',
                'i.invoice_number',
                'i.client_id',
                DB::raw('COALESCE(c.name, \'\') as client_name'),
                'i.invoice_type',
                'i.status',
                'i.amount',
                'i.remaining_amount',
                'i.due_date',
            ])
            ->whereNull('i.deleted_at')
            ->whereIn('i.status', ['unpaid', 'partial'])
            ->where('i.remaining_amount', '>', 0);

        if ($search !== '') {
            $like = '%'.$search.'%';
            // The OR group lives INSIDE the status/deleted filters, so a
            // search term can never widen the financial scope.
            $query->where(function ($q) use ($like) {
                $q->where('i.invoice_number', 'like', $like)
                    ->orWhere('c.name', 'like', $like);
            });
        }

        $query->orderByRaw("CASE i.status WHEN 'unpaid' THEN 0 ELSE 1 END")
            ->orderBy('i.due_date', 'asc')
            ->orderBy('i.id', 'asc');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $currency = get_app_config_data('currency');
        if (! is_string($currency) || trim($currency) === '') {
            throw new \RuntimeException('Currency configuration is missing');
        }

        $invoices = array_map(
            fn ($row): array => [
                'id' => (int) $row->id,
                'invoice_number' => (string) $row->invoice_number,
                'client_id' => (int) $row->client_id,
                'client_name' => (string) $row->client_name,
                'invoice_type' => (string) $row->invoice_type,
                'status' => (string) $row->status,
                'amount' => $this->money($row->amount),
                'remaining_amount' => $this->money($row->remaining_amount),
                'due_date' => $row->due_date,
            ],
            $paginator->items(),
        );

        return [
            'invoices' => array_values($invoices),
            'currency' => $currency,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->currentPage() < $paginator->lastPage(),
            ],
        ];
    }

    /**
     * DECIMAL(10,2) values always serialize as exactly two decimals with no
     * thousands separator (matches the contract pattern ^-?\d{1,8}\.\d{2}$).
     */
    private function money(string|int|float|null $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
