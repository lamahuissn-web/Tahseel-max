<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminStoreRequest;
use App\Http\Requests\Admin\AdminUpdateRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\AccountSettings;
use App\Models\Admin\Employee;
use App\Models\Log;
use App\Services\AdminUserService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $AdminUsersRepository;
    protected $employeesRepository;
    protected $rolesRepository;
    protected $permissionsRepository;
    protected $adminUserService;

    public function __construct(BasicRepositoryInterface $basicRepository, AdminUserService $adminUserService)
    {
        $this->middleware('can:list_users')->only('index');
        $this->middleware('can:create_user')->only('create', 'store');
        $this->middleware('can:update_user')->only('edit', 'update');
        $this->middleware('can:delete_user')->only('destroy');
        $this->middleware('can:change_user_status')->only('change_status');
        $this->middleware('can:update_user_permissions')->only('permissions', 'updatePermissions');

        $this->AdminUsersRepository     = createRepository($basicRepository, new Admin());
        $this->employeesRepository   = createRepository($basicRepository, new Employee());
        $this->rolesRepository   = createRepository($basicRepository, new Role());
        $this->adminUserService   = $adminUserService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $allData = Admin::with(['roles', 'employee'])->withSum('financialTransactions', 'amount')->get();
            return DataTables::of($allData)
                ->editColumn('name', function ($row) {
                    return $row->name ?? 'N/A';
                })
                ->editColumn('email', function ($row) {
                    return $row->email ?? 'N/A';
                })
                ->editColumn('role', function ($row) {
                    return $row->roles->isNotEmpty() ? $row->roles->first()->getTranslation('title', app()->getLocale()) : 'N/A';

                })
                ->editColumn('position', function ($row) {
                    return $row->position ?? 'N/A';
                })
                ->editColumn('created_by', function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == '1') {
                        $title_approved = trans('users.active');
                        $class_approved = 'success';
                        $icon_approved = '<i class="bi bi-check-circle-fill"></i>';
                    } else {
                        $title_approved = trans('users.not_active');
                        $class_approved = 'danger';
                        $icon_approved = '<i class="bi bi-x-circle-fill"></i>';
                    }
                    if (auth()->user()->can('change_user_status')) {
                        return '<a href="' . route('admin.change_status', [$row->id, $row->status]) . '" class="btn btn-' . $class_approved . ' btn-sm" onclick="return confirm(\'' . trans('users.change_type_msg') . '\');">' . $icon_approved . ' ' . $title_approved . '</a>';
                    }
                })
                ->editColumn('collected_amount', function ($row) {
                    return number_format($row->financial_transactions_sum_amount, 2);
                })
                ->addColumn('action', function ($row) {
                    $actionButtons = '<div class="btn-group btn-group-sm">';

                    if (auth()->user()->can('update_user')) {
                        $actionButtons .= '<a href="' . route('admin.users.edit', $row->id) . '" class="btn btn-sm btn-primary" title="' . trans('users.edit') . '" style="font-size: 16px;">
                            <i class="bi bi-pencil-square"></i>
                        </a>';
                    }

                    if (auth()->user()->can('delete_user')) {
                        $actionButtons .= '<a onclick="return confirm(\'Are You Sure To Delete?\')"  href="' . route('admin.delete_user', $row->id) . '"  class="btn btn-sm btn-danger" title="' . trans('users.delete') . '" style="font-size: 16px;" onclick="return confirm(\'' . trans('users.confirm_delete') . '\')">
                            <i class="bi bi-trash3"></i>
                        </a>';
                    }

                    $actionButtons .= '<a href="' . route('admin.users.show', $row->id) . '" class="btn btn-sm btn-info" title="' . trans('users.view_details') . '" style="font-size: 16px;">
                        <i class="bi bi-eye"></i>
                    </a>';

                    // $actionButtons .= ' <a href="' . route('admin.users.permissions', $row->id) . '" class="btn btn-sm btn-info" title="' . trans('users.set_permissions') . '" style="font-size: 16px;">
                    //     <i class="bi bi-lock"></i>
                    // </a>';
                    $actionButtons .=  '</div>';
                    return $actionButtons;
                })
                ->rawColumns(['name', 'action', 'status', 'role'])
                ->make(true);
        }

        if ($request->get('mobile') === 'collectors') {
            $collectors = Admin::with(['roles', 'employee', 'account' => function ($query) {
                    $query->withSum('financialTransactions', 'amount');
                }])
                ->withSum('financialTransactions', 'amount')
                ->whereNull('deleted_at')
                ->where('status', '1')
                ->orderByDesc('financial_transactions_sum_amount')
                ->get();

            $accountSettings = AccountSettings::first();
            $accountantAccount = $accountSettings ? Account::withSum('financialTransactions', 'amount')->find($accountSettings->accountant_account_id) : null;

            if ($accountantAccount) {
                $accountantUser = $accountantAccount->user;
                if ($accountantUser) {
                    $accountantUser->load(['roles', 'employee', 'account' => function ($query) {
                        $query->withSum('financialTransactions', 'amount');
                    }]);
                    $accountantUser->financial_transactions_sum_amount = $accountantAccount->financial_transactions_sum_amount ?? 0;
                    if (!$collectors->contains('id', $accountantUser->id)) {
                        $collectors->push($accountantUser);
                    }
                } else {
                    $fakeCollector = (object)[
                        'id' => 'accountant-' . $accountantAccount->id,
                        'name' => $accountantAccount->name,
                        'image' => asset('assets/media/avatars/blank.png'),
                        'roles' => collect([(object)[
                            'title' => 'محاسب'
                        ]]),
                        'employee' => null,
                        'account' => $accountantAccount,
                        'financial_transactions_sum_amount' => $accountantAccount->financial_transactions_sum_amount ?? 0
                    ];
                    $collectors->push($fakeCollector);
                }
            }

            $collectors = $collectors->sortByDesc('financial_transactions_sum_amount')->values();
            $collectorsCount = $collectors->count();
            $collectorsTotalAmount = $collectors->sum('financial_transactions_sum_amount');

            return view('dashbord.users.index', compact('collectors', 'collectorsCount', 'collectorsTotalAmount', 'accountantAccount'));
        }

        return view('dashbord.users.index');
    }

    /********************************************/
    public function create()
    {
        $data['roles']      = $this->rolesRepository->getAll();
        $data['employees']  = $this->employeesRepository->getAll();
        // dd($data);
        return view('dashbord.users.form', $data);
    }

    /********************************************/
    public function store(AdminStoreRequest $request)
    {
        try {
            // dd($request->all());
            $admin = $this->adminUserService->store($request);

            $message = sprintf(
                '👤 تم إضافة مشرف جديد: %s (%s) — البريد: %s (تمت العملية بواسطة: %s)',
                $admin->name,
                $admin->roles->first()->getTranslation('title', app()->getLocale()) ?? 'بدون صلاحية',
                $admin->email,
                auth()->user()->name
            );

            sendTelegramNotification($message, 'admin_added');

            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.users.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /********************************************/
    public function show(string $id, Request $request)
    {
        $user = $this->AdminUsersRepository->getById($id);
        
        if (!$user) {
            toastr()->addError(trans('users.user_not_found'));
            return redirect()->route('admin.users.index');
        }

        // Handle AJAX request for logs table
        if ($request->ajax()) {
            $allData = Log::with('user')
                ->where('user_id', $id)
                ->orderBy('id', 'desc')
                ->select('logs.*')
                ->get();

            return Datatables::of($allData)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('action_type', function ($row) {
                    $action = $row->action ?? 'N/A';
                    $class = match ($action) {
                        'invoice_paid' => 'badge bg-success text-white',
                        'invoice_redo' => 'badge bg-warning text-dark',
                        'invoice_created' => 'badge bg-info text-white',
                        'invoice_deleted' => 'badge bg-danger text-white',
                        'client_created' => 'badge bg-success text-white',
                        'client_updated' => 'badge bg-primary text-white',
                        'client_deleted' => 'badge bg-danger text-white',
                        'clients_imported' => 'badge bg-info text-white',
                        'user_login' => 'badge bg-secondary text-white',
                        'financial_transaction_created' => 'badge bg-success text-white',
                        'financial_transaction_deleted' => 'badge bg-danger text-white',
                        default => 'badge bg-secondary text-white'
                    };

                    $actionLabels = [
                        'invoice_paid' => trans('logs.invoice_paid'),
                        'invoice_redo' => trans('logs.invoice_redo'),
                        'invoice_created' => trans('logs.invoice_created'),
                        'invoice_deleted' => trans('logs.invoice_deleted'),
                        'client_created' => trans('logs.client_created'),
                        'client_updated' => trans('logs.client_updated'),
                        'client_deleted' => trans('logs.client_deleted'),
                        'clients_imported' => trans('logs.clients_imported'),
                        'user_login' => trans('logs.user_login'),
                        'financial_transaction_created' => trans('logs.financial_transaction_created'),
                        'financial_transaction_deleted' => trans('logs.financial_transaction_deleted')
                    ];

                    $label = $actionLabels[$action] ?? $action;
                    return '<span class="' . $class . ' px-4 py-3 rounded-pill fw-bold fs-5">' . $label . '</span>';
                })
                ->addColumn('description', function ($row) {
                    return $row->description ?? 'N/A';
                })
                ->addColumn('user', function ($row) {
                    if ($row->user) {
                        return $row->user->name;
                    }
                    return '<span class="text-muted">System</span>';
                })
                ->addColumn('ip_address', function ($row) {
                    return $row->ip_address ?? 'N/A';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';

                    $buttons .= '
                        <button type="button" class="btn btn-sm btn-info view-log-details"
                                data-log-id="' . $row->id . '"
                                title="' . trans('logs.view_details') . '" style="font-size: 16px;">
                            <i class="bi bi-eye"></i>
                        </button>';

                    $buttons .= '
                        <a onclick="return confirm(\'' . trans('logs.confirm_delete') . '\')"
                            href="' . route('admin.logs.delete', $row->id) . '"
                            class="btn btn-sm btn-danger" title="' . trans('logs.delete') . '" style="font-size: 16px;">
                            <i class="bi bi-trash3"></i>
                        </a>';

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['action_type', 'user', 'action'])
                ->make(true);
        }

        // Prepare data for view
        $data['user'] = $user;

        return view('dashbord.users.show', $data);
    }

    /********************************************/
    public function edit(string $id)
    {
        $data['admin']   = $this->AdminUsersRepository->getById($id);
        $data['roles']      = $this->rolesRepository->getAll();
        $data['employees']  = $this->employeesRepository->getAll();
        return view('dashbord.users.edit', $data);
    }

    /********************************************/
    public function update(AdminUpdateRequest $request, string $id)
    {
        try {
            // dd($request->all());
            $this->adminUserService->update($request, $id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.users.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /********************************************/
    public function destroy(string $id)
    {
        try {
            $admin = $this->AdminUsersRepository->getById($id);
            if ($admin->image) {
                $oldImagePath = public_path('images/' . $admin->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $this->AdminUsersRepository->delete($id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.users.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************/

    public function change_status($id, $status)
    {
        try {
            $admin_user = $this->AdminUsersRepository->getById($id);
            if ($admin_user) {
                if ($status == '1') {
                    $data['status'] = '0';
                } elseif ($status == '0') {
                    $data['status'] = '1';
                }
                $this->AdminUsersRepository->update($id, $data);
                toastr()->addSuccess(trans('users.status_changed_successfully'));
                return redirect()->route('admin.users.index');
            }
            return redirect()->route('admin.users.index');
        } catch (\Exception $e) {
            test($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function permissions($id)
    {
        // $data['permissions'] = $this->permissionsRepository->getAll();
        $data['admin'] = $this->AdminUsersRepository->getById($id);
        $data['sections'] = [
            'Dashboard' => Permission::whereIn('name',  ['view_dashboard'])->get(),
            'Branches' => Permission::whereIn('name', [
                'view_branches',
                'add_branch',
                'edit_branch',
                'delete_branch',
            ])->get(),
            'Governorates' => Permission::whereIn('name', [
                'view_governorates',
                'add_governorate',
                'edit_governorate',
                'delete_governorate',
            ])->get(),
            'Areas' => Permission::whereIn('name', [
                'view_areas',
                'add_area',
                'edit_area',
                'delete_area',
            ])->get(),
            'Site Data' => Permission::whereIn('name', [
                'view_site_data',
                'add_site_data',
                'edit_site_data',
            ])->get(),
            'Employees' => Permission::whereIn('name', [
                'view_employees',
                'add_employee',
                'edit_employee',
                'delete_employee',
            ])->get(),
            'Clients' => Permission::whereIn('name', [
                'list_clients',
                'create_client',
                'update_client',
                'delete_client',
            ])->get(),
            'Client Companies' => Permission::whereIn('name', [
                'view_client_companies',
                'add_client_company',
                'edit_client_company',
                'update_client_company',
                'delete_client_company',
            ])->get(),
            'Client Projects' => Permission::whereIn('name', [
                'view_client_projects',
                'add_client_project',
                'edit_client_project',
                'update_client_project',
                'delete_client_project',
            ])->get(),
            'Companies' => Permission::whereIn('name', [
                'list_companies',
                'create_company',
                'update_company',
                'delete_company',
            ])->get(),
            'Company Projects' => Permission::whereIn('name', [
                'view_company_projects',
                'add_company_project',
                'edit_company_project',
                'update_company_project',
                'delete_company_project',
            ])->get(),
            'Projects' => Permission::whereIn('name', [
                'list_projects',
                'create_project',
                'update_project',
                'delete_project',
            ])->get(),
            'Masrofat' => Permission::whereIn('name', [
                'list_masrofat',
                'create_masrofat',
                'update_masrofat',
                'delete_masrofat',
            ])->get(),
            'Tests' => Permission::whereIn('name', [
                'list_tests',
                'create_test',
                'update_test',
                'delete_test',
            ])->get(),
            'Users' => Permission::whereIn('name', [
                'list_users',
                'create_user',
                'update_user',
                'delete_user',
                'change_user_status',
                'manage_user_permissions',
            ])->get(),
        ];
        // dd($data);
        return view('dashbord.users.permissions.form', $data);
    }

    public function updatePermissions(Request $request, $id)
    {
        $validatedData = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        // dd($validatedData);
        $admin = $this->AdminUsersRepository->getById($id);
        $admin->syncPermissions($request->permissions ?? []);

        toastr()->addSuccess(trans('users.permissions_updated_successfully.'));
        return redirect()->route('admin.users.index');
    }
}
