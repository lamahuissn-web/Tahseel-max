<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CollectorBalancesService;
use App\Traits\SuperAdminGuard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CollectorBalancesController extends Controller
{
    use SuperAdminGuard;

    public function __construct(private readonly CollectorBalancesService $service)
    {
    }

    public function index()
    {
        // Server-side authorization before any query.
        if (! $this->isSuperAdmin()) {
            return $this->superAdminForbidden('collector_balances_forbidden');
        }

        try {
            $data = $this->service->snapshot();

            return response()->json([
                'result' => true,
                'message' => 'تم استرجاع أرصدة المحصلين بنجاح',
                'data' => $data,
            ]);
        } catch (Throwable $exception) {
            $correlationId = (string) Str::uuid();

            Log::error('Collector balances snapshot failed', [
                'correlation_id' => $correlationId,
                'user_id' => auth('api')->id(),
                'exception' => get_class($exception),
            ]);

            return response()->json([
                'result' => false,
                'message' => 'تعذر استرجاع أرصدة المحصلين حالياً',
                'data' => (object) [],
                'error' => [
                    'code' => 'collector_balances_internal_error',
                    'correlation_id' => $correlationId,
                ],
            ], 500);
        }
    }
}
