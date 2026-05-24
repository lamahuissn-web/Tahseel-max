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

    public function index2(Request $request)
    {
        try {
            $query = Clients::query();
            $query->where('is_active', 1);
            if ($request->has('search') && !empty(trim($request->input('search')))) {
                $search = trim($request->input('search'));
                $searchTerm = "%{$search}%";


                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
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

            $clients = $query->whereNull('deleted_at')->orderBy('created_at', 'desc')->get();
            $data = [
                'clients' => ClientResource::collection($clients)
            ];
            return $this->responseApi($data, 'تم استرجاع الزبائن بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }
    //---------------------------------------------------------------

    public function index(Request $request)
    {
        try {
            $query = Clients::query();
            $query->where('is_active', 1);
            
            if ($request->has('search') && !empty(trim($request->input('search')))) {
                $search = trim($request->input('search'));
                $searchTerm = "%{$search}%";

                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
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

            // Eager loading للعلاقات لتجنب N+1 problem
            $query->with(['subscription'])
                ->withSum('invoices', 'remaining_amount')
                ->select('tbl_clients.*')
                ->addSelect([
                    'latest_invoice_due_date' => \App\Models\Admin\Invoice::select('due_date')
                        ->whereColumn('client_id', 'tbl_clients.id')
                        ->orderBy('created_at', 'desc')
                        ->limit(1)
                ])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc');

            // Pagination - متغير ويمكن التحكم فيه من الموبايل
            // per_page: عدد العناصر في الصفحة (افتراضي: 15، الحد الأقصى: 100)
            $perPage = $request->input('per_page', 15);
            $perPage = min(max(1, (int)$perPage), 100); // بين 1 و 100
            
            // page: رقم الصفحة (افتراضي: 1)
            $page = $request->input('page', 1);
            $page = max(1, (int)$page); // على الأقل 1
            
            // تطبيق Pagination
            $clients = $query->paginate($perPage, ['*'], 'page', $page);
            
            $data = [
                'clients' => ClientResource::collection($clients->items()),
                'pagination' => [
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'per_page' => $clients->perPage(),
                    'total' => $clients->total(),
                    'from' => $clients->firstItem(),
                    'to' => $clients->lastItem(),
                    'prev_page_url' => $clients->previousPageUrl(),
                    'next_page_url' => $clients->nextPageUrl(),
                    'has_more_pages' => $clients->hasMorePages(),
                ]
            ];
            
            return $this->responseApi($data, 'تم استرجاع الزبائن بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    //---------------------------------------------------------------

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
