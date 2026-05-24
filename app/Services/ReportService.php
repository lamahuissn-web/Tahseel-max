<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\Invoice;
use App\Models\Admin\Masrofat;
use App\Models\Admin\Revenue;
use App\Models\Admin\SarfBand;
use App\Models\Admin\Subscription;
use App\Models\Admin as AdminModel;
use App\Models\Admin\Employee as AdminEmployee;
use App\Models\Clients;
use App\Models\hr\Employee;
use App\Traits\ImageProcessing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{

    use ImageProcessing;
    protected $InvoiceRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->InvoiceRepository   = createRepository($basicRepository, new Invoice());
    }

    public function getFilteredInvoicesQuery(Request $request)
    {
        // Use select to limit columns and optimize query
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
            'tbl_invoices.invoice_type',
            'tbl_invoices.enshaa_date',
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

        if ($request->filled('client_id')) {
            $query->where('tbl_invoices.client_id', $request->client_id);
        }

        if ($request->filled('type')) {
            $query->where('tbl_invoices.invoice_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('tbl_invoices.status', $request->status);
        }

        if ($request->filled('month')) {
            $monthYear = Carbon::parse($request->month);
            $query->whereMonth('tbl_invoices.enshaa_date', $monthYear->month)
                ->whereYear('tbl_invoices.enshaa_date', $monthYear->year);
        }

        if ($request->filled('from_date')) {
            $query->where('tbl_invoices.enshaa_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('tbl_invoices.enshaa_date', '<=', $request->to_date);
        }

        return $query;
    }

    public function getTotals(Request $request)
    {
        // Get totals using a separate optimized query
        $query = Invoice::selectRaw('
            COALESCE(SUM(CASE WHEN status IN (\'paid\', \'partial\') THEN paid_amount ELSE 0 END), 0) as paid_total,
            COALESCE(SUM(CASE WHEN status = \'unpaid\' THEN amount ELSE 0 END), 0) as unpaid_total
        ')
        ->whereNull('deleted_at');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('type')) {
            $query->where('invoice_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $monthYear = Carbon::parse($request->month);
            $query->whereMonth('enshaa_date', $monthYear->month)
                ->whereYear('enshaa_date', $monthYear->year);
        }

        if ($request->filled('from_date')) {
            $query->where('enshaa_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('enshaa_date', '<=', $request->to_date);
        }

        $result = $query->first();

        return [
            'paid' => number_format($result->paid_total ?? 0, 2),
            'unpaid' => number_format($result->unpaid_total ?? 0, 2),
        ];
    }

    protected function buildClientsOutstandingSubQuery(Request $request)
    {
        $query = Invoice::select('client_id')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('SUM(paid_amount) as total_paid')
            ->selectRaw('SUM(remaining_amount) as total_remaining')
            ->selectRaw('COUNT(*) as invoices_count')
            ->selectRaw('MAX(due_date) as latest_due_date')
            ->whereNull('tbl_invoices.deleted_at')
            ->whereIn('status', ['unpaid', 'partial']);

        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        if ($request->filled('month')) {
            try {
                $monthYear = Carbon::createFromFormat('Y-m', $request->month);
                $query->whereYear('due_date', $monthYear->year)
                    ->whereMonth('due_date', $monthYear->month);
            } catch (\Exception $e) {
                // ignore invalid month format
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('due_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('due_date', '<=', $request->to_date);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        return $query->groupBy('client_id');
    }

    public function getClientsOutstandingQuery(Request $request)
    {
        $subQuery = DB::query()->fromSub($this->buildClientsOutstandingSubQuery($request), 'invoice_stats');

        $query = Clients::select([
                'tbl_clients.id',
                'tbl_clients.name',
                'tbl_clients.phone',
                'tbl_clients.client_type',
                'tbl_clients.user',
                'tbl_clients.is_active',
                'tbl_subscriptions.name as subscription_name',
            ])
            ->selectRaw('invoice_stats.total_amount')
            ->selectRaw('invoice_stats.total_paid')
            ->selectRaw('invoice_stats.total_remaining')
            ->selectRaw('invoice_stats.invoices_count')
            ->selectRaw('invoice_stats.latest_due_date')
            ->joinSub($subQuery, 'invoice_stats', function ($join) {
                $join->on('invoice_stats.client_id', '=', 'tbl_clients.id');
            })
            ->leftJoin('tbl_subscriptions', 'tbl_subscriptions.id', '=', 'tbl_clients.subscription_id')
            ->whereNull('tbl_clients.deleted_at')
            ->where('invoice_stats.total_remaining', '>', 0);

        if ($request->filled('status_filter') && in_array($request->status_filter, ['0', '1'], true)) {
            $query->where('tbl_clients.is_active', $request->status_filter);
        }

        $sortDirection = strtolower($request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (!$request->has('order.0.column')) {
            $query->orderBy('invoice_stats.total_remaining', $sortDirection);
        }

        return $query;
    }

    public function getClientsOutstandingTotals(Request $request)
    {
        $subQuery = DB::query()->fromSub($this->buildClientsOutstandingSubQuery($request), 'invoice_stats');

        $query = DB::table('tbl_clients')
            ->joinSub($subQuery, 'invoice_stats', function ($join) {
                $join->on('invoice_stats.client_id', '=', 'tbl_clients.id');
            })
            ->whereNull('tbl_clients.deleted_at')
            ->where('invoice_stats.total_remaining', '>', 0);

        if ($request->filled('status_filter') && in_array($request->status_filter, ['0', '1'], true)) {
            $query->where('tbl_clients.is_active', $request->status_filter);
        }

        return $query->selectRaw('
                COUNT(*) as clients_count,
                COALESCE(SUM(invoice_stats.total_amount), 0) as total_amount,
                COALESCE(SUM(invoice_stats.total_paid), 0) as total_paid,
                COALESCE(SUM(invoice_stats.total_remaining), 0) as total_remaining,
                COALESCE(SUM(invoice_stats.invoices_count), 0) as invoices_count
            ')->first();
    }

    public function getClientsOutstandingCollection(Request $request): Collection
    {
        return $this->getClientsOutstandingQuery($request)->get();
    }

    public function getUnpaidInvoices(Request $request)
    {
        // Start with unpaid and partial invoices only
        $query = Invoice::with(['client', 'employee', 'subscription', 'revenues'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNull('deleted_at');

        if ($request->filled('client_id')) {
            $query = $query->where('client_id', $request->client_id);
        }

        if ($request->filled('type')) {
            $query = $query->where('invoice_type', $request->type);
        }

        if ($request->filled('month')) {
            $monthYear = Carbon::parse($request->month);
            $query = $query->whereMonth('enshaa_date', $monthYear->month)
                ->whereYear('enshaa_date', $monthYear->year);
        }

        if ($request->filled('from_date')) {
            $query = $query->where('enshaa_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query = $query->where('enshaa_date', '<=', $request->to_date);
        }

        $allData = $query->orderBy('due_date', 'asc')->get();

        return [
            'invoices' => $allData,
            'totals' => [
                'total_amount' => $allData->sum('amount'),
                'total_unpaid' => $allData->sum('remaining_amount'),
                'total_paid' => $allData->sum('paid_amount'),
            ],
        ];
    }

    /**
     * Get daily report by users
     * @param Request $request
     * @return array
     */
    public function getDailyReportByUsers(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        
        $revenues = Revenue::whereDate('created_at', $date)
            ->whereNull('deleted_at')
            ->select('collected_by')
            ->selectRaw('COUNT(DISTINCT invoice_id) as invoices_count')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('collected_by')
            ->orderBy('total_amount', 'desc')
            ->get();

        $usersData = $revenues->map(function ($item) {
            // Try to find Admin user
            $admin = AdminModel::find($item->collected_by);
            if (!$admin) {
                // Try to find by emp_id
                $admin = AdminModel::where('emp_id', $item->collected_by)->first();
            }
            
            if ($admin) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => $admin->name,
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'admin'
                ];
            }
            
            // Try to find Admin Employee (has first_name and last_name)
            $adminEmployee = AdminEmployee::find($item->collected_by);
            if ($adminEmployee) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => trim(($adminEmployee->first_name ?? '') . ' ' . ($adminEmployee->last_name ?? '')),
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'employee'
                ];
            }
            
            // Try to find HR Employee (has name)
            $hrEmployee = Employee::find($item->collected_by);
            if ($hrEmployee) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => $hrEmployee->name ?? 'Unknown',
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'employee'
                ];
            }
            
            return null;
        })->filter(function ($item) {
            return $item !== null;
        })->values();

        $grandTotal = $usersData->sum('total_amount');
        $grandInvoicesCount = $usersData->sum('invoices_count');

        return [
            'date' => $date,
            'users' => $usersData,
            'totals' => [
                'total_amount' => $grandTotal,
                'total_invoices' => $grandInvoicesCount,
                'users_count' => $usersData->count()
            ]
        ];
    }

    /**
     * Get monthly report by users
     * @param Request $request
     * @return array
     */
    public function getMonthlyReportByUsers(Request $request)
    {
        if ($request->filled('month')) {
            $monthYear = Carbon::parse($request->month);
            $startDate = $monthYear->startOfMonth()->format('Y-m-d');
            $endDate = $monthYear->endOfMonth()->format('Y-m-d');
        } else {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
        
        $revenues = Revenue::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNull('deleted_at')
            ->select('collected_by')
            ->selectRaw('COUNT(DISTINCT invoice_id) as invoices_count')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('collected_by')
            ->orderBy('total_amount', 'desc')
            ->get();

        $usersData = $revenues->map(function ($item) {
            // Try to find Admin user
            $admin = AdminModel::find($item->collected_by);
            if (!$admin) {
                // Try to find by emp_id
                $admin = AdminModel::where('emp_id', $item->collected_by)->first();
            }
            
            if ($admin) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => $admin->name,
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'admin'
                ];
            }
            
            // Try to find Admin Employee (has first_name and last_name)
            $adminEmployee = AdminEmployee::find($item->collected_by);
            if ($adminEmployee) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => trim(($adminEmployee->first_name ?? '') . ' ' . ($adminEmployee->last_name ?? '')),
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'employee'
                ];
            }
            
            // Try to find HR Employee (has name)
            $hrEmployee = Employee::find($item->collected_by);
            if ($hrEmployee) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => $hrEmployee->name ?? 'Unknown',
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'employee'
                ];
            }
            
            return null;
        })->filter(function ($item) {
            return $item !== null;
        })->values();

        $grandTotal = $usersData->sum('total_amount');
        $grandInvoicesCount = $usersData->sum('invoices_count');

        return [
            'month' => $request->filled('month') ? Carbon::parse($request->month)->format('Y-m') : Carbon::now()->format('Y-m'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'users' => $usersData,
            'totals' => [
                'total_amount' => $grandTotal,
                'total_invoices' => $grandInvoicesCount,
                'users_count' => $usersData->count()
            ]
        ];
    }

    /**
     * Get comprehensive report by users (all time)
     * @param Request $request
     * @return array
     */
    public function getComprehensiveReportByUsers(Request $request)
    {
        $query = Revenue::whereNull('deleted_at');

        // Apply date filters if provided
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $revenues = $query->select('collected_by')
            ->selectRaw('COUNT(DISTINCT invoice_id) as invoices_count')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('collected_by')
            ->orderBy('total_amount', 'desc')
            ->get();

        $usersData = $revenues->map(function ($item) {
            // Try to find Admin user
            $admin = AdminModel::find($item->collected_by);
            if (!$admin) {
                // Try to find by emp_id
                $admin = AdminModel::where('emp_id', $item->collected_by)->first();
            }
            
            if ($admin) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => $admin->name,
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'admin'
                ];
            }
            
            // Try to find Admin Employee (has first_name and last_name)
            $adminEmployee = AdminEmployee::find($item->collected_by);
            if ($adminEmployee) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => trim(($adminEmployee->first_name ?? '') . ' ' . ($adminEmployee->last_name ?? '')),
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'employee'
                ];
            }
            
            // Try to find HR Employee (has name)
            $hrEmployee = Employee::find($item->collected_by);
            if ($hrEmployee) {
                return [
                    'user_id' => $item->collected_by,
                    'user_name' => $hrEmployee->name ?? 'Unknown',
                    'invoices_count' => $item->invoices_count,
                    'total_amount' => $item->total_amount,
                    'user_type' => 'employee'
                ];
            }
            
            return null;
        })->filter(function ($item) {
            return $item !== null;
        })->values();

        $grandTotal = $usersData->sum('total_amount');
        $grandInvoicesCount = $usersData->sum('invoices_count');

        return [
            'from_date' => $request->filled('from_date') ? $request->from_date : null,
            'to_date' => $request->filled('to_date') ? $request->to_date : null,
            'users' => $usersData,
            'totals' => [
                'total_amount' => $grandTotal,
                'total_invoices' => $grandInvoicesCount,
                'users_count' => $usersData->count()
            ]
        ];
    }

    /**
     * Get overdue invoices (due_date passed and status is unpaid)
     * @param Request $request
     * @return array
     */
    public function getOverdueInvoicesQuery(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        
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
            'tbl_invoices.invoice_type',
            'tbl_invoices.enshaa_date',
            'tbl_invoices.created_at'
        ])
        ->with(['client:id,name,client_type'])
        ->with(['subscription:id,name'])
        ->whereNull('tbl_invoices.deleted_at')
        ->where('tbl_invoices.status', 'unpaid')
        ->whereDate('tbl_invoices.due_date', '<', $today);

        // Apply filters
        if ($request->filled('client_id')) {
            $query->where('tbl_invoices.client_id', $request->client_id);
        }

        if ($request->filled('type')) {
            $query->where('tbl_invoices.invoice_type', $request->type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('tbl_invoices.due_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('tbl_invoices.due_date', '<=', $request->to_date);
        }

        // Order by due_date DESC (most overdue first)
        $query->orderBy('tbl_invoices.due_date', 'desc');

        return $query;
    }

    public function getOverdueInvoices(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        
        $query = $this->getOverdueInvoicesQuery($request);
        $invoices = $query->get();

        // Calculate totals using aggregation for better performance
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

        // Calculate days overdue for each invoice (more efficient)
        $todayCarbon = Carbon::parse($today);
        $invoicesWithOverdueDays = $invoices->map(function ($invoice) use ($todayCarbon) {
            $dueDate = Carbon::parse($invoice->due_date);
            $invoice->days_overdue = $dueDate->diffInDays($todayCarbon);
            return $invoice;
        });

        return [
            'invoices' => $invoicesWithOverdueDays,
            'totals' => [
                'total_invoices' => $totals->total_invoices ?? 0,
                'total_amount' => $totals->total_amount ?? 0,
                'total_remaining' => $totals->total_remaining ?? 0,
                'total_paid' => $totals->total_paid ?? 0,
            ]
        ];
    }

    /**
     * Get Profit and Loss Report (Revenue - Expenses)
     * @param Request $request
     * @return array
     */
    public function getProfitAndLossReport(Request $request)
    {
        // Build revenue query
        $revenueQuery = Revenue::whereNull('deleted_at');
        $masrofatQuery = Masrofat::query();

        // Apply date filters
        if ($request->filled('from_date')) {
            $revenueQuery->whereDate('created_at', '>=', $request->from_date);
            $masrofatQuery->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $revenueQuery->whereDate('created_at', '<=', $request->to_date);
            $masrofatQuery->whereDate('created_at', '<=', $request->to_date);
        }

        // Apply month filter
        if ($request->filled('month')) {
            $monthYear = Carbon::parse($request->month);
            $revenueQuery->whereMonth('created_at', $monthYear->month)
                ->whereYear('created_at', $monthYear->year);
            $masrofatQuery->whereMonth('created_at', $monthYear->month)
                ->whereYear('created_at', $monthYear->year);
        }

        // Clone queries for totals (to avoid modifying the original queries)
        $revenueTotalQuery = clone $revenueQuery;
        $masrofatTotalQuery = clone $masrofatQuery;

        // Calculate totals using aggregation for better performance
        $revenueTotal = $revenueTotalQuery->selectRaw('COALESCE(SUM(amount), 0) as total')->first()->total ?? 0;
        $masrofatTotal = $masrofatTotalQuery->selectRaw('COALESCE(SUM(value), 0) as total')->first()->total ?? 0;

        // Calculate profit/loss
        $profit = $revenueTotal - $masrofatTotal;

        // Create separate queries for daily breakdown with same filters
        $dailyRevenueQuery = Revenue::whereNull('deleted_at');
        $dailyMasrofatQuery = Masrofat::query();

        // Apply same filters to daily queries
        if ($request->filled('from_date')) {
            $dailyRevenueQuery->whereDate('created_at', '>=', $request->from_date);
            $dailyMasrofatQuery->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $dailyRevenueQuery->whereDate('created_at', '<=', $request->to_date);
            $dailyMasrofatQuery->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('month')) {
            $monthYear = Carbon::parse($request->month);
            $dailyRevenueQuery->whereMonth('created_at', $monthYear->month)
                ->whereYear('created_at', $monthYear->year);
            $dailyMasrofatQuery->whereMonth('created_at', $monthYear->month)
                ->whereYear('created_at', $monthYear->year);
        }

        // Get daily breakdown for chart/table
        $dailyRevenue = $dailyRevenueQuery->selectRaw('
            DATE(created_at) as date,
            COALESCE(SUM(amount), 0) as total_amount,
            COUNT(*) as count
        ')
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get();

        $dailyMasrofat = $dailyMasrofatQuery->selectRaw('
            DATE(created_at) as date,
            COALESCE(SUM(value), 0) as total_amount,
            COUNT(*) as count
        ')
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get();

        // Combine daily data
        $dailyData = [];
        
        // Convert collections to arrays keyed by date for faster lookup
        $revenueByDate = [];
        foreach ($dailyRevenue as $item) {
            $revenueByDate[$item->date] = [
                'total_amount' => $item->total_amount ?? 0,
                'count' => $item->count ?? 0
            ];
        }

        $masrofatByDate = [];
        foreach ($dailyMasrofat as $item) {
            $masrofatByDate[$item->date] = [
                'total_amount' => $item->total_amount ?? 0,
                'count' => $item->count ?? 0
            ];
        }

        // Get all unique dates and sort them
        $allDates = array_unique(array_merge(array_keys($revenueByDate), array_keys($masrofatByDate)));
        sort($allDates);

        // Build daily data array
        foreach ($allDates as $date) {
            $revenue = $revenueByDate[$date] ?? null;
            $masrofat = $masrofatByDate[$date] ?? null;

            $revenueAmount = $revenue ? (float)$revenue['total_amount'] : 0;
            $masrofatAmount = $masrofat ? (float)$masrofat['total_amount'] : 0;

            $dailyData[] = [
                'date' => $date,
                'revenue' => $revenueAmount,
                'revenue_count' => $revenue ? $revenue['count'] : 0,
                'expenses' => $masrofatAmount,
                'expenses_count' => $masrofat ? $masrofat['count'] : 0,
                'profit' => $revenueAmount - $masrofatAmount
            ];
        }

        return [
            'totals' => [
                'total_revenue' => $revenueTotal,
                'total_expenses' => $masrofatTotal,
                'profit' => $profit,
                'loss' => $profit < 0 ? abs($profit) : 0
            ],
            'daily_data' => array_reverse($dailyData), // Reverse to show oldest first
            'period' => [
                'from_date' => $request->filled('from_date') ? $request->from_date : null,
                'to_date' => $request->filled('to_date') ? $request->to_date : null,
                'month' => $request->filled('month') ? Carbon::parse($request->month)->format('Y-m') : null,
            ]
        ];
    }

    /**
     * Get Expenses Report by Bands and Months
     * @param Request $request
     * @return array
     */
    public function getExpensesByBandsAndMonths(Request $request)
    {
        $year = $request->filled('year') ? (int)$request->year : (int)Carbon::now()->year;
        
        // Get all expense bands
        $bands = SarfBand::orderBy('title', 'asc')->get();
        
        // Arabic month names
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        
        // Initialize data structure
        $reportData = [];
        $monthTotals = array_fill(1, 12, 0);
        $bandTotals = [];
        
        foreach ($bands as $band) {
            $bandTotals[$band->id] = 0;
            $bandMonths = [];
            
            for ($month = 1; $month <= 12; $month++) {
                // Calculate start and end of month
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                
                // Get total expenses for this band in this month
                $monthTotal = Masrofat::where('band_id', $band->id)
                    ->whereBetween('created_at', [
                        $startDate->format('Y-m-d 00:00:00'),
                        $endDate->format('Y-m-d 23:59:59')
                    ])
                    ->sum('value');
                
                $monthTotal = (float)$monthTotal;
                $bandMonths[$month] = $monthTotal;
                $bandTotals[$band->id] += $monthTotal;
                $monthTotals[$month] += $monthTotal;
            }
            
            $reportData[] = [
                'band_id' => $band->id,
                'band_title' => $band->title,
                'months' => $bandMonths,
                'total' => $bandTotals[$band->id]
            ];
        }
        
        // Calculate grand total
        $grandTotal = array_sum($monthTotals);
        
        return [
            'year' => $year,
            'months' => $months,
            'bands_data' => $reportData,
            'month_totals' => $monthTotals,
            'grand_total' => $grandTotal
        ];
    }

    /**
     * Get Revenues Report by Users and Months
     * @param Request $request
     * @return array
     */
    public function getRevenuesByUsersAndMonths(Request $request)
    {
        $year = $request->filled('year') ? (int)$request->year : (int)Carbon::now()->year;
        
        // Get all users who collected revenues
        $revenuesByUser = Revenue::whereNull('deleted_at')
            ->whereYear('created_at', $year)
            ->select('collected_by')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('collected_by')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Get unique user IDs
        $userIds = $revenuesByUser->pluck('collected_by')->toArray();

        // Arabic month names
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        
        // Initialize data structure
        $reportData = [];
        $monthTotals = array_fill(1, 12, 0);
        $userTotals = [];
        
        foreach ($userIds as $userId) {
            // Get user name
            $admin = AdminModel::find($userId);
            if (!$admin) {
                $admin = AdminModel::where('emp_id', $userId)->first();
            }
            
            if ($admin) {
                $userName = $admin->name;
            } else {
                $adminEmployee = AdminEmployee::find($userId);
                if ($adminEmployee) {
                    $userName = trim(($adminEmployee->first_name ?? '') . ' ' . ($adminEmployee->last_name ?? ''));
                } else {
                    $hrEmployee = Employee::find($userId);
                    $userName = $hrEmployee ? ($hrEmployee->name ?? 'Unknown') : 'Unknown';
                }
            }
            
            if ($userName == 'Unknown') {
                continue;
            }
            
            $userTotals[$userId] = 0;
            $userMonths = [];
            
            for ($month = 1; $month <= 12; $month++) {
                // Calculate start and end of month
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                
                // Get total revenues for this user in this month
                $monthTotal = Revenue::where('collected_by', $userId)
                    ->whereNull('deleted_at')
                    ->whereBetween('created_at', [
                        $startDate->format('Y-m-d 00:00:00'),
                        $endDate->format('Y-m-d 23:59:59')
                    ])
                    ->sum('amount');
                
                $monthTotal = (float)$monthTotal;
                $userMonths[$month] = $monthTotal;
                $userTotals[$userId] += $monthTotal;
                $monthTotals[$month] += $monthTotal;
            }
            
            $reportData[] = [
                'user_id' => $userId,
                'user_name' => $userName,
                'months' => $userMonths,
                'total' => $userTotals[$userId]
            ];
        }
        
        // Sort by total descending
        usort($reportData, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        // Calculate grand total
        $grandTotal = array_sum($monthTotals);
        
        return [
            'year' => $year,
            'months' => $months,
            'users_data' => $reportData,
            'month_totals' => $monthTotals,
            'grand_total' => $grandTotal
        ];
    }
}
