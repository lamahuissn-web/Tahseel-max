<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\Invoice;
use App\Models\Admin\Revenue;
use App\Traits\ResponseApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollectionsController extends Controller
{
    use ResponseApi;

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->responseApiError($validator->errors()->first());
        }

        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->responseApiError('المستخدم غير مصرح له.');
            }

            $startDate = $request->filled('start_date')
                ? Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay()
                : now()->startOfDay();
            $endDate = $request->filled('end_date')
                ? Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->endOfDay()
                : now()->endOfDay();
            $perPage = max(1, min((int) $request->input('per_page', 50), 100));

            $baseQuery = Revenue::query()
                ->where('collected_by', $user->id)
                ->whereNotNull('invoice_id')
                ->whereBetween('received_at', [$startDate, $endDate])
                ->whereNull('deleted_at');

            $summaryCount = (clone $baseQuery)->count();
            $summaryTotal = (clone $baseQuery)->sum('amount');

            $collections = (clone $baseQuery)
                ->with(['invoice.client', 'client', 'user'])
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->paginate($perPage);

            // One currency lookup per request, shared by every item and the
            // summary — never per row.
            $currency = get_app_config_data('currency');

            $items = collect($collections->items())->map(function (Revenue $revenue) use ($currency) {
                $invoice = $revenue->invoice;
                $client = $revenue->client ?? $invoice?->client;
                $prefix = $client?->client_type === 'satellite' ? 'SA-' : 'IN-';

                return [
                    'id' => $revenue->id,
                    'reference' => 'COL-' . $revenue->id,
                    'invoice_id' => $revenue->invoice_id,
                    'invoice_number' => $invoice ? $prefix . $invoice->invoice_number : null,
                    'client_id' => $revenue->client_id,
                    'client_name' => $client?->name,
                    'amount' => $revenue->amount,
                    'remaining_amount' => $revenue->remaining_amount,
                    'received_at' => $revenue->received_at
                        ? Carbon::parse($revenue->received_at)->format('Y-m-d H:i:s')
                        : null,
                    'collected_by' => $revenue->user?->name,
                    'notes' => $revenue->notes,
                    'currency' => $currency,
                    // Feature 006: date-only due date sourced ONLY from the
                    // already eager-loaded invoice relation (no extra query).
                    // Missing invoice or missing/unparseable due date maps to
                    // null — never an invented date.
                    'due_date' => $this->dueDateOnly($invoice),
                ];
            })->values();

            return $this->responseApi([
                'collections' => $items,
                'summary' => [
                    'count' => $summaryCount,
                    'total_amount' => $summaryTotal,
                    'currency' => $currency,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'pagination' => [
                    'current_page' => $collections->currentPage(),
                    'last_page' => $collections->lastPage(),
                    'per_page' => $collections->perPage(),
                    'total' => $collections->total(),
                    'has_more' => $collections->hasMorePages(),
                ],
            ], 'تم استرجاع عمليات القبض بنجاح');
        } catch (\Throwable $e) {
            return $this->responseApiError('حدث خطأ أثناء استرجاع عمليات القبض.');
        }
    }

    /**
     * Feature 006: strict date-only (YYYY-MM-DD) representation of the
     * invoice's due date. Works exclusively on the already eager-loaded
     * invoice — no query is issued here. Missing invoice, missing due date,
     * or an unparseable/zero value fails safely to null.
     */
    private function dueDateOnly(?Invoice $invoice): ?string
    {
        $dueDate = $invoice?->due_date;

        if (! $dueDate) {
            return null;
        }

        try {
            return Carbon::parse($dueDate)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
