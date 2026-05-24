<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\account\SaveAccountSettingsRequest;
use App\Http\Requests\Admin\account\SaveRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\AccountSettings;
use App\Models\Admin\FinancialTransaction;
use App\Services\AccountService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $accountsRepository;
    protected $accountService;
    protected $usersRepository;

    public function __construct(BasicRepositoryInterface $basicRepository, AccountService $accountService)
    {
        $this->middleware('can:view_accounts', ['only' => ['accounts']]);
        $this->middleware('can:create_account', ['only' => ['add_account']]);
        $this->middleware('can:edit_account', ['only' => ['edit_account']]);
        $this->middleware('can:delete_account', ['only' => ['destroy']]);
        $this->middleware('can:view_account_settings', ['only' => ['account_setting']]);
        $this->middleware('can:save_account_settings', ['only' => ['save_account_setting']]);

        $this->accountsRepository = createRepository($basicRepository, new Account());
        $this->usersRepository   = createRepository($basicRepository, new Admin());
        $this->accountService   = $accountService;
    }

    public function accounts()
    {
        $accounts = $this->accountsRepository->getAll();
        $data['accounts'] = $accounts->isNotEmpty() ? $accounts->toQuery()->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->with(['children' => function ($query) {
                $query->withSum('financialTransactions', 'amount');
            }])
            ->withSum('financialTransactions', 'amount')
            ->get() : collect();
        $data['users'] = $this->usersRepository->getAll();
        // dd($data);
        // dd(auth()->user());
        return view('dashbord.accounts.index', $data);
    }
    public function get_ajax_accounts()
    {
        if (request()->ajax()) {
            try {
                $accounts = $this->accountsRepository->getWithRelations(['admin', 'user', 'parent']);
                $data = $accounts->isNotEmpty() ? $accounts->toQuery()->where('deleted_at', null)->orderBy('created_at', 'desc')->get() : collect();

                $counter = 0;

                return DataTables::of($data)
                    ->addColumn('id', function () use (&$counter) {
                        $counter++;
                        return $counter;
                    })
                    ->addColumn('name', function ($row) {
                        return $row->name;
                    })
                    ->addColumn('parent_account', function ($row) {
                        return $row->parent ? $row->parent->name : 'No Parent';
                    })
                    ->addColumn('assigned_user', function ($row) {
                        return $row->user ? $row->user->name : 'N/A';
                    })
                    ->addColumn('created_by', function ($row) {
                        return $row->admin ? $row->admin->name : 'N/A';
                    })
                    ->addColumn('action', function ($row) {
                        $actionButtons = '';

                        // if (auth()->user()->can('edit_account')) {
                        $actionButtons .= '<a data-bs-toggle="modal" data-bs-target="#modalAccounts" onclick="edit_account(' . $row->id . ')" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>';
                        // }

                        // if (auth()->user()->can('delete_account')) {
                        $actionButtons .= '<a onclick="return confirm(\'Are You Sure To Delete?\')" href="' . route('admin.delete_account', $row->id) . '"  class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </a>';
                        // }

                        return $actionButtons;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }

    /********************************************/
    public function add_account(Request $request)
    {
        $validated_data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:tbl_accounts,id',
            // 'user_id' => 'nullable|exists:admins,id|unique:admins,account_id',
        ]);
        try {
            // dd($request->all());
            // if (!empty($validated_data['user_id'])) {
            //     $existingAdmin = Admin::where('account_id', '!=', null)
            //         ->where('id', $validated_data['user_id'])
            //         ->first();
            //     dd($existingAdmin);
            //     if ($existingAdmin) {
            //         toastr()->addError(trans('accounts.this_user_is_already_assigned_to_an_account.'));
            //         return redirect()->back();
            //     }
            // }


            // $user_id = $validated_data['user_id'];
            // unset($validated_data['user_id']);
            if (empty($request->row_id)) {
                $validated_data['created_by'] = auth()->user()->id;
                $account = $this->accountsRepository->create($validated_data);
            } else {
                $validated_data['updated_by'] = auth()->user()->id;
                $account = $this->accountsRepository->update($request->row_id, $validated_data);
                $account = $this->accountsRepository->getById($request->row_id);
            }

            $level = $this->calculateLevel($account->id);
            $this->accountsRepository->update($account->id, ['level' => $level]);

            // if (!empty($user_id)) {
            //     $user = $this->usersRepository->getById($user_id);
            //     if ($user) {
            //         $user->update(['account_id' => $account->id]);
            //     }
            // }

            toastr()->addSuccess(trans('accounts.account_added_successfully'));
            return redirect()->route('admin.accounts');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function calculateLevel($account_id, $level = 1)
    {
        $account = $this->accountsRepository->getById($account_id);

        if ($account->parent_id) {
            return $this->calculateLevel($account->parent_id, $level + 1);
        }

        return $level;
    }

    public function edit_account($id)
    {
        $data['all_data'] = $this->accountsRepository->getById($id);
        Admin::where('account_id', $data['all_data']->id)->first();
        $admin = $this->usersRepository->getBywhere(['account_id' => $data['all_data']->id])->first();
        $data['account_id'] = $admin ? $admin->account_id : null;
        Log::info($data);
        return response()->json($data);
    }

    /********************************************/
    public function show(string $id)
    {
        //
    }

    /********************************************/
    public function destroy(string $id)
    {
        try {
            $this->accountsRepository->delete($id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.accounts');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************/

    public function account_setting()
    {
        $data['accountSetting'] = AccountSettings::first();
        $data['accounts'] = $this->accountsRepository->getAll();

        return view('dashbord.accounts.account_settings', $data);
    }

    public function save_account_setting(SaveAccountSettingsRequest $request)
    {
        try {
            $accountSetting = AccountSettings::firstOrNew([]);

            $accountSetting->general_account_id = $request->general_account_id;
            $accountSetting->masrofat_account_id = $request->masrofat_account_id;
            $accountSetting->employee_account_id = $request->employee_account_id;
            $accountSetting->accountant_account_id = $request->accountant_account_id ?? 10;

            $accountSetting->save();

            return redirect()->route('admin.account_settings');
        } catch (\Exception $e) {
            test($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function get_transactions($id)
    {
        $account = Account::findOrFail($id);
        $transactions = FinancialTransaction::with(['account', 'admin'])->where('account_id', $id)->whereNull('deleted_at')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'account_name' => $account->name,
            'transactions' => $transactions
        ]);
    }
}
