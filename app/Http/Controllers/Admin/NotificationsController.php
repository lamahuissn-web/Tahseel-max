<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\Account;
use App\Models\Admin\Invoice;
use App\Models\Clients;
use App\Notifications\AccountTransferNotification;
use App\Notifications\AccountTransferRedoNotification;
use App\Notifications\InvoicePaidNotification;
use App\Notifications\InvoiceRedoNotification;
use App\Notifications\InvoiceReminderNotification;
use App\Notifications\NewClientAddedNotification;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
// use DataTables;
class NotificationsController extends Controller
{

    use ImageProcessing;
    use ValidationMessage;

    /***********************************************************/

    protected $ClientsRepository;

    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->middleware('can:view_new_clients_notifications')->only('new_clients', 'get_ajax_notifications');
        $this->middleware('can:view_unpaid_invoices_notifications')->only('unpaid_invoices', 'get_ajax_invoice_notifications');
        $this->middleware('can:mark_notification_read')->only('mark_notification_read');

        $this->ClientsRepository = createRepository($basicRepository, new Clients());
    }
    /***********************************************************/

    public function new_clients()
    {
        return view('dashbord.notifications.new_clients');
    }

    /***********************************************************/
    public function get_ajax_notifications()
    {
        if (request()->ajax()) {
            try {
                // $notifications = auth()->user()->notifications->where('type', NewClientAddedNotification::class);
                $sevenDaysAgo = Carbon::now()->subDays(7);

                $notifications = auth()->user()->notifications()
                    ->where('type', NewClientAddedNotification::class)
                    ->where('created_at', '>=', $sevenDaysAgo);

                $counter = 0;

                return DataTables::of($notifications)
                    ->addColumn('id', function () use (&$counter) {
                        $counter++;
                        return $counter;
                    })
                    ->addColumn('message', function ($row) {
                        return $row->data['message'] ?? 'تم إضافة عميل جديد';
                    })
                    ->addColumn('client_name', function ($row) {
                        // return '<a href="' . route('admin.client_paid_invoices', $row->data['client_id']) . '" class="text-primary" style="text-decoration: underline;">
                        //             ' . trans('notifications.client_details') . '
                        //         </a>';
                        $client = Clients::find($row->data['client_id']);
                        return '<a href="' . route('admin.client_paid_invoices', $row->data['client_id']) . '" class="text-primary" style="text-decoration: underline;">
                                    '. $client?->name .'
                                </a>';
                    })
                    ->addColumn('start_date', function ($row) {
                        $client = Clients::find($row->data['client_id']);
                        return $client?->start_date;
                    })
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format('Y-m-d');
                    })
                    ->addColumn('status', function ($row) {
                        return $row->read_at ? trans('notifications.read') : trans('notifications.unread');
                    })
                    ->addColumn('action', function ($row) {
                        if (!$row->read_at) {
                            if (Auth::user()->can('mark_notification_read')) {
                                return '<a href="' . route('admin.mark_notification_read', $row->id) . '" class="btn btn-sm btn-primary">
                                        ' . trans('notifications.mark_as_read') . '
                                    </a>';
                            }
                        }
                        return '<span class="badge bg-success">' . trans('notifications.read') . '</span>';
                    })
                    ->setRowClass(function ($row) {
                        return $row->read_at ? 'table-light' : 'table-warning';
                    })
                    ->rawColumns(['client_name', 'action'])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error('Error in get_ajax_notifications: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }

    /*****************************************************/
    public function mark_notification_read($id)
    {
        $notification = auth()->user()->notifications->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return redirect()->back()->with('success', trans('notifications.notification_read'));
    }
    /***********************************************************/

    public function unpaid_invoices()
    {
        return view('dashbord.notifications.invoices');
    }
    /*****************************************************/
    public function get_ajax_invoice_notifications()
    {
        if (request()->ajax()) {
            try {
                // $notifications = auth()->user()->notifications->where('type', InvoiceReminderNotification::class);
                $sevenDaysAgo = Carbon::now()->subDays(7);

                $notifications = auth()->user()->notifications()
                    ->where('type', InvoiceReminderNotification::class)
                    ->where('created_at', '>=', $sevenDaysAgo);

                $counter = 0;

                return DataTables::of($notifications)
                    ->addColumn('id', function () use (&$counter) {
                        $counter++;
                        return $counter;
                    })
                    ->addColumn('invoice_number', function ($row) {
                        $invoice = Invoice::find($row->data['invoice_id']);
                        if (!$invoice) return 'N/A';

                        $client = Clients::find($invoice->client_id);
                        $prefix = ($client && $client->client_type == 'satellite') ? 'SA-' : 'IN-';

                        return '<a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $invoice->id) . '\')"
                                class="text-primary fw-bold" style="text-decoration: underline;" title="' . trans('invoices.view_details') . '">
                                ' . $prefix . $invoice->invoice_number . '
                            </a>';
                    })
                    ->addColumn('message', function ($row) {
                        return $row->data['message'] ?? 'تنبيه بفاتورة مستحقة';
                    })
                    ->addColumn('amount', function ($row) {
                        return number_format($row->data['amount'], 2);
                    })
                    ->addColumn('paid_amount', function ($row) {
                        return number_format($row->data['paid_amount'], 2);
                    })
                    ->addColumn('remaining_amount', function ($row) {
                        return number_format($row->data['remaining_amount'], 2);
                    })
                    ->addColumn('due_date', function ($row) {
                        $invoice = Invoice::find($row->invoice_id);
                        return $invoice ? $invoice->due_date : 'N/A';
                    })
                    ->addColumn('client', function ($row) {
                        $invoice = Invoice::find($row->data['invoice_id']);
                        if (!$invoice) return 'N/A';

                        $client = Clients::find($invoice->client_id);
                        return '<a href="' . route('admin.client_paid_invoices', $row->data['client']) . '" class="text-primary" style="text-decoration: underline;">
                                    '. $client->name .'
                                </a>';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->read_at ? trans('notifications.read') : trans('notifications.unread');
                    })
                    ->addColumn('month_year', function ($row) {
                        return $row->created_at ? Carbon::parse($row->created_at)->format('F Y') : 'N/A';
                    })
                    ->addColumn('action', function ($row) {
                        if (!$row->read_at) {
                            if (Auth::user()->can('mark_notification_read')) {
                                return '<a href="' . route('admin.mark_notification_read', $row->id) . '" class="btn btn-sm btn-primary">
                                        ' . trans('notifications.mark_as_read') . '
                                    </a>';
                            }
                        }
                        return '<span class="badge bg-success">' . trans('notifications.read') . '</span>';
                    })
                    ->setRowClass(function ($row) {
                        return $row->read_at ? 'table-light' : 'table-warning';
                    })
                    ->rawColumns(['invoice_number', 'client', 'action', 'month_year'])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error('Error in get_ajax_invoice_notifications: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }
    public function invoices()
    {
        return view('dashbord.notifications.pay_invoices');
    }
    /*****************************************************/
    public function get_ajax_invoices_notifications()
    {
        if (request()->ajax()) {
            try {
                // $notifications = auth()->user()->notifications()
                //                 ->whereIn('type', [
                //                     \App\Notifications\InvoicePaidNotification::class,
                //                     \App\Notifications\InvoiceRedoNotification::class,
                //                 ])
                //                 ->orderBy('created_at', 'desc');
                $sevenDaysAgo = Carbon::now()->subDays(7);

            $notifications = auth()->user()->notifications()
                    ->whereIn('type', [
                        \App\Notifications\InvoicePaidNotification::class,
                        \App\Notifications\InvoiceRedoNotification::class,
                    ])
                    ->where('created_at', '>=', $sevenDaysAgo)
                    ->orderBy('created_at', 'desc');


                $counter = 0;

                return DataTables::of($notifications)
                    ->addColumn('id', function () use (&$counter) {
                        $counter++;
                        return $counter;
                    })
                    ->addColumn('invoice_number', function ($row) {
                        $invoice = Invoice::find($row->data['invoice_id']);
                        if (!$invoice) return 'N/A';

                        $client = Clients::find($invoice->client_id);
                        $prefix = ($client && $client->client_type == 'satellite') ? 'SA-' : 'IN-';

                        return '<a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $invoice->id) . '\')"
                                class="text-primary fw-bold" style="text-decoration: underline;" title="' . trans('invoices.view_details') . '">
                                ' . $prefix . $invoice->invoice_number . '
                            </a>';
                    })
                    ->addColumn('message', function ($row) {
                        return $row->data['message'] ?? 'تنبيه بفاتورة مستحقة';
                    })
                    ->addColumn('amount', function ($row) {
                        return number_format($row->data['amount'], 2);
                    })
                    ->addColumn('client', function ($row) {
                        $invoice = Invoice::find($row->data['invoice_id']);
                        if (!$invoice) return 'N/A';

                        $client = Clients::find($invoice->client_id);
                        return '<a href="' . route('admin.client_paid_invoices', $client) . '" class="text-primary" style="text-decoration: underline;">
                                    '. $client->name .'
                                </a>';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->read_at ? trans('notifications.read') : trans('notifications.unread');
                    })
                    ->addColumn('month_year', function ($row) {
                        return $row->created_at ? Carbon::parse($row->created_at)->format('F Y') : 'N/A';
                    })
                    ->addColumn('action', function ($row) {
                        if (!$row->read_at) {
                            if (Auth::user()->can('mark_notification_read')) {
                                return '<a href="' . route('admin.mark_notification_read', $row->id) . '" class="btn btn-sm btn-primary">
                                        ' . trans('notifications.mark_as_read') . '
                                    </a>';
                            }
                        }
                        return '<span class="badge bg-success">' . trans('notifications.read') . '</span>';
                    })
                    ->setRowClass(function ($row) {
                        return $row->read_at ? 'table-light' : 'table-warning';
                    })
                    ->rawColumns(['invoice_number', 'client', 'action', 'month_year'])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error('Error in get_ajax_invoice_notifications: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }
    public function transfers()
    {
        return view('dashbord.notifications.transfers');
    }
    /*****************************************************/
    public function get_ajax_transfers_notifications()
    {
        if (request()->ajax()) {
            try {
                // $notifications = auth()->user()->notifications()
                //                 ->whereIn('type', [
                //                     \App\Notifications\AccountTransferNotification::class,
                //                     \App\Notifications\AccountTransferRedoNotification::class,
                //                 ])
                //                 ->orderBy('created_at', 'desc');
                $sevenDaysAgo = Carbon::now()->subDays(7);

                $notifications = auth()->user()->notifications()
                    ->whereIn('type', [
                        \App\Notifications\AccountTransferNotification::class,
                        \App\Notifications\AccountTransferRedoNotification::class,
                    ])
                    ->where('created_at', '>=', $sevenDaysAgo)
                    ->orderBy('created_at', 'desc');

                $counter = 0;

                return DataTables::of($notifications)
                    ->addColumn('id', function () use (&$counter) {
                        $counter++;
                        return $counter;
                    })
                    ->addColumn('message', function ($row) {
                        return $row->data['message'] ?? 'تنبيه بتحويل مالي';
                    })
                    ->addColumn('amount', function ($row) {
                        return number_format($row->data['amount'], 2);
                    })
                    ->addColumn('from_account', function ($row) {
                        $account = Account::find($row->data['from_account']);
                        return $row->data['from_account'] ?? 'N/A';
                    })
                    ->addColumn('to_account', function ($row) {
                        $account = Account::find($row->data['to_account']);
                        return $row->data['to_account'] ?? 'N/A';
                    })
                    ->addColumn('user_name', function ($row) {
                        return $row->data['user_name'] ?? 'N/A';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->read_at ? trans('notifications.read') : trans('notifications.unread');
                    })
                    ->addColumn('month_year', function ($row) {
                        return $row->created_at ? Carbon::parse($row->created_at)->format('F Y') : 'N/A';
                    })
                    ->addColumn('action', function ($row) {
                        if (!$row->read_at) {
                            if (Auth::user()->can('mark_notification_read')) {
                                return '<a href="' . route('admin.mark_notification_read', $row->id) . '" class="btn btn-sm btn-primary">
                                        ' . trans('notifications.mark_as_read') . '
                                    </a>';
                            }
                        }
                        return '<span class="badge bg-success">' . trans('notifications.read') . '</span>';
                    })
                    ->setRowClass(function ($row) {
                        return $row->read_at ? 'table-light' : 'table-warning';
                    })
                    ->rawColumns(['action', 'month_year'])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error('Error in get_ajax_invoice_notifications: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }

    public function invoice_management()
    {
        // $notifications = auth()->user()->notifications()
        //                         ->whereIn('type', [
        //                             \App\Notifications\InvoiceCreatedNotification::class,
        //                             \App\Notifications\InvoiceDeletedNotification::class,
        //                         ])
        //                         ->orderBy('created_at', 'desc');
        // dd($notifications);
        return view('dashbord.notifications.invoice_management');
    }

    public function get_ajax_invoice_management_notifications()
    {
        if (request()->ajax()) {
            try {
                // $notifications = auth()->user()->notifications()
                //                 ->whereIn('type', [
                //                     \App\Notifications\InvoiceCreatedNotification::class,
                //                     \App\Notifications\InvoiceDeletedNotification::class,
                //                 ])
                //                 ->orderBy('created_at', 'desc');

                $sevenDaysAgo = Carbon::now()->subDays(7);

                $notifications = auth()->user()->notifications()
                    ->whereIn('type', [
                        \App\Notifications\InvoiceCreatedNotification::class,
                        \App\Notifications\InvoiceDeletedNotification::class,
                    ])
                    ->where('created_at', '>=', $sevenDaysAgo)
                    ->orderBy('created_at', 'desc');

                $counter = 0;

                return DataTables::of($notifications)
                    ->addColumn('id', function () use (&$counter) {
                        $counter++;
                        return $counter;
                    })
                    ->addColumn('type', function ($row) {
                        $type = $row->data['type'] ?? 'unknown';
                        $badgeClass = $type == 'invoice_created' ? 'badge bg-info' : 'badge bg-danger';
                        $typeText = $type == 'invoice_created' ? 'إنشاء فاتورة' : 'حذف فاتورة';

                        return '<span class="' . $badgeClass . '">' . $typeText . '</span>';
                    })
                    ->addColumn('invoice_number', function ($row) {
                        $invoice = Invoice::find($row->data['invoice_id']);
                        if (!$invoice) return 'N/A';

                        $client = Clients::find($invoice->client_id);
                        $prefix = ($client && $client->client_type == 'satellite') ? 'SA-' : 'IN-';

                        return '<a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', $invoice->id) . '\')"
                                class="text-primary fw-bold" style="text-decoration: underline;" title="' . trans('invoices.view_details') . '">
                                ' . $prefix . $invoice->invoice_number . '
                            </a>';
                    })
                    ->addColumn('message', function ($row) {
                        return $row->data['message'] ?? 'تنبيه بإدارة الفواتير';
                    })
                    ->addColumn('amount', function ($row) {
                        return isset($row->data['amount']) ? number_format($row->data['amount'], 2) : 'N/A';
                    })
                    ->addColumn('client', function ($row) {
                        $clientName = $row->data['client_name'] ?? 'N/A';

                        if (isset($row->data['client_id'])) {
                            return '<a href="' . route('admin.client_paid_invoices', $row->data['client_id']) . '" class="text-primary" style="text-decoration: underline;">
                                        ' . $clientName . '
                                    </a>';
                        }

                        return $clientName;
                    })
                    ->addColumn('user_name', function ($row) {
                        return $row->data['user_name'] ?? 'N/A';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->read_at ? trans('notifications.read') : trans('notifications.unread');
                    })
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d H:i') : 'N/A';
                    })
                    ->addColumn('action', function ($row) {
                        if (!$row->read_at) {
                            if (Auth::user()->can('mark_notification_read')) {
                                return '<a href="' . route('admin.mark_notification_read', $row->id) . '" class="btn btn-sm btn-primary">
                                        ' . trans('notifications.mark_as_read') . '
                                    </a>';
                            }
                        }
                        return '<span class="badge bg-success">' . trans('notifications.read') . '</span>';
                    })
                    ->setRowClass(function ($row) {
                        return $row->read_at ? 'table-light' :
                                ($row->data['type'] == 'invoice_created' ? 'table-info' : 'table-danger');
                    })
                    ->rawColumns(['type', 'invoice_number', 'client', 'action'])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error('Error in get_ajax_invoice_management_notifications: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }
}
