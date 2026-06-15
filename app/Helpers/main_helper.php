<?php



use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\AccountSettings;
use App\Models\Admin\Employee;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Invoice;
use App\Models\Admin\Masrofat;
use App\Models\Admin\Revenue;
use App\Models\Clients;
use App\Models\Log;
use App\Traits\ImageProcessing;
use Carbon\Carbon;
use Flasher\Laravel\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Storage;


if (!function_exists('getDefultImage')) {

    function getDefultImage()
    {
        return asset('assets/media/avatars/blank.png');
    }
}
// if (!function_exists('getMainData')) {

//     function getMainData()
//     {
//         $mdata = \App\Models\Site\SiteData::first();
//         return ($mdata);
//     }
// }

if (!function_exists('extractVideoId')) {

    function extractVideoId($videoLink)
    {
        // Extract video ID from the YouTube link
        $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        preg_match($pattern, $videoLink, $matches);

        // Check if the regex matched and return the video ID or null
        return isset($matches[1]) ? $matches[1] : null;
    }
}

if (!function_exists('formatDateForDisplay')) {


    function formatDateForDisplay($dateTimeStr)
    {
        $dateTime = new DateTime($dateTimeStr);

        $formattedDate = $dateTime->format('d M Y');
        $formattedTime = $dateTime->format('g:ia');
        $formattedTime = strtolower($formattedTime);

        return $formattedDate . ' at ' . $formattedTime;
    }
}
if (!function_exists('formatTimeForDisplay')) {


    function formatTimeForDisplay($dateTimeStr)
    {
        $dateTime = new DateTime($dateTimeStr);

        $formattedTime = $dateTime->format('g:i a');
        $formattedTime = strtolower($formattedTime);

        return  $formattedTime;
    }
}
if (!function_exists('formatDateDayDisplay')) {


    function formatDateDayDisplay($dateTimeStr)
    {
        $dateTime = new DateTime($dateTimeStr);

        $formattedDate = $dateTime->format('Y-m-d');

        return $formattedDate;
    }
}


if (!function_exists('getFirstLetters')) {
    function getFirstLetters($inputString)
    {
        $words = explode(' ', $inputString);
        $firstLetters = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $firstLetters .= strtoupper($word[0]);  // Get the first letter and convert to uppercase
            }
        }

        return $firstLetters;
    }
}

if (!function_exists('generateUniqueRandomCode')) {

    function generateUniqueRandomCode($table, $column)
    {
        do {
            $code = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $exists = DB::table($table)->where($column, $code)->exists();
        } while ($exists);

        return $code;
    }
}



/*************************************************************/
function get_session_attendance($member_id, $additional_sub_id)
{
    if ($member_id && $additional_sub_id) {
        $session_num = \App\Models\MembersAttendance::where('member_id', $member_id)->where('additional_subscription_id', $additional_sub_id)->count();
        return $session_num;
    } else {
        return 0;
    }
}

/**************************************************************/
function get_app_config_data($key)
{
    $data = \App\Models\AppConfig::where('key', $key)->first();
    return $data?->value ?? null;
}

/***************************************************************/
function AddButton($route)
{
    $button = '
            <div class="d-flex">
                <a href="' . $route . '" class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4">
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="currentColor"/>
                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="currentColor"/>
                        </svg>
                    </span>

                </a>
            </div>';

    echo $button;
}

/****************************************************************/
function BackButton($route)
{
    $button = '
            <div class="d-flex">
                <a href="' . $route . '" class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4">
                    <span class="svg-icon svg-icon-2">
                                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                       <path
                                           d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z"
                                           fill="currentColor"/>
                                   </svg>
                    </span>

                </a>
            </div>';

    echo $button;
}
/****************************************************************/
function PageTitle($title, $breadcrumbs)
{
    $breadcrumbItems = '';
    foreach ($breadcrumbs as $breadcrumb) {
        if (isset($breadcrumb['link']) && $breadcrumb['link'] !== '') {
            $breadcrumbItems .= '<li class="breadcrumb-item text-muted"><a href="' . $breadcrumb['link'] . '" class="text-muted text-hover-primary">' . $breadcrumb['label'] . '</a></li>';
        } else {
            $breadcrumbItems .= '<li class="breadcrumb-item text-muted">' . $breadcrumb['label'] . '</li>';
        }
        $breadcrumbItems .= '<li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>';
    }
    $breadcrumbItems = rtrim($breadcrumbItems, '<li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>');

    $pageTitle = '
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">' . $title . '</h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            ' . $breadcrumbItems . '
        </ul>
    </div>';

    echo $pageTitle;
}

/**********************************************************/
function generateTable(array $headers)
{

    $table = '<div class="card-body">
                    <div class="table-responsive">
                        <table id="table1" class="table table-bordered">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800">';

    foreach ($headers as $header) {
        $table .= '<th style="text-align: center;">' . htmlspecialchars(trans($header)) . '</th>';
    }

    $table .= '</tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>';

    echo $table;
}




/*----------------------------------------------*/
if (!function_exists('createRepository')) {
    function createRepository(BasicRepositoryInterface $basicRepository, $model)
    {
        $repository = clone $basicRepository;
        $repository->set_model($model);
        return $repository;
    }
}



if (!function_exists('count_sarf_band')) {
    function count_sarf_band()
    {
        $query = DB::table('tbl_sarf_bands');
        $count = $query->count();

        return $count;
    }
}

if (!function_exists('count_subscriptions')) {
    function count_subscriptions()
    {
        $query = DB::table('tbl_subscriptions');
        $count = $query->count();

        return $count;
    }
}

if (!function_exists('count_notifications_clients')) {
    function count_notifications_clients()
    {
        $admin = Auth::user();

        if (!$admin) {
            return 0;
        }

        $sevenDaysAgo = Carbon::now()->subDays(7);

        return $admin->notifications()
            ->where('type', \App\Notifications\NewClientAddedNotification::class)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->whereNull('read_at')
            ->count();
    }
}

if (!function_exists('count_all_notifications_clients')) {
    function count_all_notifications_clients()
    {
        $admin = Auth::user();

        if (!$admin) {
            return 0;
        }

        $sevenDaysAgo = Carbon::now()->subDays(7);

        return $admin->notifications()
            ->whereIn('type', [
                \App\Notifications\NewClientAddedNotification::class,
                \App\Notifications\InvoiceReminderNotification::class,
                \App\Notifications\InvoicePaidNotification::class,
                \App\Notifications\InvoiceRedoNotification::class,
                \App\Notifications\AccountTransferNotification::class,
                \App\Notifications\AccountTransferRedoNotification::class,
            ])
            ->where('created_at', '>=', $sevenDaysAgo)
            ->whereNull('read_at')
            ->count();
    }
}


if (!function_exists('count_invoice_reminder_notifications')) {
    function count_invoice_reminder_notifications()
    {
        $admin = Auth::user();

        if (!$admin) {
            return 0;
        }

        $sevenDaysAgo = Carbon::now()->subDays(7);

        return $admin->notifications()
            ->where('type', \App\Notifications\InvoiceReminderNotification::class)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->whereNull('read_at')
            ->count();
    }
}

if (!function_exists('count_invoices_process_notifications')) {
    function count_invoices_process_notifications()
    {
        $admin = Auth::user();

        if (!$admin) {
            return 0;
        }

        $sevenDaysAgo = Carbon::now()->subDays(7);

        return $admin->notifications()
            ->whereIn('type', [
                \App\Notifications\InvoicePaidNotification::class,
                \App\Notifications\InvoiceRedoNotification::class
            ])
            ->where('created_at', '>=', $sevenDaysAgo)
            ->whereNull('read_at')
            ->count();
    }
}

if (!function_exists('count_invoice_management_notifications')) {
    function count_invoice_management_notifications()
    {
        $admin = Auth::user();

        if (!$admin) {
            return 0;
        }

        $sevenDaysAgo = Carbon::now()->subDays(7);

        return $admin->notifications()
            ->whereIn('type', [
                \App\Notifications\InvoiceCreatedNotification::class,
                \App\Notifications\InvoiceDeletedNotification::class,
            ])
            ->where('created_at', '>=', $sevenDaysAgo)
            ->whereNull('read_at')
            ->count();
    }
}

if (!function_exists('count_transfers_notifications')) {
    function count_transfers_notifications()
    {
        $admin = Auth::user();

        if (!$admin) {
            return 0;
        }

        $sevenDaysAgo = Carbon::now()->subDays(7);

        return $admin->notifications()
            ->whereIn('type', [
                \App\Notifications\AccountTransferNotification::class,
                \App\Notifications\AccountTransferRedoNotification::class
            ])
            ->where('created_at', '>=', $sevenDaysAgo)
            ->whereNull('read_at')
            ->count();
    }
}


if (!function_exists('test')) {
    function test($data)
    {
        $startTime = microtime(true);
        echo '<pre>';
        print_r($data);
        die();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        echo "Execution Time: $executionTime seconds";
    }

    if (!function_exists('formatFileSize')) {
        function formatFileSize($destination)
        {
            $bytes = filesize($destination);
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            } elseif ($bytes > 1) {
                return $bytes . ' bytes';
            } elseif ($bytes == 1) {
                return $bytes . ' byte';
            } else {
                return '0 bytes';
            }
        }
    }
}
/************************************************/
/**********************************************/
if (!function_exists('generateCardHeader')) {
    function generateCardHeader($card_title, $route, $add_button_title)
    {
        if ($add_button_title != ' ') {
            $button = '<a class="btn btn-primary" href="' . route($route) . '">
                                <i class="bi bi-plus fs-1"></i>' . htmlspecialchars(trans($add_button_title)) . '
                            </a>';
        } else {
            $button = '';
        }
        $header = '
         <div class="card-header">
                    <h3 class="card-title">' . htmlspecialchars(trans($card_title)) . '</h3>
                    <div class="card-toolbar">
                        <div class="text-center">
                          ' . $button . '
                        </div>
                    </div>
                </div>
        ';

        echo $header;
    }
}
/***********************************************/
if (!function_exists('form_icon')) {
    function form_icon($type)
    {
        $icons = [
            'text' => '<i class="bi bi-pencil-square fs-4"></i>',
            'date' => '<i class="bi bi-calendar-event fs-4"></i>',
            'select' => '<i class="bi bi-list-ul fs-4"></i>',
            'select1' => '<i class="bi bi-caret-down fs-2"></i>',
            'number' => '<i class="bi bi-hash fs-4"></i>',
            'email' => '<i class="bi bi-envelope fs-4"></i>',
            'password' => '<i class="bi bi-key fs-4"></i>',
            'image' => '<i class="bi bi-image fs-4"></i>',
            'phone' => '<i class="bi bi-telephone fs-4"></i>',
            'file' => '<i class="bi bi-file-earmark fs-4"></i>',
            'checkbox' => '<i class="bi bi-check2-square fs-4"></i>',
            'address' => '<i class="bi bi-geo-alt fs-4"></i>',
            'status' => '<i class="bi bi-toggle-on fs-4"></i>',
            'role' => '<i class="bi bi-person-badge fs-4"></i>',
            'price' => '<i class="bi bi-currency-dollar fs-4"></i>',
        ];

        // Return the icon if it exists, otherwise return a default icon
        return $icons[$type] ?? '<i class="bi bi-question-circle fs-4"></i>';
    }


    if (! function_exists('getLastFieldValue')) {
        function getLastFieldValue($model, $field)
        {
            $lastValue = $model::withTrashed()->latest()->value($field);
            return is_null($lastValue) ? 1 : $lastValue + 1;
        }
    }

    if (!function_exists('get_dashboard_data')) {
        function get_dashboard_data1()
        {
            $accountSettings = AccountSettings::first();
            $generalAccountId = $accountSettings ? $accountSettings->general_account_id : null;
            return [
                'employees' => Employee::count(),
                'clients' => Clients::count(),

                'paid_invoices_count' => Invoice::whereIn('status', ['paid', 'partial'])->count(),
                'paid_invoices_total' => Invoice::whereIn('status', ['paid', 'partial'])->sum('amount'),

                'unpaid_invoices_count' => Invoice::where('status', 'unpaid')->count(),
                'unpaid_invoices_total' => Invoice::where('status', 'unpaid')->sum('amount'),

                'revenues' => Revenue::sum('amount'),
                'masrofat' => Masrofat::sum('value'),

                'general_account' => FinancialTransaction::where('account_id', $generalAccountId)->sum('amount'),
            ];
        }
        function get_dashboard_data()
        {
            // Users Statistics
            $usersCount = Admin::whereNull('deleted_at')->where('status', '1')->count();
            // Active users are those with status = '1', inactive are those with status = '0'
            $activeUsersCount = Admin::whereNull('deleted_at')->where('status', '1')->count();
            $inactiveUsersCount = Admin::whereNull('deleted_at')->where('status', '0')->count();

            // Employees Statistics
            $employeesCount = Employee::count();
            $activeEmployeesCount = Employee::where('status', 'active')->count();
            $inactiveEmployeesCount = Employee::where('status', 'inactive')->count();

            // Clients Statistics
            $totalClientsCount = Clients::whereNull('deleted_at')->count();
            $activeClientsCount = Clients::where('is_active', 1)->whereNull('deleted_at')->count();
            $inactiveClientsCount = $totalClientsCount - $activeClientsCount;

            // New Clients (last 30 days)
            $newClientsCount = Clients::where('created_at', '>=', now()->subDays(30))->whereNull('deleted_at')->count();

            // Clients by Type
            $internetClientsCount = Clients::where('client_type', 'internet')->where('is_active', 1)->whereNull('deleted_at')->count();
            $satelliteClientsCount = Clients::where('client_type', 'satellite')->where('is_active', 1)->whereNull('deleted_at')->count();

            // Invoices Statistics
            $totalInvoicesCount = Invoice::whereNull('deleted_at')->count();
            $totalInvoicesAmount = Invoice::whereNull('deleted_at')->sum('amount');

            $paidInvoices = Invoice::where('status', 'paid')->whereNull('deleted_at');
            $paidInvoicesCount = $paidInvoices->count();
            $paidInvoicesAmount = $paidInvoices->sum('amount');

            $unpaidInvoices = Invoice::where('status', 'unpaid')->whereNull('deleted_at');
            $unpaidInvoicesCount = $unpaidInvoices->count();
            $unpaidInvoicesAmount = $unpaidInvoices->sum('amount');

            $partialInvoices = Invoice::where('status', 'partial')->whereNull('deleted_at');
            $partialInvoicesCount = $partialInvoices->count();
            $partialInvoicesAmount = $partialInvoices->sum('amount');

            // Calculate total paid from financial transactions
            $allData = Admin::withSum('financialTransactions', 'amount')->get();
            $totalPaid = $allData->sum('financial_transactions_sum_amount');
            $totalRemaining = Invoice::whereNull('deleted_at')->sum('remaining_amount');

            // Revenues Statistics
            $totalRevenues = Revenue::whereNull('deleted_at')->sum('amount');
            $monthlyRevenues = Revenue::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereNull('deleted_at')
                ->sum('amount');

            // Expenses (Masrofat) Statistics
            $totalExpenses = Masrofat::sum('value');
            $monthlyExpenses = Masrofat::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('value');

            // Monthly statistics for charts (last 6 months)
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthName = $date->format('M');
                $monthlyData[$monthName] = [
                    'revenues' => Revenue::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->whereNull('deleted_at')
                        ->sum('amount'),
                    'expenses' => Masrofat::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->sum('value'),
                    'invoices' => Invoice::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->whereNull('deleted_at')
                        ->sum('amount'),
                ];
            }

            // Invoice status distribution
            $invoiceStatusDistribution = [
                'paid' => $paidInvoicesCount,
                'unpaid' => $unpaidInvoicesCount,
                'partial' => $partialInvoicesCount,
            ];

            // Client type distribution
            $clientTypeDistribution = [
                'internet' => $internetClientsCount,
                'satellite' => $satelliteClientsCount,
            ];

            // Today's Logs (last 10 logs)
            $todayLogs = Log::with('user')
                ->whereDate('created_at', today())
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // Today's Paid Invoices
            $todayPaidInvoices = Invoice::with(['client:id,name', 'subscription:id,name'])
                ->whereIn('status', ['paid', 'partial'])
                ->whereDate('paid_date', today())
                ->whereNull('deleted_at')
                ->orderBy('paid_date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            // Users with today's invoices count and total
            $todayUsersInvoices = Revenue::whereDate('created_at', today())
                ->whereNull('deleted_at')
                ->select('collected_by')
                ->selectRaw('COUNT(DISTINCT invoice_id) as invoices_count')
                ->selectRaw('SUM(amount) as total_amount')
                ->groupBy('collected_by')
                ->orderBy('total_amount', 'desc')
                ->get()
                ->map(function ($item) {
                    // Get user (Admin)
                    $user = Admin::find($item->collected_by);
                    if (!$user) {
                        // Try to find by emp_id
                        $user = Admin::where('emp_id', $item->collected_by)->first();
                    }
                    if (!$user) {
                        // Try Employee
                        $employee = Employee::find($item->collected_by);
                        if ($employee) {
                            return [
                                'user_id' => $item->collected_by,
                                'user_name' => $employee->first_name . ' ' . $employee->last_name,
                                'invoices_count' => $item->invoices_count,
                                'total_amount' => $item->total_amount,
                                'is_employee' => true
                            ];
                        }
                    }

                    return [
                        'user_id' => $item->collected_by,
                        'user_name' => $user ? $user->name : 'Unknown',
                        'invoices_count' => $item->invoices_count,
                        'total_amount' => $item->total_amount,
                        'is_employee' => false
                    ];
                })
                ->filter(function ($item) {
                    return $item['user_name'] !== 'Unknown';
                })
                ->values();

            return [
                // Users
                'users_count' => $usersCount,
                'active_users_count' => $activeUsersCount,
                'inactive_users_count' => $inactiveUsersCount,

                // Employees
                'employees_count' => $employeesCount,
                'active_employees_count' => $activeEmployeesCount,
                'inactive_employees_count' => $inactiveEmployeesCount,

                // Clients
                'total_clients_count' => $totalClientsCount,
                'active_clients_count' => $activeClientsCount,
                'inactive_clients_count' => $inactiveClientsCount,
                'new_clients_count' => $newClientsCount,
                'internet_clients_count' => $internetClientsCount,
                'satellite_clients_count' => $satelliteClientsCount,

                // Invoices
                'total_invoices_count' => $totalInvoicesCount,
                'total_invoices_amount' => $totalInvoicesAmount,
                'paid_invoices_count' => $paidInvoicesCount,
                'paid_invoices_amount' => $paidInvoicesAmount,
                'unpaid_invoices_count' => $unpaidInvoicesCount,
                'unpaid_invoices_amount' => $unpaidInvoicesAmount,
                'partial_invoices_count' => $partialInvoicesCount,
                'partial_invoices_amount' => $partialInvoicesAmount,
                'total_paid' => $totalPaid,
                'total_remaining' => $totalRemaining,

                // Revenues
                'total_revenues' => $totalRevenues,
                'monthly_revenues' => $monthlyRevenues,

                // Expenses
                'total_expenses' => $totalExpenses,
                'monthly_expenses' => $monthlyExpenses,

                // Charts Data
                'monthly_data' => $monthlyData,
                'invoice_status_distribution' => $invoiceStatusDistribution,
                'client_type_distribution' => $clientTypeDistribution,

                // Today's Data
                'today_logs' => $todayLogs,
                'today_paid_invoices' => $todayPaidInvoices,
                'today_users_invoices' => $todayUsersInvoices,
            ];
        }
    }




    //-------------------------------------------------------------------
    function get_user_account_balance($user_id)
    {
        $account_id = Admin::find($user_id)->account_id;
        $account = Account::with('totalAmount')->find($account_id);
        return $account->totalAmount;
    }
    //-------------------------------------------------------------------
    function get_employee_account_balance($employee_id)
    {
        $account_id = Admin::where('emp_id', $employee_id)->first()->account_id;
        $account = Account::with('financialTransactions')->find($account_id);
        // dd($account->financialTransactions->sum('amount'));
        return $account->financialTransactions->sum('amount');
    }
}

if (!function_exists('log_helper')) {
    function log_helper($action, $description, $data = [])
    {
        try {
            $user = Auth::user();
            $request = app(\Illuminate\Http\Request::class);

            $model = $data['model'] ?? null;
            $oldData = $data['old_data'] ?? null;
            $newData = $data['new_data'] ?? null;

            return Log::create([
                'action' => $action,
                'description' => $description,
                'old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model ? $model->id : null,
                'user_id' => $user ? $user->id : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $e) {
            FacadesLog::error('Failed to create log: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists("formatBytes")) {
    function formatBytes($bytes, $precision = 2) {
        $units = ["B", "KB", "MB", "GB", "TB"];
        $bytes = max(intval($bytes), 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . " " . $units[$pow];
    }
}
