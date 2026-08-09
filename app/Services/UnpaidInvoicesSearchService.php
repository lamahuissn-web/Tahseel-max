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
 *  - nullable plain-text notes PREVIEW: blank→null, surrounding whitespace
 *    trimmed, truncated Unicode-safely to max 1000 chars (mb_substr) to stay
 *    within the strict mobile client cap; full note only on details endpoint
 *  - strict money strings with exactly two decimals
 *  - bounded queries: one LEFT JOIN, one count + one page query, no N+1
 */
class UnpaidInvoicesSearchService
{
    /**
     * @param  string  $search  trimmed search term; '' means no search
     * @param  string  $clientType  trimmed exact client category; '' means all
     * @return array{invoices: array<int, array<string, mixed>>, currency: string, pagination: array<string, mixed>}
     */
    public function search(string $search, int $page, int $perPage, string $clientType = ''): array
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
                'i.notes',
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

        // Feature 008: exact client category filter, applied BEFORE
        // order/pagination/count and composed with the financial scope and
        // the grouped search above (AND semantics; never widens scope).
        if ($clientType !== '') {
            $query->where('c.client_type', $clientType);
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
                // Feature 008: minimal nullable notes PREVIEW sourced ONLY
                // from the invoice's own notes column. Blank normalizes to
                // null; surrounding whitespace is trimmed; the text is
                // truncated Unicode-safely to at most 1000 characters so a
                // long stored note can never exceed the strict mobile client
                // cap and fail the whole page. No HTML transformation, no
                // extra PII. The full note stays on the invoice-details
                // endpoint (unchanged).
                'notes' => $this->notesPreview($row->notes),
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

    /**
     * Nullable note PREVIEW normalization for list rows:
     *  - null and blank/whitespace-only become null
     *  - surrounding whitespace is trimmed (internal content preserved)
     *  - Unicode-safe truncation to at most 1000 characters (mb_substr), the
     *    same bound as the strict mobile parser, so an over-long stored note
     *    can never make the whole page fail to parse
     * The full stored note is unchanged in the database and remains available
     * on the invoice-details endpoint.
     */
    private function notesPreview(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        return mb_substr($trimmed, 0, 1000);
    }
}
