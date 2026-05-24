<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ClientsRemainingExport;
use App\Http\Controllers\Controller;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\Invoice;
use App\Models\Admin\Revenue;
use App\Models\Admin\Subscription;
use App\Models\Clients;
use App\Services\InvoiceService;
use App\Services\ReportService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $admin_view = 'dashbord.reports';
    protected $ClientsRepository;
    protected $invoiceService;
    protected $SubscriptionRepository;
    protected $InvoiceRepository;
    protected $reportService;

    public function __construct(BasicRepositoryInterface $basicRepository, ReportService $reportService)
    {
        $this->middleware('can:view_reports')->only('reports');
        // $this->middleware('can:generate_reports')->only('index');

        $this->InvoiceRepository = createRepository($basicRepository, new Invoice());
        $this->SubscriptionRepository = createRepository($basicRepository, new Subscription());
        $this->ClientsRepository = createRepository($basicRepository, new Clients());
        $this->reportService = $reportService;
    }

    public function reports()
    {
        $clients = $this->ClientsRepository->getAll();

        return view($this->admin_view . '.index', compact('clients'));
    }

    public function clientsRemaining()
    {
        return view($this->admin_view . '.clients_remaining');
    }

    public function unpaidInvoicesReport()
    {
        $clients = $this->ClientsRepository->getAll();

        return view($this->admin_view . '.unpaid_invoices', compact('clients'));
    }

    public function unpaidInvoicesReportData(Request $request)
    {
        if ($request->ajax()) {
            $allData = $this->reportService->getUnpaidInvoices($request);
            $data = $allData['invoices'];
            $totals = $allData['totals'];

            return Datatables::of($data)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('invoice_number', function ($row) {
                    $prefix = $row->client && $row->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                    if ($row->invoice_number) {
                        return '<a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $row->id) . '\')"
                                class="text-primary fw-bold" style="text-decoration: underline;" title="' . trans('invoices.view_details') . '">
                                ' . $prefix . ($row->invoice_number ?? 'N/A') . '
                            </a>';
                    }

                    return 'N/A';
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
                    $latestRevenue = $row->revenues->sortByDesc('created_at')->first();

                    if ($latestRevenue && $latestRevenue->user) {
                        return $latestRevenue->user->name;
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
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';

                    if (($row->status == 'unpaid' || $row->status == 'partial') && auth()->user()->can('pay_invoice')) {
                        $buttons .= '
                            <a href="javascript:void(0)" onclick="showPayModal(\'' . route('admin.pay_invoice', $row->id) . '\', ' . $row->remaining_amount . ', ' . $row->amount . ', `'.str_replace('`', '\`', $row->notes ?? '').'`, `' . ($row->paid_date ?? '') . '`)"
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
                ->with('totals', $totals)
                ->rawColumns(['subscription', 'status', 'client', 'month_year', 'invoice_number', 'action'])
                ->make(true);
        }
    }

    public function clientsRemainingData(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->reportService->getClientsOutstandingQuery($request);
            $totals = $this->reportService->getClientsOutstandingTotals($request);
            $currency = get_app_config_data('currency');

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return '<a href="' . route('admin.clients.details', $row->id) . '" class="text-primary fw-bold" target="_blank">'
                        . e($row->name) . '</a>';
                })
                ->editColumn('phone', function ($row) {
                    return $row->phone ?? 'N/A';
                })
                ->editColumn('client_type', function ($row) {
                    $key = 'clients.' . ($row->client_type ?? '');
                    return trans()->has($key) ? trans($key) : ($row->client_type ?? 'N/A');
                })
                ->addColumn('subscription', function ($row) {
                    return $row->subscription_name ?? 'N/A';
                })
                ->editColumn('total_amount', function ($row) use ($currency) {
                    return '<span class="text-muted fw-semibold">' . number_format($row->total_amount ?? 0, 2) . ' ' . $currency . '</span>';
                })
                ->editColumn('total_paid', function ($row) use ($currency) {
                    return '<span class="text-success fw-semibold">' . number_format($row->total_paid ?? 0, 2) . ' ' . $currency . '</span>';
                })
                ->editColumn('total_remaining', function ($row) use ($currency) {
                    return '<span class="text-danger fw-bold">' . number_format($row->total_remaining ?? 0, 2) . ' ' . $currency . '</span>';
                })
                ->editColumn('latest_due_date', function ($row) {
                    return $row->latest_due_date
                        ? Carbon::parse($row->latest_due_date)->format('Y-m-d')
                        : 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $isActive = $row->is_active == '1';
                    $class = $isActive ? 'badge bg-success text-white' : 'badge bg-danger text-white';
                    $label = $isActive ? trans('clients.active') : trans('clients.inactive');

                    return '<span class="' . $class . ' px-3 py-2 rounded-pill fw-bold">' . $label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';
                    $buttons .= '<a href="' . route('admin.client_paid_invoices', $row->id) . '" target="_blank" class="btn btn-secondary" title="' . trans('clients.client_invoices') . '">'
                        . '<i class="bi bi-currency-dollar"></i></a>';
                    $buttons .= '<a href="' . route('admin.clients.edit', $row->id) . '" target="_blank" class="btn btn-info text-white" title="' . trans('clients.edit_clients') . '">'
                        . '<i class="bi bi-pencil-square"></i></a>';
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->with('totals', [
                    'total_clients' => (int)($totals->clients_count ?? 0),
                    'total_amount' => number_format($totals->total_amount ?? 0, 2),
                    'total_paid' => number_format($totals->total_paid ?? 0, 2),
                    'total_remaining' => number_format($totals->total_remaining ?? 0, 2),
                    'total_invoices' => (int)($totals->invoices_count ?? 0),
                ])
                ->rawColumns(['name', 'total_amount', 'total_paid', 'total_remaining', 'status', 'action'])
                ->make(true);
        }
    }

    public function clientsRemainingExportExcel(Request $request)
    {
        $data = $this->reportService->getClientsOutstandingCollection($request);
        $fileName = 'clients_remaining_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ClientsRemainingExport($data, get_app_config_data('currency')), $fileName);
    }

    public function clientsRemainingPrint(Request $request)
    {
        $rows = $this->reportService->getClientsOutstandingCollection($request);
        $totals = $this->reportService->getClientsOutstandingTotals($request);

        return view($this->admin_view . '.exports.clients_remaining_print', [
            'rows' => $rows,
            'totals' => $totals,
            'currency' => get_app_config_data('currency'),
            'filters' => [
                'month' => $request->input('month'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'sort_direction' => $request->input('sort_direction'),
                'status_filter' => $request->input('status_filter'),
            ],
        ]);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get optimized query from service
            $query = $this->reportService->getFilteredInvoicesQuery($request);
            
            // Get totals using separate optimized query
            $totals = $this->reportService->getTotals($request);

            return Datatables::of($query)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('invoice_number', function ($row) {
                    // return $row->invoice_number ?? 'N/A';
                    // $prefix = $row->client && $row->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                    // return $prefix . $row->invoice_number;
                    $prefix = $row->client && $row->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                    if ($row->invoice_number) {
                        return '<a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $row->id) . '\')"
                                class="text-primary fw-bold" style="text-decoration: underline;" title="' . trans('invoices.view_details') . '">
                                ' . $prefix . ($row->invoice_number ?? 'N/A') . '
                            </a>';
                    }

                    return 'N/A';
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
                ->with('totals', $totals)
                ->rawColumns(['subscription', 'status', 'client', 'invoice_number', 'action'])
                ->make(true);
        }
    }

    public function dailyReportByUsers()
    {
        return view($this->admin_view . '.daily_report_by_users');
    }

    public function dailyReportByUsersData(Request $request)
    {
        if ($request->ajax()) {
            $reportData = $this->reportService->getDailyReportByUsers($request);
            return response()->json($reportData);
        }
    }

    public function monthlyReportByUsers()
    {
        return view($this->admin_view . '.monthly_report_by_users');
    }

    public function monthlyReportByUsersData(Request $request)
    {
        if ($request->ajax()) {
            $reportData = $this->reportService->getMonthlyReportByUsers($request);
            return response()->json($reportData);
        }
    }

    public function comprehensiveReportByUsers()
    {
        return view($this->admin_view . '.comprehensive_report_by_users');
    }

    public function comprehensiveReportByUsersData(Request $request)
    {
        if ($request->ajax()) {
            $reportData = $this->reportService->getComprehensiveReportByUsers($request);
            return response()->json($reportData);
        }
    }

    public function overdueInvoicesReport()
    {
        $clients = $this->ClientsRepository->getAll();
        return view($this->admin_view . '.overdue_invoices', compact('clients'));
    }

    public function overdueInvoicesReportData(Request $request)
    {
        if ($request->ajax()) {
            // Use query directly for DataTables server-side processing
            $query = $this->reportService->getOverdueInvoicesQuery($request);
            
            // Get totals separately for better performance
            $today = Carbon::today()->format('Y-m-d');
            $totalsQuery = Invoice::whereNull('tbl_invoices.deleted_at')
                ->where('tbl_invoices.status', 'unpaid')
                ->whereDate('tbl_invoices.due_date', '<', $today);

            if ($request->filled('client_id')) {
                $totalsQuery->where('tbl_invoices.client_id', $request->client_id);
            }
            if ($request->filled('type')) {
                $totalsQuery->where('tbl_invoices.invoice_type', $request->type);
            }
            if ($request->filled('from_date')) {
                $totalsQuery->whereDate('tbl_invoices.due_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $totalsQuery->whereDate('tbl_invoices.due_date', '<=', $request->to_date);
            }

            $totals = $totalsQuery->selectRaw('
                COUNT(*) as total_invoices,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(remaining_amount), 0) as total_remaining,
                COALESCE(SUM(paid_amount), 0) as total_paid
            ')->first();

            $todayCarbon = Carbon::parse($today);
            
            return Datatables::of($query)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('invoice_number', function ($row) {
                    $prefix = $row->client && $row->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                    if ($row->invoice_number) {
                        return '<a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $row->id) . '\')"
                                class="text-primary fw-bold" style="text-decoration: underline;" title="' . trans('invoices.view_details') . '">
                                ' . $prefix . ($row->invoice_number ?? 'N/A') . '
                            </a>';
                    }
                    return 'N/A';
                })
                ->addColumn('client', function ($row) {
                    if ($row->client) {
                        $url = route('admin.client_paid_invoices', $row->client->id);
                        return '<a href="' . $url . '" class="text-primary fw-bold" style="text-decoration: underline;">' . $row->client->name . '</a>';
                    }
                    return 'N/A';
                })
                ->addColumn('amount', function ($row) {
                    return number_format($row->amount ?? 0, 2);
                })
                ->addColumn('paid_amount', function ($row) {
                    return number_format($row->paid_amount ?? 0, 2);
                })
                ->addColumn('remaining_amount', function ($row) {
                    return number_format($row->remaining_amount ?? 0, 2);
                })
                ->addColumn('due_date', function ($row) {
                    return $row->due_date ?? 'N/A';
                })
                ->addColumn('days_overdue', function ($row) use ($todayCarbon) {
                    $dueDate = Carbon::parse($row->due_date);
                    $days = $dueDate->diffInDays($todayCarbon);
                    $badgeClass = $days > 30 ? 'bg-danger' : ($days > 15 ? 'bg-warning' : 'bg-info');
                    return '<span class="badge ' . $badgeClass . ' text-white px-3 py-2 rounded-pill fw-bold">' . $days . ' ' . trans('reports.days') . '</span>';
                })
                ->addColumn('subscription', function ($row) {
                    return $row->subscription ? $row->subscription->name : '<span class="badge bg-success text-white px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.service') . '</span>';
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge bg-danger text-white px-4 py-3 rounded-pill fw-bold fs-5">' . trans('invoices.unpaid') . '</span>';
                })
                ->with('totals', [
                    'total_invoices' => $totals->total_invoices ?? 0,
                    'total_amount' => $totals->total_amount ?? 0,
                    'total_remaining' => $totals->total_remaining ?? 0,
                    'total_paid' => $totals->total_paid ?? 0,
                ])
                ->rawColumns(['subscription', 'status', 'client', 'invoice_number', 'days_overdue'])
                ->make(true);
        }
    }

    public function profitAndLossReport()
    {
        return view($this->admin_view . '.profit_and_loss');
    }

    public function profitAndLossReportData(Request $request)
    {
        if ($request->ajax()) {
            $reportData = $this->reportService->getProfitAndLossReport($request);
            return response()->json($reportData);
        }
    }

    public function expensesByBandsAndMonthsReport()
    {
        return view($this->admin_view . '.expenses_by_bands_and_months');
    }

    public function expensesByBandsAndMonthsReportData(Request $request)
    {
        if ($request->ajax()) {
            $reportData = $this->reportService->getExpensesByBandsAndMonths($request);
            return response()->json($reportData);
        }
    }

    public function revenuesByUsersAndMonthsReport()
    {
        return view($this->admin_view . '.revenues_by_users_and_months');
    }

    public function revenuesByUsersAndMonthsReportData(Request $request)
    {
        if ($request->ajax()) {
            $reportData = $this->reportService->getRevenuesByUsersAndMonths($request);
            return response()->json($reportData);
        }
    }
}
