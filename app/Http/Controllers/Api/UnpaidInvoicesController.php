<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UnpaidInvoicesSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

/**
 * Feature 005 — GET /api/v1/unpaid-invoices.
 *
 * Dedicated minimal contract (never reuses the unbounded legacy
 * InvoicesController::unpaidInvoices response):
 *   { "status": true, "data": { "invoices": [...], "currency": "...", "pagination": {...} } }
 * JWT-only; active (status '1') authenticated admins of any role may read.
 * Inactive accounts are denied with a stable 403 account_inactive BEFORE any
 * service query, so no list data is ever built or leaked for them. Payment
 * authorization is untouched (secure payment endpoints keep their own flow).
 */
class UnpaidInvoicesController extends Controller
{
    public function __construct(private readonly UnpaidInvoicesSearchService $service)
    {
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'معاملات البحث غير صحيحة',
                'data' => (object) [],
                'error' => ['code' => 'invalid_query_parameters'],
            ], 422);
        }

        // Active-only gate (Admin status semantics: '1' = active). Must run
        // before the service query so an inactive account never triggers a
        // list query or receives any invoice data.
        $user = auth('api')->user();
        if ($user === null || (string) $user->status !== '1') {
            return response()->json([
                'result' => false,
                'message' => 'هذا الحساب غير نشط',
                'data' => (object) [],
                'error' => ['code' => 'account_inactive'],
            ], 403);
        }

        // Empty/whitespace search behaves as no search.
        $search = trim((string) $request->input('search', ''));
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 25);

        try {
            $data = $this->service->search($search, $page, $perPage);

            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (Throwable $exception) {
            $correlationId = (string) Str::uuid();

            Log::error('Unpaid invoices search failed', [
                'correlation_id' => $correlationId,
                'user_id' => auth('api')->id(),
                'exception' => get_class($exception),
            ]);

            return response()->json([
                'result' => false,
                'message' => 'تعذر استرجاع الفواتير غير المدفوعة حالياً',
                'data' => (object) [],
                'error' => [
                    'code' => 'unpaid_invoices_internal_error',
                    'correlation_id' => $correlationId,
                ],
            ], 500);
        }
    }
}
