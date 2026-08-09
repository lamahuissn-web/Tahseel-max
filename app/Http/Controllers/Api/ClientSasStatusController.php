<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use App\Services\Sas4\ClientSasStatusService;
use App\Traits\ResponseApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientSasStatusController extends Controller
{
    use ResponseApi;

    public function __construct(private ClientSasStatusService $statusService)
    {
    }

    /**
     * Feature 009 — POST /api/v1/clients/sas-status
     *
     * Resolves SAS connection status for visible active clients.
     *
     * Request: {"client_ids": [1, 2, 3]} — required array of 1..100 unique
     * positive integers. Extra top-level keys and malformed/non-integer
     * entries are rejected safely with 422. IDs resolve inside the exact
     * same active + non-deleted visibility scope as GET /api/v1/clients;
     * unknown/out-of-scope IDs are omitted without disclosure. Only
     * client_id / sas_username / status are ever returned.
     */
    public function check(Request $request)
    {
        $input = $request->all();
        $ids = $input['client_ids'] ?? [];

        $validator = Validator::make($input, [
            'client_ids' => ['required', 'array', 'min:1', 'max:100'],
            'client_ids.*' => ['integer', 'min:1', 'distinct'],
        ]);

        // Strict contract enforcement on top of the validator: list-shaped
        // array of strictly-typed positive integers, no duplicates, no extra
        // top-level keys. Anything else is a safe 422.
        $strict = is_array($ids)
            && array_is_list($ids)
            && $ids !== []
            && count($ids) <= 100
            && count($ids) === count(array_unique($ids))
            && collect($ids)->every(fn ($id) => is_int($id) && $id >= 1)
            && array_diff(array_keys($input), ['client_ids']) === [];

        if ($validator->fails() || ! $strict) {
            return response()->json([
                'result' => false,
                'message' => 'بيانات الطلب غير صحيحة',
                'data' => (object) [],
            ], 422);
        }

        $ids = array_values(array_unique($ids));

        // Same visibility scope as the clients list: active + non-deleted.
        $clients = Clients::query()
            ->whereIn('tbl_clients.id', $ids)
            ->where('tbl_clients.is_active', 1)
            ->whereNull('tbl_clients.deleted_at')
            ->get(['tbl_clients.id', 'tbl_clients.sas_username']);

        $byId = $this->statusService->resolve($clients);

        // Omit unknown/out-of-scope IDs entirely (no disclosure) and keep the
        // requested order for deterministic responses.
        $statuses = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $statuses[] = $byId[$id];
            }
        }

        return $this->responseApi(['statuses' => $statuses], 'تم جلب حالة اتصال SAS');
    }
}
