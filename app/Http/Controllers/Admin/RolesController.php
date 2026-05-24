<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\roles\RolesRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Services\RoleService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RolesController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $rolesRepository;
    protected $permissionsRepository;
    protected $roleService;

    public function __construct(BasicRepositoryInterface $basicRepository, RoleService $roleService)
    {
        $this->middleware('can:list_roles')->only('index');
        $this->middleware('can:create_role')->only('create', 'store');
        $this->middleware('can:update_role')->only('edit', 'update');
        $this->middleware('can:delete_role')->only('destroy');

        $this->rolesRepository   = createRepository($basicRepository, new Role());
        $this->permissionsRepository   = createRepository($basicRepository, new Permission());
        $this->roleService   = $roleService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $allData = $this->rolesRepository->getAll();
            return DataTables::of($allData)
                ->editColumn('title', function ($row) {
                    return $row->getTranslation('title', app()->getLocale()) ?? 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $actionButtons = '<div class="btn-group btn-group-sm">';

                    if (auth()->user()->can('update_role')) {
                        $actionButtons .= '<a href="' . route('admin.roles.edit', $row->id) . '" class="btn btn-sm btn-primary" title="' . trans('users.edit') . '" style="font-size: 16px;">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>';
                    }

                    if (auth()->user()->can('delete_role')) {
                        $actionButtons .= '<a onclick="return confirm(\'Are You Sure To Delete?\')"  href="' . route('admin.delete_role', $row->id) . '"  class="btn btn-sm btn-danger" title="' . trans('users.delete') . '" style="font-size: 16px;" onclick="return confirm(\'' . trans('users.confirm_delete') . '\')">
                                            <i class="bi bi-trash3"></i>
                                        </a>';
                    }

                    $actionButtons .= '</div>';
                    return $actionButtons;
                })
                ->rawColumns(['title', 'action'])
                ->make(true);
        }
        return view('dashbord.roles.index');
    }

    /********************************************/
    public function create()
    {
        // $data['permissions']  = $this->permissionsRepository->getAll();
        $data['sections']  = $this->permissions();
        // dd($data);
        // return view('dashbord.roles.form', $data);
    }

    /********************************************/
    public function store(RolesRequest $request)
    {
        try {
            // dd($request->all());
            $this->roleService->store($request);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.roles.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /********************************************/
    public function show(string $id)
    {
        //
    }

    /********************************************/
    public function edit(string $id)
    {
        $data['role']      = $this->rolesRepository->getById($id);
        $data['sections']  = $this->permissions();
        //dd($data['sections']);
        return view('dashbord.roles.edit', $data);
    }

    /********************************************/
    public function update(RolesRequest $request, string $id)
    {
        try {
            // dd($request->all());
            $this->roleService->update($request, $id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.roles.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /********************************************/
    public function destroy(string $id)
    {
        try {
            $this->roleService->delete($id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.users.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************/

    public function permissions()
    {
        return [
            'Dashboard' => permission::whereIn('name',  ['view_dashboard'])->get(),
            'sarf_band' => permission::whereIn('name', [
                'view_sarf_band',
                'add_sarf_band',
                'edit_sarf_band',
                'delete_sarf_band',
            ])->get(),
            'Subscriptions' => permission::whereIn('name', [
                'view_subscriptions',
                'add_subscription',
                'edit_subscription',
                'delete_subscription',
            ])->get(),
            'Employees' => permission::whereIn('name', [
                'view_employees',
                'add_employee',
                'edit_employee',
                'delete_employee',
                'view_employee_files',
                'add_employee_files',
                'read_employee_file',
                'download_employee_file',
                'delete_employee_file',
                'view_employee_details',
                'view_employee_masrofat',
                'add_employee_masrofat',
                'delete_employee_masrofat',
                'view_employee_revenues',
                'view_employee_transactions'
            ])->get(),
            'Roles' => permission::whereIn('name', [
                'list_roles',
                'create_role',
                'update_role',
                'delete_role',
            ])->get(),
            'Clients' => permission::whereIn('name', [
                'list_clients',
                'create_client',
                'update_client',
                'delete_client',
                'view_client_unpaid_invoices',
                'view_client_paid_invoices',
                'view_client_invoices',
                'add_client_invoice',
            ])->get(),
            'Invoices' => permission::whereIn('name', [
                'list_invoices',
                'delete_invoice',
                'pay_invoice',
                'view_invoice_details',
                'print_invoice',
                'redo_invoice',
            ])->get(),
            'Reports' => permission::whereIn('name', [
                'view_reports',
                'generate_reports',
            ])->get(),
            'Masrofat' => permission::whereIn('name', [
                'list_masrofat',
                'create_masrofat',
                'update_masrofat',
                'delete_masrofat',
            ])->get(),
            'Eradat' => permission::whereIn('name', [
                'list_eradat',
                'create_eradat',
                'update_eradat',
                'delete_eradat',
            ])->get(),
            'Users' => permission::whereIn('name', [
                'list_users',
                'create_user',
                'update_user',
                'delete_user',
                'change_user_status',
            ])->get(),
            'Notifications' => permission::whereIn('name', [
                'view_new_clients_notifications',
                'view_unpaid_invoices_notifications',
                'mark_notification_read',
            ])->get(),
            'Accounts' => Permission::whereIn('name', [
                'view_accounts',
                'create_account',
                'edit_account',
                'delete_account',
                'view_account_settings',
                'save_account_settings',
            ])->get(),

            'Financial Transactions' => Permission::whereIn('name', [
                'view_financial_transactions',
            ])->get(),

            'Account Transfers' => Permission::whereIn('name', [
                'view_account_transfers',
                'create_account_transfer',
                'redo_account_transfer',
            ])->get(),
        ];
        // dd($data);
        // return view('dashbord.roles.form', $data);
    }
}
