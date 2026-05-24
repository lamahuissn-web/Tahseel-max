<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Invoice;
use App\Models\Admin\Revenue;
use App\Models\Admin\Subscription;
use App\Models\Clients;
use App\Notifications\InvoiceDeletedNotification;
use App\Notifications\InvoicePaidNotification;
use App\Notifications\InvoiceRedoNotification;
use App\Services\InvoiceService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AllDataExport;
use App\Models\AppConfig;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $admin_view = 'dashbord.invoices';
    protected $ClientsRepository;
    protected $invoiceService;
    protected $SubscriptionRepository;
    protected $InvoiceRepository;

    public function __construct(BasicRepositoryInterface $basicRepository, InvoiceService $invoiceService)
    {
        $this->middleware('can:list_invoices')->only('index', 'dueMonthlyInvoices', 'newlyPaidInvoices');
        $this->middleware('can:delete_invoice')->only('destroy');
        $this->middleware('can:pay_invoice')->only('pay_invoice');
        $this->middleware('can:view_invoice_details')->only('show_details');
        $this->middleware('can:print_invoice')->only('print_invoice');
        $this->middleware('can:redo_invoice')->only('redo_invoice');

        $this->InvoiceRepository = createRepository($basicRepository, new Invoice());
        $this->SubscriptionRepository = createRepository($basicRepository, new Subscription());
        $this->ClientsRepository = createRepository($basicRepository, new Clients());
        $this->invoiceService = $invoiceService;
    }


    public function index2(Request $request)
    {
        if ($request->ajax()) {

            // Use direct query builder instead of loading all data first
            $query = Invoice::with(['client', 'employee', 'subscription', 'revenues' => function($q) {
                $q->whereNull('deleted_at')->orderBy('created_at', 'desc');
            }])
                ->whereNull('deleted_at')
                ->orderBy('id');

            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->filled('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            if ($request->filled('subscription_id') && $request->subscription_id != '') {
                $query->whereHas('subscription', function ($q) use ($request) {
                    $q->where('id', $request->subscription_id);
                });
            }

            if ($request->filled('collector_id') && $request->collector_id != '') {
                $query->whereHas('revenues.user', function ($q) use ($request) {
                    $q->where('id', $request->collector_id);
                });
            }

            if ($request->filled('min_amount') && $request->min_amount != '') {
                $query->where('amount', '>=', $request->min_amount);
            }
            if ($request->filled('max_amount') && $request->max_amount != '') {
                $query->where('amount', '<=', $request->max_amount);
            }

            if ($request->filled('from_date') && $request->from_date != '') {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date') && $request->to_date != '') {
                $query->whereDate('created_at', '<=', $request->to_date);
            }
            return Datatables::of($query)

                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('invoice_number', function ($row) {
                    // return $row->invoice_number ?? 'N/A';
                    $prefix = $row->client && $row->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                    return $prefix . $row->invoice_number;
                })
                ->addColumn('client', function ($row) {
                    if ($row->client) {
                        $url = route('admin.client_paid_invoices', $row->client->id);
                        return '<a href="' . $url . '" class="text-primary fw-bold" style="text-decoration: underline;">' . $row->client->name . '</a>';
                    }
                    return 'N/A';
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount ?? 'N/A';
                })
                ->addColumn('paid_amount', function ($row) {
                    // return $row->amount - $row->remaining_amount;
                    return $row->paid_amount ?? 'N/A';
                })
                ->addColumn('remaining_amount', function ($row) {
                    return $row->remaining_amount ?? 'N/A';
                })
                ->addColumn('due_date', function ($row) {
                    return $row->due_date ?? 'N/A';
                })
                ->addColumn('paid_date', function ($row) {
                    return $row->paid_date
                        ? Carbon::parse($row->paid_date)->format('Y-m-d h:i A')
                        : 'N/A';
                })
                ->addColumn('collected_by', function ($row) {
                    $latestRevenue = $row->revenues->first();

                    if ($latestRevenue && $latestRevenue->collected_by) {
                        $admin = \App\Models\Admin::find($latestRevenue->collected_by);
                        if ($admin) {
                            return $admin->name;
                        }
                    }
                    return 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status ?? 'N/A';
                    $class = match ($status) {
                        'paid' => 'badge bg-success text-white',
                        'partial' => 'badge bg-warning text-dark',
                        'unpaid' => 'badge bg-danger text-white',
                    };
                    return '<span class="' . $class . 'px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.' . $status) . '</span>';
                })
                ->addColumn('subscription', function ($row) {
                    return $row->subscription ? $row->subscription->name : '<span class="badge bg-success text-white px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.service') . '</span>';
                })
                ->addColumn('month_year', function ($row) {
                    return $row->enshaa_date ? Carbon::parse($row->enshaa_date)->format('F Y') : 'N/A';
                })
                ->addColumn('notes', function ($row) {
                    return $row->notes ?? 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';

                    if (($row->status == 'unpaid' || $row->status == 'partial') && auth()->user()->can('pay_invoice')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="showPayModal(\'' . route('admin.pay_invoice', $row->id) . '\', ' . $row->remaining_amount . ', ' . $row->amount . ', `' . str_replace('`', '\`', $row->notes ?? '') . '`, `' . ($row->paid_date ?? '') . '`)"
                                class="btn btn-sm btn-success" title="' . trans('invoices.mark_as_paid') . '" style="font-size: 16px;">
                                <i class="bi bi-check-circle"></i>
                            </a>';
                    }
                    if (auth()->user()->can('print_invoice')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="print_invoice(\'' . route('admin.print_invoice', $row->id) . '\')"
                                class="btn btn-sm btn-warning" title="' . trans('invoices.print') . '" style="font-size: 16px;">
                                <i class="bi bi-printer"></i>
                            </a>';
                    }

                    if (auth()->user()->can('view_invoice_details')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $row->id) . '\')"
                                class="btn btn-sm btn-info" title="' . trans('invoices.view_details') . '" style="font-size: 16px;">
                                <i class="bi bi-eye"></i>
                            </a>';
                    }

                    // if (($row->status == 'paid' || $row->status == 'partial') && $row->subscription_id != null) {
                    if (($row->status == 'paid' || $row->status == 'partial') && auth()->user()->can('redo_invoice')) {
                        $buttons .= '
                            <a onclick="return confirm(\'' . trans('invoices.confirm_redo') . '\')"
                                href="' . route('admin.redo_invoice', $row->id) . '"
                                class="btn btn-sm btn-secondary" title="' . trans('invoices.redo_invoice') . '" style="font-size: 16px;">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>';
                    }
                    if (auth()->user()->can('delete_invoice')) {
                        $buttons .= '
                            <a onclick="return confirm(\'' . trans('employees.confirm_delete') . '\')"
                                href="' . route('admin.delete_invoice', $row->id) . '"
                                class="btn btn-sm btn-danger" title="' . trans('clients.delete') . '" style="font-size: 16px;">
                                <i class="bi bi-trash3"></i>
                            </a>';
                    }
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['subscription', 'action', 'client', 'status', 'month_year', 'invoice_number'])
                ->make(true);
        }

        $data['clients'] = $this->ClientsRepository->getAll();
        $data['subscriptions'] = Subscription::all();
        $data['collectors'] = Admin::whereHas('revenues')->get();

        return view($this->admin_view . '.index', $data);
    }

    //-------------------------------------------------------------------
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Invoice::select([
                'tbl_invoices.id',
                'tbl_invoices.invoice_number',
                'tbl_invoices.client_id',
                'tbl_invoices.subscription_id',
                'tbl_invoices.amount',
                'tbl_invoices.paid_amount',
                'tbl_invoices.remaining_amount',
                'tbl_invoices.due_date',
                'tbl_invoices.paid_date',
                'tbl_invoices.status',
                'tbl_invoices.notes',
                'tbl_invoices.created_at'
            ])
            ->with(['client:id,name,client_type'])
            ->with(['subscription:id,name'])
            ->with(['revenues' => function($q) {
                $q->select('id', 'invoice_id', 'collected_by', 'created_at')
                  ->whereNull('deleted_at')
                  ->orderBy('created_at', 'desc');
            }])
            ->whereNull('tbl_invoices.deleted_at');

        // Apply filters
        if ($request->has('client_id') && !empty($request->client_id)) {
            $query->where('tbl_invoices.client_id', $request->client_id);
        }

        if ($request->has('subscription_id') && !empty($request->subscription_id)) {
            $query->where('tbl_invoices.subscription_id', $request->subscription_id);
        }

        if ($request->has('collector_id') && !empty($request->collector_id)) {
            $query->whereExists(function($q) use ($request) {
                $q->select(DB::raw(1))
                  ->from('tbl_revenues')
                  ->whereColumn('tbl_revenues.invoice_id', 'tbl_invoices.id')
                  ->where('tbl_revenues.collected_by', $request->collector_id);
            });
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('tbl_invoices.status', $request->status);
        }

        if ($request->has('min_amount') && !empty($request->min_amount)) {
            $query->where('tbl_invoices.amount', '>=', $request->min_amount);
        }

        if ($request->has('max_amount') && !empty($request->max_amount)) {
            $query->where('tbl_invoices.amount', '<=', $request->max_amount);
        }

        if ($request->has('from_date') && !empty($request->from_date)) {
            if ($request->has('status') && in_array($request->status, ['paid', 'partial'])) {
                $query->whereDate('tbl_invoices.paid_date', '>=', $request->from_date);
            } else {
                $query->whereDate('tbl_invoices.due_date', '>=', $request->from_date);
            }
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            if ($request->has('status') && in_array($request->status, ['paid', 'partial'])) {
                $query->whereDate('tbl_invoices.paid_date', '<=', $request->to_date);
            } else {
                $query->whereDate('tbl_invoices.due_date', '<=', $request->to_date);
            }
        }

        if ($request->has('month_filter') && !empty($request->month_filter)) {
            // Filter by month based on due_date
            $monthYear = $request->month_filter; // Format: YYYY-MM
            $query->whereYear('tbl_invoices.due_date', substr($monthYear, 0, 4))
                  ->whereMonth('tbl_invoices.due_date', substr($monthYear, 5, 2));
        }

        if ($request->has('client_type') && !empty($request->client_type)) {
            $query->whereExists(function($q) use ($request) {
                $q->select(DB::raw(1))
                  ->from('tbl_clients')
                  ->whereColumn('tbl_clients.id', 'tbl_invoices.client_id')
                  ->where('tbl_clients.client_type', $request->client_type);
            });
        }

        // Apply sorting
        $sortBy = $request->has('sort_by') && in_array($request->sort_by, ['id', 'due_date', 'paid_date']) 
            ? $request->sort_by 
            : 'id';
        $sortOrder = $request->has('sort_order') && in_array($request->sort_order, ['asc', 'desc']) 
            ? $request->sort_order 
            : 'desc';
        
        // Handle sorting for paid_date (can be null)
        if ($sortBy === 'paid_date') {
            if ($sortOrder === 'asc') {
                $query->orderByRaw('ISNULL(tbl_invoices.paid_date), tbl_invoices.paid_date ASC');
            } else {
                $query->orderByRaw('ISNULL(tbl_invoices.paid_date), tbl_invoices.paid_date DESC');
            }
        } else {
            $query->orderBy('tbl_invoices.' . $sortBy, $sortOrder);
        }

            return Datatables::of($query)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('invoice_number', function ($row) {
                    $prefix = $row->client && $row->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                    return $prefix . $row->invoice_number;
                })
                ->addColumn('client', function ($row) {
                    if ($row->client) {
                        $url = route('admin.client_paid_invoices', $row->client->id);
                        return '<a href="' . $url . '" class="text-primary fw-bold" style="text-decoration: underline;">' . $row->client->name . '</a>';
                    }
                    return 'N/A';
                })
                ->addColumn('client_type', function ($row) {
                    if ($row->client) {
                        $clientType = $row->client->client_type ?? 'N/A';

                        if ($clientType == 'satellite') {
                            return '
                                <div class="text-center">
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-satellite me-1"></i>
                                        ' . trans('clients.satellite') . '
                                    </span>
                                    <div class="small text-muted mt-1">قمر صناعي</div>
                                </div>';
                        } else if ($clientType == 'internet') {
                            return '
                                <div class="text-center">
                                    <span class="badge bg-info text-white px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-wifi me-1"></i>
                                        ' . trans('clients.internet') . '
                                    </span>
                                    <div class="small text-muted mt-1">انترنت</div>
                                </div>';
                        }
                    }
                    return '
                        <div class="text-center">
                            <span class="badge bg-secondary px-3 py-2 rounded-pill fw-bold">
                                <i class="bi bi-question-circle me-1"></i>
                                N/A
                            </span>
                        </div>';
                })
                ->addColumn('subscription', function ($row) {
                    return $row->subscription ? $row->subscription->name : '<span class="badge bg-success text-white px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.service') . '</span>';
                })
                ->addColumn('due_date', function ($row) {
                    return $row->due_date ?? 'N/A';
                })
                ->addColumn('paid_date', function ($row) {
                    return $row->paid_date ? $row->paid_date : 'N/A';
                })
                ->addColumn('collected_by', function ($row) {
                    $latestRevenue = $row->revenues->first();

                    if ($latestRevenue && $latestRevenue->user) {
                        return $latestRevenue->user->name;
                    }
                    return 'N/A';
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount ?? 'N/A';
                })
                ->addColumn('paid_amount', function ($row) {
                    // return $row->amount - $row->remaining_amount;
                    return $row->paid_amount ?? 'N/A';
                })
                ->addColumn('remaining_amount', function ($row) {
                    return $row->remaining_amount ?? 'N/A';
                })
                ->addColumn('notes', function ($row) {
                    return $row->notes ?? 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status ?? 'N/A';
                    $config = match ($status) {
                        'paid' => [
                            'class' => 'badge bg-success text-white',
                            'icon' => 'bi-check-circle-fill',
                            'style' => 'box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);'
                        ],
                        'partial' => [
                            'class' => 'badge bg-warning text-dark',
                            'icon' => 'bi-clock-history',
                            'style' => 'box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);'
                        ],
                        'unpaid' => [
                            'class' => 'badge bg-danger text-white',
                            'icon' => 'bi-x-circle-fill',
                            'style' => 'box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);'
                        ],
                        default => [
                            'class' => 'badge bg-secondary text-white',
                            'icon' => 'bi-question-circle-fill',
                            'style' => 'box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);'
                        ]
                    };
                    
                    return '<span class="' . $config['class'] . ' px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center gap-2" style="' . $config['style'] . ' transition: all 0.3s ease; font-size: 0.875rem;">
                        <i class="' . $config['icon'] . '"></i>
                        ' . trans('invoices.' . $status) . '
                    </span>';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';

                    if (($row->status == 'unpaid' || $row->status == 'partial') && auth()->user()->can('pay_invoice')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="showPayModal(\'' . route('admin.pay_invoice', $row->id) . '\', ' . $row->remaining_amount . ', ' . $row->amount . ', `' . str_replace('`', '\`', $row->notes ?? '') . '`, `' . ($row->paid_date ?? '') . '`)"
                                class="btn btn-sm btn-success" title="' . trans('invoices.mark_as_paid') . '" style="font-size: 16px;">
                                <i class="bi bi-check-circle"></i>
                            </a>';
                    }
                    if (auth()->user()->can('print_invoice')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="print_invoice(\'' . route('admin.print_invoice', $row->id) . '\')"
                                class="btn btn-sm btn-warning" title="' . trans('invoices.print') . '" style="font-size: 16px;">
                                <i class="bi bi-printer"></i>
                            </a>';
                    }

                    if (auth()->user()->can('view_invoice_details')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $row->id) . '\')"
                                class="btn btn-sm btn-info" title="' . trans('invoices.view_details') . '" style="font-size: 16px;">
                                <i class="bi bi-eye"></i>
                            </a>';
                    }

                    // if (($row->status == 'paid' || $row->status == 'partial') && $row->subscription_id != null) {
                    if (($row->status == 'paid' || $row->status == 'partial') && auth()->user()->can('redo_invoice')) {
                        $buttons .= '
                            <a onclick="return confirm(\'' . trans('invoices.confirm_redo') . '\')"
                                href="' . route('admin.redo_invoice', $row->id) . '"
                                class="btn btn-sm btn-secondary" title="' . trans('invoices.redo_invoice') . '" style="font-size: 16px;">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>';
                    }
                    if (auth()->user()->can('delete_invoice')) {
                        $buttons .= '
                            <a onclick="return confirm(\'' . trans('employees.confirm_delete') . '\')"
                                href="' . route('admin.delete_invoice', $row->id) . '"
                                class="btn btn-sm btn-danger" title="' . trans('clients.delete') . '" style="font-size: 16px;">
                                <i class="bi bi-trash3"></i>
                            </a>';
                    }
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['subscription', 'action', 'client_type', 'client', 'status', 'invoice_number'])
                ->make(true);
        }
            $data['clients'] = $this->ClientsRepository->getAll();
        $data['subscriptions'] = Subscription::all();
        $data['collectors'] = Admin::whereHas('revenues')->get();

        return view($this->admin_view . '.index', $data);
    }



    //--------------------------------------------------------------------

    /***********************************************/
    public function destroy(string $id)
    {
        try {
            $invoice = $this->InvoiceRepository->getById($id);

            if (!$invoice) {
                return redirect()->back()->withErrors(['error' => trans('invoices.invoice_not_found')]);
            }

            $invoiceData = [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $invoice->amount,
                'client_name' => $invoice->client->name ?? 'N/A',
                'client_id' => $invoice->client_id
            ];

            $invoice->revenues()->delete();
            $this->InvoiceRepository->delete($id);

            $notificationMessage = sprintf(
                'تم حذف فاتورة رقم %s للعميل %s التي كانت بمبلغ %s %s (تمت العملية بواسطة: %s)',
                $invoiceData['invoice_number'],
                $invoiceData['client_name'],
                number_format($invoiceData['amount'], 2),
                get_app_config_data('currency'),
                auth()->user()->name
            );

            $admins = Admin::where('status', '1')
                ->whereNull('deleted_at')
                ->whereHas('roles', function ($query) {
                    $query->whereIn('id', [1, 7]);
                })
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new InvoiceDeletedNotification(
                    $invoiceData,
                    auth()->user(),
                    $notificationMessage
                ));
            }

            if (!empty($admins)) {
                sendOneSignalNotification1(
                    $admins,
                    $notificationMessage,
                    [
                        'type' => 'invoice_deleted',
                        'amount' => $invoiceData['amount'],
                        'initiator' => auth()->user()->name,
                        'invoice_details' => [
                            'number' => $invoiceData['invoice_number'],
                            'client' => $invoiceData['client_name']
                        ]
                    ],
                    null
                );
            }

            sendTelegramNotification($notificationMessage, 'invoice_deleted');

            log_helper(
                'invoice_deleted',
                $notificationMessage,
                [
                    'model' => $invoice,
                    'old_data' => $invoiceData
                ]
            );

            toastr()->addSuccess(trans('forms.success'));
            // return redirect()->route('admin.invoices.index');
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function pay_invoice1($id, Request $request)
    {
        $request->validate([
            'invoice_amount' => 'required|numeric|min:1',
            'paid_amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);
        // dd($request->all());
        try {
            $result = $this->invoiceService->payInvoice($id, $request);
            $invoice = Invoice::findOrFail($id);
            $admins = Admin::where('status', '1')
                ->whereNull('deleted_at')
                // ->where('id', '!=', auth()->id())
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new InvoicePaidNotification(
                    $invoice,
                    $request->paid_amount,
                    auth()->user(),
                    'تم دفع فاتورة رقم ' . $invoice->id . ' بقيمة ' . $request->paid_amount . ' جنيه'
                ));
            }

            return $result;
            // toastr()->addSuccess(trans('forms.success'));
            // return redirect()->back()->with('success', trans('forms.success'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay_invoice($id, Request $request)
    {
        $request->validate([
            'invoice_amount' => 'required|numeric|min:1',
            'paid_amount' => 'nullable|numeric',
            'paid_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($request->ajax()) {
            return $this->payInvoiceAjax($id, $request);
        }

        try {
            DB::beginTransaction();
            $oldInvoiceData = Invoice::find($id)->toArray();

            $result = $this->invoiceService->payInvoice($id, $request);
            $invoice = Invoice::findOrFail($id);

            $notificationMessage = sprintf(
                'تم دفع مبلغ %s %s للعميل %s، وكان تاريخ الاستحقاق %s. (تمت العملية بواسطة: %s)',
                number_format($request->paid_amount, 2),
                get_app_config_data('currency'),
                $invoice->client->name ?? 'غير محدد',
                $invoice->due_date,
                auth()->user()->name
            );

            $admins = Admin::where('status', '1')
                ->whereNull('deleted_at')
                ->whereHas('roles', function ($query) {
                    $query->whereIn('id', [1, 7]);
                })
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new InvoicePaidNotification(
                    $invoice,
                    $request->paid_amount,
                    auth()->user(),
                    $notificationMessage
                ));
            }

            if (!empty($admins)) {
                sendOneSignalNotification1(
                    $admins,
                    $notificationMessage,
                    [
                        'invoice_id' => $invoice->id,
                        'type' => 'invoice_paid',
                        'amount' => $request->paid_amount,
                        'initiator' => auth()->user()->name,
                        'invoice_details' => [
                            'number' => $invoice->invoice_number,
                            'date' => $invoice->paid_date,
                            'client' => $invoice->client->name ?? 'Unknown'
                        ]
                    ],
                    null
                );
            }

            sendTelegramNotification($notificationMessage, 'invoice_paid');

            DB::commit();

            log_helper(
                'invoice_paid',
                $notificationMessage,
                [
                    'model' => $invoice,
                    'amount' => $request->paid_amount,
                    'old_data' => $oldInvoiceData,
                    'new_data' => $invoice->toArray()
                ]
            );

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function payInvoiceAjax($id, Request $request)
    {
        try {
            DB::beginTransaction();
            $oldInvoiceData = Invoice::find($id)->toArray();

            $result = $this->invoiceService->payInvoice($id, $request);

            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                $session = $result->getSession();
                if ($session && $session->has('error')) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $session->get('error')], 422);
                }
            }

            $invoice = Invoice::findOrFail($id);

            $notificationMessage = sprintf(
                'تم دفع مبلغ %s %s للعميل %s، وكان تاريخ الاستحقاق %s. (تمت العملية بواسطة: %s)',
                number_format($request->paid_amount, 2),
                get_app_config_data('currency'),
                $invoice->client->name ?? 'غير محدد',
                $invoice->due_date,
                auth()->user()->name
            );

            $admins = Admin::where('status', '1')
                ->whereNull('deleted_at')
                ->whereHas('roles', function ($query) {
                    $query->whereIn('id', [1, 7]);
                })
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new InvoicePaidNotification(
                    $invoice,
                    $request->paid_amount,
                    auth()->user(),
                    $notificationMessage
                ));
            }

            if (!empty($admins)) {
                sendOneSignalNotification1(
                    $admins,
                    $notificationMessage,
                    [
                        'invoice_id' => $invoice->id,
                        'type' => 'invoice_paid',
                        'amount' => $request->paid_amount,
                        'initiator' => auth()->user()->name,
                        'invoice_details' => [
                            'number' => $invoice->invoice_number,
                            'date' => $invoice->paid_date,
                            'client' => $invoice->client->name ?? 'Unknown'
                        ]
                    ],
                    null
                );
            }

            sendTelegramNotification($notificationMessage, 'invoice_paid');

            DB::commit();

            log_helper(
                'invoice_paid',
                $notificationMessage,
                [
                    'model' => $invoice,
                    'amount' => $request->paid_amount,
                    'old_data' => $oldInvoiceData,
                    'new_data' => $invoice->toArray()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => trans('forms.success'),
                'invoice' => $invoice->toArray(),
                'new_remaining' => $invoice->client->invoices()->sum('remaining_amount'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show_details($id)
    {
        $data['all_data'] = Invoice::with('client', 'subscription', 'employee')->findOrFail($id);
        // dd($data);
        return view($this->admin_view . '.details', $data);
    }

    public function show_details_partial($id)
    {
        $invoice = Invoice::with('client', 'subscription', 'employee')->findOrFail($id);
        return view('dashbord.invoices.details_partial', compact('invoice'));
    }

    public function print_invoice($id)
    {
        $data['all_data'] = Invoice::findOrFail($id);
        return view($this->admin_view . '.print', $data);
    }

    public function redo_invoice1($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $lastPayment = Revenue::where('invoice_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$lastPayment) {
                return redirect()->back()->withErrors(['error' => 'لا توجد دفعات سابقة لهذه الفاتورة!']);
            }

            $invoice->remaining_amount += $lastPayment->amount;
            $invoice->paid_amount -= $lastPayment->amount;

            $client = Clients::find($invoice->client_id);
            if (!$client) {
                return redirect()->back()->withErrors(['error' => 'العميل غير موجود!']);
            }

            if ($invoice->remaining_amount == $invoice->amount) {
                $invoice->status = 'unpaid';
                // $invoice->remaining_amount = 0.0;
                $invoice->paid_date = null;
                // if ($invoice->invoice_type == 'subscription') {
                //     $invoice->amount = $client->price;
                // }
            } elseif ($invoice->remaining_amount > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();

            $financialTransaction = FinancialTransaction::where('account_id', auth()->user()->account_id)
                ->where('amount', $lastPayment->amount)
                ->where('created_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($financialTransaction) {
                $financialTransaction->delete();
            }

            $lastPayment->delete();
            $admins = Admin::where('status', '1')
                ->whereNull('deleted_at')
                // ->where('id', '!=', auth()->id())
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new InvoiceRedoNotification(
                    $invoice,
                    $lastPayment->amount,
                    auth()->user(),
                    'تم التراجع عن دفع فاتورة رقم ' . $invoice->id . ' بقيمة ' . $lastPayment->amount . ' جنيه'
                ));
            }

            return redirect()->back()->with(['success' => trans('messages.redo_successfully')]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function redo_invoice($id)
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($id);
            $oldInvoiceData = $invoice->toArray();
            $lastPayment = Revenue::where('invoice_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$lastPayment) {
                return redirect()->back()->withErrors(['error' => 'لا توجد دفعات سابقة لهذه الفاتورة!']);
            }

            $invoice->remaining_amount += $lastPayment->amount;
            $invoice->paid_amount -= $lastPayment->amount;

            $client = Clients::find($invoice->client_id);
            if (!$client) {
                return redirect()->back()->withErrors(['error' => 'العميل غير موجود!']);
            }

            if ($invoice->remaining_amount == $invoice->amount) {
                $invoice->status = 'unpaid';
                $invoice->paid_date = null;
            } elseif ($invoice->remaining_amount > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();

            $originalCollectorAccountId = Admin::find($lastPayment->collected_by)->account_id ?? null;

            $financialTransaction = null;
            if ($originalCollectorAccountId) {
                $financialTransaction = FinancialTransaction::where('account_id', $originalCollectorAccountId)
                    ->where('amount', $lastPayment->amount)
                    ->where('type', 'qapd')
                    ->whereBetween('created_at', [
                        $lastPayment->created_at->subMinutes(10),
                        $lastPayment->created_at->addMinutes(10)
                    ])
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            if ($financialTransaction) {
                $financialTransaction->delete();
            }

            $lastPayment->delete();

            // $notificationMessage = sprintf(
            //     'تم التراجع عن دفع فاتورة رقم %s بقيمة %s جنيه للعميل %s بواسطة %s',
            //     $invoice->invoice_number,
            //     $lastPayment->amount,
            //     $client->name ?? 'غير معروف',
            //     auth()->user()->name
            // );

            $notificationMessage = sprintf(
                'تم التراجع عن دفع فاتورة رقم %s بقيمة %s %s للعميل %s بواسطة %s',
                $invoice->invoice_number,
                $lastPayment->amount,
                get_app_config_data('currency') ?? 'جنيه',
                $client->name ?? 'غير معروف',
                auth()->user()->name
            );

            $admins = Admin::where('status', '1')
                ->whereNull('deleted_at')
                ->whereHas('roles', function ($query) {
                    $query->whereIn('id', [1, 7]);
                })
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new InvoiceRedoNotification(
                    $invoice,
                    $lastPayment->amount,
                    auth()->user(),
                    $notificationMessage
                ));
            }

            if (!empty($admins)) {
                sendOneSignalNotification1(
                    $admins,
                    $notificationMessage,
                    [
                        'invoice_id' => $invoice->id,
                        'type' => 'invoice_payment_redo',
                        'amount' => $lastPayment->amount,
                        'initiator' => auth()->user()->name,
                        'invoice_details' => [
                            'number' => $invoice->invoice_number,
                            'client' => $client->name ?? 'Unknown',
                            'status' => $invoice->status
                        ]
                    ],
                    null
                );
            }

            sendTelegramNotification($notificationMessage, 'invoice_redone');

            DB::commit();

            log_helper(
                'invoice_redo',
                $notificationMessage,
                [
                    'model' => $invoice,
                    'amount' => $lastPayment->amount,
                    'old_data' => $oldInvoiceData,
                    'new_data' => $invoice->toArray()
                ]
            );
            return redirect()->back()->with(['success' => trans('messages.redo_successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function dueMonthlyInvoices(Request $request)
    {
        if ($request->ajax()) {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $query = Invoice::select([
                'tbl_invoices.id',
                'tbl_invoices.invoice_number',
                'tbl_invoices.client_id',
                'tbl_invoices.subscription_id',
                'tbl_invoices.amount',
                'tbl_invoices.paid_amount',
                'tbl_invoices.remaining_amount',
                'tbl_invoices.due_date',
                'tbl_invoices.paid_date',
                'tbl_invoices.status',
                'tbl_invoices.notes',
                'tbl_invoices.created_at'
            ])
            ->with(['client:id,name,client_type'])
            ->with(['subscription:id,name'])
            ->with(['revenues' => function($q) {
                $q->select('id', 'invoice_id', 'collected_by', 'created_at')
                  ->whereNull('deleted_at')
                  ->orderBy('created_at', 'desc');
            }])
            ->whereMonth('tbl_invoices.due_date', $currentMonth)
            ->whereYear('tbl_invoices.due_date', $currentYear)
            ->where('tbl_invoices.remaining_amount', '>', 0)
            ->whereNull('tbl_invoices.deleted_at')
            ->orderBy('tbl_invoices.due_date', 'asc');

            return Datatables::of($query)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('invoice_number', function ($row) {
                    $prefix = $row->client && $row->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                    return $prefix . $row->invoice_number;
                })
                ->addColumn('client', function ($row) {
                    if ($row->client) {
                        $url = route('admin.client_paid_invoices', $row->client->id);
                        return '<a href="' . $url . '" class="text-primary fw-bold" style="text-decoration: underline;">' . $row->client->name . '</a>';
                    }
                    return 'N/A';
                })
                ->addColumn('subscription', function ($row) {
                    return $row->subscription ? $row->subscription->name : '<span class="badge bg-success text-white px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.service') . '</span>';
                })
                ->addColumn('due_date', function ($row) {
                    return $row->due_date ?? 'N/A';
                })
                ->addColumn('paid_date', function ($row) {
                    return $row->paid_date ? $row->paid_date : 'N/A';
                })
                ->addColumn('collected_by', function ($row) {
                    $latestRevenue = $row->revenues->first();

                    if ($latestRevenue && $latestRevenue->user) {
                        return $latestRevenue->user->name;
                    }
                    return 'N/A';
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount ?? 'N/A';
                })
                ->addColumn('paid_amount', function ($row) {
                    // return $row->amount - $row->remaining_amount;
                    return $row->paid_amount ?? 'N/A';
                })
                ->addColumn('remaining_amount', function ($row) {
                    return $row->remaining_amount ?? 'N/A';
                })
                ->addColumn('notes', function ($row) {
                    return $row->notes ?? 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status ?? 'N/A';
                    $class = match ($status) {
                        'paid' => 'badge bg-success text-white',
                        'partial' => 'badge bg-warning text-dark',
                        'unpaid' => 'badge bg-danger text-white',
                    };
                    return '<span class="' . $class . 'px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.' . $status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';

                    if (($row->status == 'unpaid' || $row->status == 'partial') && auth()->user()->can('pay_invoice')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="showPayModal(\'' . route('admin.pay_invoice', $row->id) . '\', ' . $row->remaining_amount . ', ' . $row->amount . ', `' . str_replace('`', '\`', $row->notes ?? '') . '`, `' . ($row->paid_date ?? '') . '`)"
                                class="btn btn-sm btn-success" title="' . trans('invoices.mark_as_paid') . '" style="font-size: 16px;">
                                <i class="bi bi-check-circle"></i>
                            </a>';
                    }
                    if (auth()->user()->can('print_invoice')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="print_invoice(\'' . route('admin.print_invoice', $row->id) . '\')"
                                class="btn btn-sm btn-warning" title="' . trans('invoices.print') . '" style="font-size: 16px;">
                                <i class="bi bi-printer"></i>
                            </a>';
                    }

                    if (auth()->user()->can('view_invoice_details')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $row->id) . '\')"
                                class="btn btn-sm btn-info" title="' . trans('invoices.view_details') . '" style="font-size: 16px;">
                                <i class="bi bi-eye"></i>
                            </a>';
                    }
                    // if (($row->status == 'paid' || $row->status == 'partial') && $row->subscription_id != null) {
                    if (($row->status == 'paid' || $row->status == 'partial') && auth()->user()->can('redo_invoice')) {
                        $buttons .= '
                            <a onclick="return confirm(\'' . trans('invoices.confirm_redo') . '\')"
                                href="' . route('admin.redo_invoice', $row->id) . '"
                                class="btn btn-sm btn-secondary" title="' . trans('invoices.redo_invoice') . '" style="font-size: 16px;">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>';
                    }

                    if (auth()->user()->can('delete_invoice')) {
                        $buttons .= '
                            <a onclick="return confirm(\'' . trans('employees.confirm_delete') . '\')"
                                href="' . route('admin.delete_invoice', $row->id) . '"
                                class="btn btn-sm btn-danger" title="' . trans('clients.delete') . '" style="font-size: 16px;">
                                <i class="bi bi-trash3"></i>
                            </a>';
                    }
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['subscription', 'action', 'client', 'status', 'invoice_number'])
                ->make(true);
        }
        return view($this->admin_view . '.monthly_due_invoices');
    }

    //--------------------------------------------------------------------------------




    //--------------------------------------------------------------------------------

    public function newlyPaidInvoices(Request $request)
    {
        if ($request->ajax()) {
            $allData = Invoice::with(['client', 'employee', 'subscription'])
                ->whereIn('status', ['paid', 'partial'])
                ->whereDate('paid_date', '>=', Carbon::now()->subDays(10))
                ->whereNull('deleted_at')
                ->orderBy('paid_date', 'desc')
                ->get();

            return Datatables::of($allData)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('invoice_number', function ($row) {
                    $prefix = $row->client && $row->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                    return $prefix . $row->invoice_number;
                })
                ->addColumn('client', function ($row) {
                    if ($row->client) {
                        $url = route('admin.client_paid_invoices', $row->client->id);
                        return '<a href="' . $url . '" class="text-primary fw-bold" style="text-decoration: underline;">' . $row->client->name . '</a>';
                    }
                    return 'N/A';
                })
                ->addColumn('amount', function ($row) {
                    // return $row->amount - $row->remaining_amount;
                    return $row->amount ?? 'N/A';
                })
                ->addColumn('paid_amount', function ($row) {
                    // return $row->amount - $row->remaining_amount;
                    return $row->paid_amount ?? 'N/A';
                })
                ->addColumn('remaining_amount', function ($row) {
                    return $row->remaining_amount ?? 'N/A';
                })
                ->addColumn('due_date', function ($row) {
                    return $row->due_date ?? 'N/A';
                })
                ->addColumn('paid_date', function ($row) {
                    return $row->paid_date ? $row->paid_date : 'N/A';
                })
                ->addColumn('collected_by', function ($row) {
                    $latestRevenue = $row->revenues->first();

                    if ($latestRevenue && $latestRevenue->user) {
                        return $latestRevenue->user->name;
                    }
                    return 'N/A';
                })
                ->addColumn('notes', function ($row) {
                    return $row->notes ?? 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status;
                    $class = match ($status) {
                        'paid' => 'badge bg-success text-white',
                        'partial' => 'badge bg-warning text-dark',
                        default => 'badge bg-secondary text-white',
                    };
                    return '<span class="' . $class . ' px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.' . $status) . '</span>';
                })
                ->addColumn('subscription', function ($row) {
                    return $row->subscription ? $row->subscription->name : '<span class="badge bg-success text-white px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.service') . '</span>';
                })
                // ->addColumn('month_year', function ($row) {
                //     return $row->enshaa_date ? Carbon::parse($row->enshaa_date)->format('F Y') : 'N/A';
                // })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';

                    if (auth()->user()->can('print_invoice')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="print_invoice(\'' . route('admin.print_invoice', $row->id) . '\')"
                                class="btn btn-sm btn-warning" title="' . trans('invoices.print') . '" style="font-size: 16px;">
                                <i class="bi bi-printer"></i>
                            </a>';
                    }

                    if (auth()->user()->can('view_invoice_details')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $row->id) . '\')"
                                class="btn btn-sm btn-info" title="' . trans('invoices.view_details') . '" style="font-size: 16px;">
                                <i class="bi bi-eye"></i>
                            </a>';
                    }


                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['subscription', 'action', 'client', 'status', 'invoice_number'])
                ->make(true);
        }

        return view($this->admin_view . '.new_paid_invoices');
    }

    public function generate()
    {
        return $this->invoiceService->generateMonthlyInvoices();
    }

    public function exportAllData()
    {
        $fileName = 'all_data_export_' . date('Y-m-d_His') . '.xlsx';
        
        // الحصول على مسار النسخ الاحتياطي من الإعدادات
        $backupPath = AppConfig::where('key', 'backup_path')->value('value');
        
        // إذا كان هناك مسار محدد، احفظ الملف هناك أيضاً
        if ($backupPath && !empty(trim($backupPath))) {
            $backupPath = trim($backupPath);
            
            // التأكد من وجود المجلد
            if (!is_dir($backupPath)) {
                try {
                    mkdir($backupPath, 0755, true);
                } catch (\Exception $e) {
                    // إذا فشل إنشاء المجلد، استخدم المسار الافتراضي
                    $backupPath = null;
                }
            }
            
            if ($backupPath) {
                // حفظ الملف في المسار المحدد
                $filePath = $backupPath . DIRECTORY_SEPARATOR . $fileName;
                
                try {
                    Excel::store(new AllDataExport(), $fileName, 'local');
                    
                    // نسخ الملف إلى المسار المحدد
                    $storedPath = storage_path('app/' . $fileName);
                    if (file_exists($storedPath)) {
                        copy($storedPath, $filePath);
                        unlink($storedPath); // حذف الملف المؤقت
                    }
                    
                    // تنزيل الملف
                    return response()->download($filePath)->deleteFileAfterSend(false);
                } catch (\Exception $e) {
                    // في حالة الفشل، استخدم الطريقة العادية
                    \Log::error('فشل حفظ النسخة الاحتياطية: ' . $e->getMessage());
                }
            }
        }
        
        // إذا لم يكن هناك مسار محدد أو فشل الحفظ، استخدم الطريقة العادية
        return Excel::download(new AllDataExport(), $fileName);
    }
}
