<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\InvoiceResource;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\Invoice;
use App\Models\Clients;
use App\Traits\ResponseApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientsController extends Controller
{
    use ResponseApi;

    protected $ClientsRepository;
    protected $InvoiceRepository;

    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->ClientsRepository = createRepository($basicRepository, new Clients());
        $this->InvoiceRepository = createRepository($basicRepository, new Invoice());
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                // Feature 008: optional exact client category; absent/blank
                // means all. Arrays fail the string rule, unknown values fail
                // the in rule, overlong values fail the max rule — all 422.
                'client_type' => ['nullable', 'string', 'max:16', 'in:internet,satellite'],
            ]);
            if ($validator->fails()) {
                // Existing safe envelope (result/message/data), now with the
                // HTTP 422 status the Feature 008 contract requires.
                return response()->json([
                    'result' => false,
                    'message' => $validator->errors()->first(),
                    'data' => (object) [],
                ], 422);
            }

            $perPage = max(1, min((int) $request->input('per_page', 50), 100));
            $invoiceBalances = Invoice::query()
                ->selectRaw('client_id, SUM(COALESCE(remaining_amount, 0)) as invoices_sum_remaining_amount')
                ->whereNull('deleted_at')
                ->groupBy('client_id');

            $query = Clients::query()
                ->select('tbl_clients.*')
                ->selectRaw('COALESCE(invoice_balances.invoices_sum_remaining_amount, 0) as invoices_sum_remaining_amount')
                ->leftJoinSub($invoiceBalances, 'invoice_balances', function ($join) {
                    $join->on('invoice_balances.client_id', '=', 'tbl_clients.id');
                })
                ->with('subscription:id,name')
                ->where('tbl_clients.is_active', 1);
            $search = trim((string) $request->input('search', ''));
            if ($search !== '') {
                $searchTerm = "%{$search}%";


                $query->where(function ($q) use ($searchTerm) {
                    $q->where('tbl_clients.name', 'like', $searchTerm);
                    // Feature 009: search may also match the SAS username,
                    // composed inside the grouped scope (active/deleted,
                    // client_type, ordering, pagination) — never outside it.
                    $q->orWhere('tbl_clients.sas_username', 'like', $searchTerm);
                    // ->orWhere('email', 'like', $searchTerm)
                    // ->orWhere('phone', 'like', $searchTerm)
                    // ->orWhere('user', 'like', $searchTerm)
                    // ->orWhere('address1', 'like', $searchTerm)
                    // ->orWhere('box_switch', 'like', $searchTerm)
                    // ->orWhere('client_type', 'like', $searchTerm);
                    // ->orWhereHas('subscription', function ($subQuery) use ($searchTerm) {
                    //     $subQuery->where('name', 'like', $searchTerm);
                    // });
                });
            }

            // Feature 008: optional exact client category filter, applied
            // server-side BEFORE pagination/count/order and composed with the
            // grouped search plus the active/deleted scope above.
            $clientType = trim((string) $request->input('client_type', ''));
            if ($clientType !== '') {
                $query->where('tbl_clients.client_type', $clientType);
            }

            $clients = $query->whereNull('tbl_clients.deleted_at')
                ->orderBy('tbl_clients.created_at', 'desc')
                ->orderBy('tbl_clients.id', 'desc')
                ->paginate($perPage);
            $data = [
                'clients' => ClientResource::collection($clients->items()),
                'pagination' => [
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'per_page' => $clients->perPage(),
                    'total' => $clients->total(),
                    'has_more' => $clients->hasMorePages(),
                ],
            ];
            return $this->responseApi($data, 'تم استرجاع الزبائن بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    // public function clientInvoices($id)
    // {
    //     try {
    //         $client = $this->ClientsRepository->getById($id);

    //         if (!$client) {
    //             return $this->responseApiError('العميل غير موجود');
    //         }
    //         $oneYearAgo = Carbon::now()->subYear();

    //         $unpaidAndPartialInvoices = Invoice::with(['client', 'employee', 'subscription'])
    //             ->where('client_id', $id)
    //             ->whereIn('status', ['unpaid', 'partial'])
    //             ->where('created_at', '>=', $oneYearAgo)
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $paidInvoices = Invoice::with(['client', 'employee', 'subscription'])
    //             ->where('client_id', $id)
    //             ->whereIn('status', ['paid', 'partial'])
    //             ->where('created_at', '>=', $oneYearAgo)
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $data = [
    //             'client' => new ClientResource($client),
    //             'paid_invoices' => [
    //                 'count' => $paidInvoices->count(),
    //                 'total_paid_amount' => $paidInvoices->sum('amount'),
    //                 'invoices' => InvoiceResource::collection($paidInvoices)
    //             ],
    //             'unpaid_and_partial_invoices' => [
    //                 'count' => $unpaidAndPartialInvoices->count(),
    //                 'total_unpaidAndPartial_amount' => $unpaidAndPartialInvoices->sum('remaining_amount'),
    //                 'invoices' => InvoiceResource::collection($unpaidAndPartialInvoices)
    //             ],
    //         ];

    //         return $this->responseApi($data, 'تم استرجاع فواتير العميل بنجاح');
    //     } catch (\Exception $e) {
    //         return $this->responseApiError('حدث خطأ ما.');
    //     }
    // }
    public function clientInvoices($id)
    {
        try {
            $client = $this->ClientsRepository->getById($id);

            if (!$client) {
                return $this->responseApiError('العميل غير موجود');
            }
            $oneYearAgo = Carbon::now()->subYear();

            $unpaidAndPartialInvoices = Invoice::with(['client', 'employee', 'subscription'])
                ->where('client_id', $id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->where('created_at', '>=', $oneYearAgo)
                ->orderBy('created_at', 'asc')
                ->get();

            $user = auth('api')->user();

            $paidInvoicesForUser = Invoice::with(['client', 'employee', 'subscription', 'revenues' => function ($q) use ($user) {
                $q->where('collected_by', $user->id)
                    ->orderBy('received_at', 'desc');
            }])
                ->where('client_id', $id)
                ->whereIn('status', ['paid', 'partial'])
                ->where('created_at', '>=', $oneYearAgo)
                ->orderBy('paid_date', 'desc')
                ->get();

            $paidInvoices = Invoice::with([
                'client',
                'employee',
                'subscription',
                'revenues' => function ($q) {
                    $q->orderBy('received_at', 'desc');
                }
            ])
                ->where('client_id', $id)
                ->whereIn('status', ['paid', 'partial'])
                ->where('created_at', '>=', $oneYearAgo)
                ->orderBy('paid_date', 'desc')
                ->get();


            // dd($paidInvoices);
            $processedPaidInvoices = [];
            foreach ($paidInvoices as $invoice) {
                if ($invoice->revenues->count() > 0) {
                    foreach ($invoice->revenues as $revenue) {

                        // $paidBeforeThisRevenue = $revenue->amount + $revenue->remaining_amount;

                        $processedPaidInvoices[] = [
                            'id' => $invoice->id,
                            'invoice_number' => ($invoice->client->client_type == 'satellite' ? 'SA-' : 'IN-') . $invoice->invoice_number,
                            'client_id' => $invoice->client->id,
                            'client_name' => $invoice->client->name,
                            'client_phone' => $invoice->client->phone,
                            'client_address' => $invoice->client->address1,
                            'subscription_id' => $invoice->subscription_id,
                            'subscription' => $invoice->subscription ? $invoice->subscription->name : trans('invoices.service'),
                            'amount' => $invoice->amount,
                            'paid_amount' => $revenue->amount,
                            // 'remaining_before_payment' => $paidBeforeThisRevenue,
                            'remaining_amount' => $revenue->remaining_amount,
                            'due_date' => $invoice->due_date ?? 'N/A',
                            'paid_date' => $revenue->received_at,
                            'collected_by' => $revenue->user->name,
                            // 'status' => $revenue->status,
                            'status' => 'paid',
                            'invoice_type' => $invoice->invoice_type,
                            'notes' => $revenue->notes,
                            'currency' => get_app_config_data('currency')
                        ];
                    }
                }
            }

            $data = [
                'client' => new ClientResource($client),
                'paid_invoices' => [
                    'count' => count($processedPaidInvoices),
                    'total_paid_amount' => $paidInvoicesForUser->sum('paid_amount'),
                    'invoices' => $processedPaidInvoices
                ],
                'unpaid_and_partial_invoices' => [
                    'count' => $unpaidAndPartialInvoices->count(),
                    'total_unpaidAndPartial_amount' => $unpaidAndPartialInvoices->sum('remaining_amount'),
                    'invoices' => InvoiceResource::collection($unpaidAndPartialInvoices)
                ],
            ];

            return $this->responseApi($data, 'تم استرجاع فواتير العميل بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }
}
