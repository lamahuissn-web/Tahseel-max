<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\clients\ImportClientsRequest;
use App\Http\Requests\Admin\clients\SaveRequests;
use App\Http\Requests\Admin\clients\UpdateRequests;
use App\Imports\ClientsImport;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Invoice;
use App\Models\Admin\Revenue;
use App\Models\Admin\Subscription;
use App\Models\Clients;
use App\Notifications\InvoiceCreatedNotification;
use App\Notifications\InvoicePaidNotification;
use App\Services\ClientService;
use App\Services\CompanyService;
use App\Services\ProjectsService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $admin_view = 'dashbord.clients';
    protected $NotificationsControllerNotificationsController;
    protected $clientService;
    protected $ClientsRepository;
    protected $SubscriptionRepository;
    protected $InvoiceRepository;

    public function __construct(BasicRepositoryInterface $basicRepository, ClientService $clientService, CompanyService $companyService, ProjectsService $projectsService)
    {
        $this->middleware('can:list_clients')->only('index');
        $this->middleware('can:create_client')->only('create', 'store');
        $this->middleware('can:update_client')->only('edit', 'update');
        $this->middleware('can:delete_client')->only('destroy');
        $this->middleware('can:view_client_unpaid_invoices')->only('client_unpaid_invoices', 'remainingInvoices');
        // $this->middleware('can:view_client_paid_invoices')->only('client_paid_invoices');
        $this->middleware('can:view_client_invoices')->only('client_invoices');
        $this->middleware('can:add_client_invoice')->only('client_add_invoice');


        $this->SubscriptionRepository = createRepository($basicRepository, new Subscription());
        $this->ClientsRepository = createRepository($basicRepository, new Clients());
        $this->clientService = $clientService;
        $this->InvoiceRepository   = createRepository($basicRepository, new Invoice());
    }


    public function index(Request $request)
    {
        if ($request->ajax()) {
            
            $query = Clients::select([
                'tbl_clients.id',
                'tbl_clients.name',
                'tbl_clients.phone',
                'tbl_clients.client_type',
                'tbl_clients.user',
                'tbl_clients.box_switch',
                'tbl_clients.address1',
                'tbl_clients.price',
                'tbl_clients.notes',
                'tbl_clients.start_date',
                'tbl_clients.is_active',
                'tbl_clients.subscription_id',
                'tbl_clients.sas_username',
                'tbl_clients.created_at'
            ])
            ->with(['subscription:id,name']) 
            ->withSum('invoices as remaining_amount_total', 'remaining_amount');

    
            if ($request->has('show_inactive_only') && $request->show_inactive_only == '1') {
              
                $query->where('tbl_clients.is_active', '0');
            } else {
                
                $query->where('tbl_clients.is_active', '1');
            }

     
            if ($request->has('client_type_filter') && !empty($request->client_type_filter)) {
                $query->where('tbl_clients.client_type', $request->client_type_filter);
            }
      
            if ($request->has('name_search') && !empty($request->name_search)) {
                $query->where('tbl_clients.name', 'like', '%' . $request->name_search . '%');
            }

            if ($request->has('other_fields_search') && !empty($request->other_fields_search)) {
                $searchTerm = $request->other_fields_search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('tbl_clients.phone', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tbl_clients.address1', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tbl_clients.notes', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tbl_clients.client_type', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tbl_clients.price', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tbl_clients.box_switch', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tbl_clients.start_date', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tbl_clients.user', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('subscription', function($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                    });
                });
            }

           
            return Datatables::of($query)
                ->addIndexColumn()
                ->addColumn('id', function ($row) {
                    return $row->id;
                })
                ->editColumn('name', function ($row) {
                    return $row->name ?? 'N/A';
                })
                ->editColumn('phone', function ($row) {
                    return $row->phone ?? 'N/A';
                })
                ->editColumn('client_type', function ($row) {
                    return $row->client_type ?? 'N/A';
                })
                ->editColumn('user', function ($row) {
                    return $row->user ?? 'N/A';
                })
                ->editColumn('box_switch', function ($row) {
                    return $row->box_switch ?? 'N/A';
                })
                ->editColumn('address1', function ($row) {
                    return $row->address1 ?? 'N/A';
                })
                ->addColumn('subscription', function ($row) {
                    return $row->subscription ? $row->subscription->name : 'N\A';
                })
                ->editColumn('price', function ($row) {
                    return $row->price ?? 'N/A';
                })
                ->editColumn('notes', function ($row) {
                    return $row->notes ? $row->notes : 'N/A';
                })
                ->editColumn('start_date', function ($row) {
                    return $row->start_date ? $row->start_date : 'N/A';
                })
                ->addColumn('remaining_amount', function ($row) {
                    return number_format($row->remaining_amount_total ?? 0, 2);
                })
                ->orderColumn('remaining_amount', function ($query, $order) {
                    $query->orderBy('remaining_amount_total', $order);
                })
                // ->addColumn('is_active', function ($row) {
                //     return $row->is_active
                //         ? '<span class="badge bg-success">'.trans('clients.active').'</span>'
                //         : '<span class="badge bg-danger">'.trans('clients.inactive').'</span>';
                // })
                ->addColumn('status', function ($row) {
                    if ($row->is_active == '1') {
                        $title = trans('clients.active');
                        $class = 'badge bg-success text-white';
                        $icon = '<i class="bi bi-check-circle-fill me-1"></i>';
                    } else {
                        $title = trans('clients.inactive');
                        $class = 'badge bg-danger text-white';
                        $icon = '<i class="bi bi-x-circle-fill me-1"></i>';
                    }

                    $badge = '<span class="' . $class . ' px-3 py-2 rounded-pill fw-bold status-badge">' . $icon . $title . '</span>';
                    
                    if (auth()->user()->can('update_client')) {
                        return '<a href="javascript:void(0)" 
                                onclick="changeClientStatus(' . $row->id . ', \'' . $row->is_active . '\')"
                                class="text-decoration-none cursor-pointer"
                                title="' . trans('clients.change_status') . '">'
                                . $badge . '</a>';
                    } else {
                        return $badge;
                    }
                })
                ->addColumn('sas4_status', function ($row) {
                    if (!$row->sas_username) {
                        return '<span class="badge bg-light text-muted">—</span>';
                    }
                    return '<span class="sas4-indicator" data-id="' . $row->id . '" data-username="' . e($row->sas_username) . '">' .
                        '<i class="bi bi-wifi text-secondary"></i> ' . e($row->sas_username) .
                        '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionButtons = '<div class="btn-group">';
                    $actionButtons .= '<button type="button" style="font-size: 16px" class="btn btn-sm btn-secondary">' . trans('employees.actions') . '</button>';
                    $actionButtons .= '<button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-icon" data-bs-toggle="dropdown" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>';
                    $actionButtons .= '<ul class="dropdown-menu">';
                    $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item" href="javascript:void(0)" onclick="showClientDetails(' . $row->id . ')"><i class="bi bi-eye-fill"></i> ' . trans('clients.view_details') . '</a></li>';
                    if (auth()->user()->can('update_client')) {
                        $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item" href="' . route('admin.clients.edit', $row->id) . '" target="_blank"><i class="bi bi-pencil-square"></i> ' . trans('clients.edit_clients') . '</a></li>';
                    }

                    // if (auth()->user()->can('delete_client')) {
                    //     $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item text-danger" onclick="return confirm(\'' . trans('clients.confirm_delete') . '\')" href="' . route('admin.delete_client', $row->id) . '"><i class="bi bi-trash-fill"></i> ' . trans('clients.client_delete') . '</a></li>';
                    // }

                    // if (auth()->user()->can('view_client_paid_invoices')) {
                    $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item" href="' . route('admin.client_paid_invoices', $row->id) . '" target="_blank"><i class="bi bi-currency-dollar"></i> ' . trans('clients.client_invoices') . '</a></li>';
                    // }

                    // if (auth()->user()->can('view_client_unpaid_invoices')) {
                    //     $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item" href="' . route('admin.client_unpaid_invoices', $row->id) . '"><i class="bi bi-receipt-cutoff"></i> ' . trans('clients.client_unpaid_invoices') . '</a></li>';
                    // }

                    if (auth()->user()->can('add_client_invoice')) {
                        $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item" href="' . route('admin.client_invoices', $row->id) . '" target="_blank"><i class="bi bi-file-earmark-plus"></i> ' . trans('clients.client_add_invoice') . '</a></li>';
                    }

                    $actionButtons .= '</ul>';
                    $actionButtons .= '</div>';

                    return $actionButtons;
                })
                ->rawColumns(['subscription', 'action', 'status', 'sas4_status'])
                ->make(true);
        }
        return view($this->admin_view . '.index');
    }

    /***********************************************/
    public function create()
    {
        $data['client_code'] = $this->ClientsRepository->getLastFieldValue('client_code');
        $data['subscriptions'] = $this->SubscriptionRepository->getAll();
        return view($this->admin_view . '.form', $data);
    }

    /***********************************************/
    public function store(SaveRequests $request)
    {
        try {
            $client = $this->clientService->store($request);
            $this->handleSas4Operations($client, $request);
            $notificationMessage = sprintf(
                'تم إنشاء عميل جديد: %s - النوع: %s - السعر: %s %s - تم الإنشاء بواسطة %s',
                $client->name,
                $client->client_type,
                number_format($client->price, 2),
                get_app_config_data('currency'),
                auth()->user()->name
            );

            log_helper(
                'client_created',
                $notificationMessage,
                [
                    'model' => $client
                ]
            );
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.clients.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /***********************************************/
    public function show(string $id)
    {
        //
    }

    /***********************************************/
    public function edit(string $id)
    {
        $data['subscriptions'] = $this->SubscriptionRepository->getAll();
        $data['all_data'] =  $this->ClientsRepository->getById($id);
        return view($this->admin_view . '.edit', $data);
    }
    /***********************************************/
    public function update(UpdateRequests $request, string $id)
    {
        try {
            $oldClientData = $this->ClientsRepository->getById($id)->toArray();
            $this->clientService->update($request, $id);
            $client = $this->ClientsRepository->getById($id);
            $this->handleSas4Operations($client, $request);
            $notificationMessage = sprintf(
                'تم تحديث بيانات العميل: %s - تم التحديث بواسطة %s',
                $client->name,
                auth()->user()->name
            );

            log_helper(
                'client_updated',
                $notificationMessage,
                [
                    'model' => $client,
                    'old_data' => $oldClientData,
                    'new_data' => $client->toArray()
                ]
            );

            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.clients.index');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /***********************************************/
    public function destroy(string $id)
    {
        try {
            $client = $this->ClientsRepository->getById($id);
            $this->ClientsRepository->delete($id);
            $notificationMessage = sprintf(
                'تم حذف العميل: %s - تم الحذف بواسطة %s',
                $client->name,
                auth()->user()->name
            );

            log_helper(
                'client_deleted',
                $notificationMessage,
                [
                    'model' => $client
                ]
            );
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.clients.index');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function get_price($id)
    {
        $subscription = $this->SubscriptionRepository->getById($id);

        if ($subscription) {
            return response()->json(['price' => $subscription->price]);
        }

        return response()->json(['price' => '0']);
    }

    /***********************************************/

    public function client_unpaid_invoices($id)
    {
        $data['all_data'] = $this->ClientsRepository->getById($id);
        $data['unpaid_data'] = Invoice::with(['client', 'employee', 'subscription'])
            ->where('client_id', $id)
            ->where('status', 'unpaid')
            ->get();
        $data['paid_data'] = Invoice::with(['client', 'employee', 'subscription'])
            ->where('client_id', $id)
            ->whereIn('status', ['paid', 'partial'])
            ->get();
        // dd($data);
        return view($this->admin_view . '.client_unpaid_invoices', $data);
    }
    /***********************************************/
    public function client_paid_invoices($id)
    {
        $data['all_data'] = $this->ClientsRepository->getById($id);
        $data['unpaid_data'] = Invoice::with(['client', 'employee', 'subscription'])
            ->where('client_id', $id)
            ->where('status', 'unpaid')
            ->get();
        $data['paid_data'] = Invoice::with(['client', 'employee', 'subscription'])
            ->where('client_id', $id)
            ->whereIn('status', ['paid', 'partial'])
            ->get();
        $data['total_unpaid'] = $data['unpaid_data']->sum('amount');
        $data['total_paid'] = $data['paid_data']->sum('paid_amount');
        // dd($data);
        return view($this->admin_view . '.invoices.invoices', $data);
    }
    /***********************************************/
    public function client_invoices($id)
    {
        $data['all_data'] = $this->ClientsRepository->getById($id);
        $data['unpaid_data'] = Invoice::where('client_id', $id)
            ->where('status', 'unpaid')
            ->get();
        $data['paid_data'] = Invoice::where('client_id', $id)
            ->whereIn('status', ['paid', 'partial'])
            ->get();
        // $data['invoiceNumber'] = $this->InvoiceRepository->getLastFieldValue('invoice_number');
        $lastInvoice = Invoice::withTrashed()->orderBy('id', 'desc')->first();
        $data['invoiceNumber'] = $lastInvoice->invoice_number + 1;
        $data['subscriptions'] = $this->SubscriptionRepository->getAll();

        $data['total_unpaid'] = $data['unpaid_data']->sum('amount');
        $data['total_paid'] = $data['paid_data']->sum('paid_amount');
        // dd($data);
        return view($this->admin_view . '.add_invoice.add_invoice', $data);
    }
    /***********************************************/
    public function client_add_invoice(Request $request, $id)
    {
        $request->validate([
            'invoice_number' => 'required|string|unique:tbl_invoices,invoice_number',
            'invoice_type' => 'required|in:service,subscription',
            'subscription_id' => 'nullable|exists:tbl_subscriptions,id',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date',
            'status' => 'required|in:paid,unpaid',
            // 'remaining_amount' => 'nullable|numeric|min:0|max:' . $request->amount,
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            // if ($request->remaining_amount == 0) {
            //     $status = 'paid';
            // } elseif ($request->remaining_amount > 0 && $request->remaining_amount < $request->amount) {
            //     $status = 'partial';
            // } else {
            //     $status = 'unpaid';
            // }

            // $remainingAmount = $request->remaining_amount ?? 0;
            // $paidAmount = $request->amount - $remainingAmount;

            $remainingAmount = $request->status === 'paid' ? 0.00 : $request->amount;
            $paidAmount = $request->status === 'paid' ? $request->amount : 0.00;
            $paidDate = $request->status === 'paid' ? now() : null;

            $invoiceData = [
                'invoice_number' => $request->invoice_number,
                'client_id' => $id,
                'subscription_id' => $request->invoice_type === 'subscription' ? $request->subscription_id : null,
                'amount' => $request->amount,
                'remaining_amount' => $remainingAmount,
                'paid_amount' => $paidAmount,
                'enshaa_date' => now()->format('Y-m-d'),
                'invoice_type' => $request->invoice_type,
                'notes' => $request->notes,
                'paid_date' => $paidDate,
                'due_date' => Carbon::parse($request->due_date)->format('Y-m-d'),
                'created_by' => auth()->user()->id,
                'status' => $request->status,
                'auto_generated' => $request->invoice_type === 'subscription'
            ];

            // dd($invoiceData);

            $invoice = $this->InvoiceRepository->create($invoiceData);

            // if ($request->remaining_amount < $request->amount) {
            // $admin = auth()->user();
            // $collectedBy = $admin && $admin->is_employee ? $admin->emp_id : auth()->user()->id;

            $creationMessage = sprintf(
                'تم إنشاء فاتورة جديدة رقم %s للعميل %s بمبلغ %s %s (تمت العملية بواسطة: %s)',
                $invoice->invoice_number,
                $invoice->client->name ?? 'غير محدد',
                number_format($invoice->amount, 2),
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
                $admin->notify(new InvoiceCreatedNotification(
                    $invoice,
                    auth()->user(),
                    $creationMessage
                ));
            }

            if (!empty($admins)) {
                sendOneSignalNotification1(
                    $admins,
                    $creationMessage,
                    [
                        'invoice_id' => $invoice->id,
                        'type' => 'invoice_created',
                        'amount' => $invoice->amount,
                        'initiator' => auth()->user()->name,
                        'invoice_details' => [
                            'number' => $invoice->invoice_number,
                            'type' => $invoice->invoice_type,
                            'client' => $invoice->client->name ?? 'Unknown'
                        ]
                    ],
                    null
                );
            }

            sendTelegramNotification($creationMessage, 'invoice_created');

            log_helper(
                'invoice_created',
                $creationMessage,
                [
                    'model' => $invoice
                ]
            );

            if ($request->status === 'paid') {
                Revenue::create([
                    'invoice_id' => $invoice->id,
                    'client_id' => $id,
                    'amount' => $request->amount,
                    'collected_by' => auth()->id(),
                    'status' => 'paid',
                    'remaining_amount' => 0,
                    'received_at' => now(),
                ]);

                $accountId = auth()->user()->account_id ?? null;
                if ($accountId) {
                    FinancialTransaction::create([
                        'account_id'    => $accountId,
                        'amount'        => $request->amount,
                        'date'          => now()->toDateString(),
                        'time'          => now()->toTimeString(),
                        'month'         => now()->month,
                        'year'          => now()->year,
                        'notes'         => 'سداد مستحقات الفاتورة رقم #' . $invoice->invoice_number,
                        'type'          => 'qapd',
                        'created_by'    => auth()->id(),
                    ]);
                }

                $paymentType = '';
                if ($request->invoice_type === 'subscription') {
                    $paymentType = ' (دفعة مقدمة للاشتراك)';
                } else {
                    $paymentType = ' (دفعة مقابل خدمة)';
                }

                $notificationMessage = sprintf(
                    'تم دفع مبلغ %s %s%s للعميل %s، %s. (تمت العملية بواسطة: %s)',
                    number_format($request->amount, 2),
                    get_app_config_data('currency'),
                    $paymentType,
                    $invoice->client->name ?? 'غير محدد',
                    $request->invoice_type === 'subscription' ?
                        'لاشتراك ' . ($invoice->subscription->name ?? '') :
                        'لخدمة ' . ($invoice->notes ?? ''),
                    auth()->user()->name
                );

                $admins = Admin::where('status', '1')
                    ->whereNull('deleted_at')
                    ->whereHas('roles', function($query) {
                        $query->whereIn('id', [1, 7]);
                    })
                    ->get();

                foreach ($admins as $admin) {
                    $admin->notify(new InvoicePaidNotification(
                        $invoice,
                        $request->amount,
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
                            'amount' => $request->amount,
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

                log_helper(
                    'invoice_paid',
                    $notificationMessage,
                    [
                        'model' => $invoice,
                        'amount' => $invoice->amount
                    ]
                );
            }

            DB::commit();

            return redirect()->route('admin.client_paid_invoices', $id)->with('success', trans('clients.invoice_created_successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', trans('clients.failed_to_create_invoice.'));
        }
    }

    public function change_status($id, $status)
    {
        try {
            $client = $this->ClientsRepository->getById($id);
            if ($client) {
                $data['is_active'] = $status == '1' ? '0' : '1';
                $newStatus = $data['is_active'];

                $this->ClientsRepository->update($id, $data);
                
                // إذا كان الطلب AJAX، أعد JSON response
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => trans('users.status_changed_successfully'),
                        'new_status' => $newStatus,
                        'new_status_text' => $newStatus == '1' ? trans('clients.active') : trans('clients.inactive')
                    ]);
                }
                
                toastr()->addSuccess(trans('users.status_changed_successfully'));
                return redirect()->route('admin.clients.index');
            }
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => trans('clients.client_not_found')
                ], 404);
            }
            
            return redirect()->route('admin.clients.index');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            test($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**============================================================== */

    public function showImportForm()
    {
        $lastClientCode = $this->ClientsRepository->getLastFieldValue('client_code');

        $subscriptions = $this->SubscriptionRepository->getAll();

        $defaultSubscriptionDate = now()->format('Y-m-d');
        return view('dashbord.clients.import', [
            'last_client_code' => $lastClientCode,
            'subscriptions' => $subscriptions,
            'default_subscription_date' => $defaultSubscriptionDate,
        ]);
    }
    public function import1(ImportClientsRequest $request)
    {
        set_time_limit(300);
        try {
            $subscriptionDate = $request->input('subscription_date');
            $file = $request->file('file');

            $import = new ClientsImport($subscriptionDate);
            Excel::import($import, $file);

            $successCount = $import->getSuccessCount();
            $failures = $import->getFailures();

            if (count($failures) > 0) {
                return redirect()
                    ->back()
                    ->with('failures', $failures)
                    ->with('success_count', $successCount)
                    ->with('success', trans('clients.import_partial_success', ['count' => $successCount]));
            }

            toastr()->addSuccess(trans('clients.import_success', ['count' => $successCount]));
            return redirect()->route('admin.clients.index');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => trans('clients.import_error') . $e->getMessage()]);
        }
    }

    public function import2(ImportClientsRequest $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        try {
            $subscriptionDate = $request->input('subscription_date');
            $file = $request->file('file');

            $this->validateImportFile($file);

            $import = new ClientsImport($subscriptionDate);

            Excel::import($import, $file);

            $successCount = $import->getSuccessCount();
            $failures = $import->getFailures();

            Log::info('Client import completed', [
                'success_count' => $successCount,
                'failure_count' => count($failures),
                'user_id' => auth()->id()
            ]);

            if (count($failures) > 0) {
                return redirect()
                    ->back()
                    ->with('failures', $failures)
                    ->with('success_count', $successCount)
                    ->with('warning', trans('clients.import_partial_success', ['count' => $successCount]));
            }

            toastr()->addSuccess(trans('clients.import_success', ['count' => $successCount]));
            return redirect()->route('admin.clients.index');

        } catch (\Exception $e) {
            Log::error('Client import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => trans('clients.import_error') . ': ' . $e->getMessage()]);
        }
    }
    public function import(ImportClientsRequest $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        try {
            $subscriptionDate = $request->input('subscription_date');
            $file = $request->file('file');

            // Log::info('Starting import process', [
            //     'file_name' => $file->getClientOriginalName(),
            //     'file_size' => $file->getSize(),
            //     'file_extension' => $file->getClientOriginalExtension(),
            //     'subscription_date' => $subscriptionDate
            // ]);

            // $reader = Excel::toCollection(null, $file);
            // Log::info('File contents debug', [
            //     'sheets_count' => $reader->count(),
            //     'first_sheet_rows' => $reader->first()->count(),
            //     'first_5_rows' => $reader->first()->take(5)->toArray()
            // ]);

            $import = new ClientsImport($subscriptionDate);
            Excel::import($import, $file);

            $successCount = $import->getSuccessCount();
            $failures = $import->getFailures();

            $notificationMessage = sprintf(
                'تم استيراد %s عميل بنجاح - %s فشل في الاستيراد - تم الاستيراد بواسطة %s',
                $successCount,
                count($failures),
                auth()->user()->name
            );

            log_helper(
                'clients_imported',
                $notificationMessage,
                [
                    'count' => $successCount
                ]
            );

            // Log::info('Import completed', [
            //     'success_count' => $successCount,
            //     'failure_count' => count($failures),
            //     'failures' => $failures
            // ]);

            if (count($failures) > 0) {
                // dd($failures);
                return redirect()
                    ->back()
                    ->with('failures', $failures)
                    ->with('success_count', $successCount)
                    ->with('warning', trans('clients.import_partial_success', [
                        'success_count' => $successCount,
                        'failure_count' => count($failures)
                    ]));
            }

            return redirect()
                ->route('admin.clients.index')
                ->with('success', trans('clients.import_success', [
                    'count' => $successCount
                ]));

        } catch (\Exception $e) {
            // Log::error('Import failed', [
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString()
            // ]);

            // return redirect()
            //     ->back()
            //     ->withErrors(['error' => trans('clients.import_error_message', [
            //         'error' => $e->getMessage()
            //     ])]);
            return redirect()
                ->back()
                ->withErrors(['error' => trans('clients.import_error')]);
        }
    }

    protected function validateImportFile($file)
    {
        $allowedExtensions = ['xlsx', 'xls', 'csv'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('Invalid file format. Only Excel and CSV files are allowed.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \InvalidArgumentException('File size too large. Maximum 10MB allowed.');
        }
    }

    public function getClientDetails($id)
    {
        $client = Clients::with(['subscription', 'invoices'])
            ->withSum('invoices as remaining_amount_total', 'remaining_amount')
            ->findOrFail($id);

        return view($this->admin_view . '.details_modal', compact('client'))->render();
    }

    public function remainingInvoices($id)
    {
        $client = $this->ClientsRepository->getById($id);
        $unpaidInvoices = Invoice::with(['subscription'])
            ->where('client_id', $id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date', 'asc')
            ->get();

        return view($this->admin_view . '.remaining_invoices_modal_content', compact('client', 'unpaidInvoices'))->render();
    }

    public function getSas4Info($id)
    {
        $client = $this->ClientsRepository->getById($id);
        if (!$client || !$client->sas_username) {
            return response()->json(['error' => trans('clients.no_sas4_username_linked')], 404);
        }

        $sas4Service = app(\App\Services\Sas4\Sas4ApiService::class);
        $info = $sas4Service->getUserFullInfo($client->sas_username);

        if (!$info) {
            return response()->json(['error' => trans('clients.sas4_user_not_found')], 404);
        }

        return response()->json($info);
    }

    public function searchSas4Users()
    {
        $query = request('q', '');
        $sas4Service = app(\App\Services\Sas4\Sas4ApiService::class);
        $result = $sas4Service->searchUsers($query);

        return response()->json($result ?? ['data' => []]);
    }

    public function getSas4OnlineStatus()
    {
        $usernames = request('usernames', []);
        if (empty($usernames)) {
            return response()->json([]);
        }

        $sas4Service = app(\App\Services\Sas4\Sas4ApiService::class);
        $statusMap = [];

        foreach ($usernames as $username) {
            $user = $sas4Service->getUserByUsername($username);
            if ($user && isset($user['data'])) {
                $userData = $user['data'];
                $statusMap[$username] = [
                    'online' => $userData['online_status'] ?? 0,
                    'enabled' => $userData['enabled'] ?? 0,
                    'expiration' => $userData['expiration'] ?? '',
                ];
            }
        }

        return response()->json($statusMap);
    }

    public function getSas4Profiles()
    {
        $sas4Service = app(\App\Services\Sas4\Sas4ApiService::class);
        $result = $sas4Service->getProfiles();

        return response()->json($result ?? ['data' => []]);
    }

    public function sas4Control($id)
    {
        $client = $this->ClientsRepository->getById($id);
        if (!$client || !$client->sas_username) {
            return response()->json(['success' => false, 'message' => trans('clients.no_sas4_username_linked')], 404);
        }

        $action = request('action');
        $profileId = request('profile_id');

        $sas4Service = app(\App\Services\Sas4\Sas4ApiService::class);
        $sas4User = $sas4Service->getUserByUsername($client->sas_username);

        if (!$sas4User || !isset($sas4User['data']['id'])) {
            return response()->json(['success' => false, 'message' => trans('clients.sas4_user_not_found')], 404);
        }

        $sas4UserId = $sas4User['data']['id'];

        $result = null;
        $message = '';

        switch ($action) {
            case 'enable':
                $result = $sas4Service->enableUser($sas4UserId);
                $message = trans('clients.sas4_enabled_success');
                break;
            case 'disable':
                $result = $sas4Service->disableUser($sas4UserId);
                $message = trans('clients.sas4_disabled_success');
                break;
            case 'disconnect':
                $result = $sas4Service->disconnectUser($sas4UserId);
                $message = trans('clients.sas4_disconnected_success');
                break;
            case 'change_profile':
                if (!$profileId) {
                    return response()->json(['success' => false, 'message' => trans('clients.sas4_profile_required')], 400);
                }
                $expirationDate = request('expiration_date');
                if ($expirationDate) {
                    $result = $sas4Service->changeProfileAndExpiration($sas4UserId, $profileId, $expirationDate);
                } else {
                    $result = $sas4Service->changeProfile($sas4UserId, $profileId);
                }
                $message = trans('clients.sas4_profile_changed_success');
                break;
            default:
                return response()->json(['success' => false, 'message' => trans('clients.sas4_invalid_action')], 400);
        }

        if ($result && isset($result['status']) && $result['status'] == 200) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return response()->json([
            'success' => false,
            'message' => trans('clients.sas4_action_failed')
        ], 500);
    }

    protected function handleSas4Operations($client, $request)
    {
        $sas4Action = $request->input('sas4_action');
        $sas4Mode = $request->input('sas4_mode', 'link');

        if ($sas4Action === 'unlink') {
            $client->sas_username = null;
            $client->save();
            return;
        }

        if ($sas4Mode === 'create') {
            $newUsername = $request->input('sas4_new_username');
            $newPassword = $request->input('sas4_new_password');
            $newProfile = $request->input('sas4_new_profile');

            if (!$newUsername || !$newPassword || !$newProfile) {
                return;
            }

            $sas4Service = app(\App\Services\Sas4\Sas4ApiService::class);

            if ($sas4Service->usernameExists($newUsername)) {
                return;
            }

            $result = $sas4Service->createUser($newUsername, $newPassword, $newProfile, $client->name);

            if ($result && isset($result['status']) && $result['status'] == 200) {
                $client->sas_username = $newUsername;
                $client->save();
            }
        } elseif ($sas4Mode === 'link' || $sas4Action === 'link') {
            $sasUsername = $request->input('sas_username');
            if ($sasUsername) {
                $client->sas_username = $sasUsername;
                $client->save();
            }
        }
    }

}
