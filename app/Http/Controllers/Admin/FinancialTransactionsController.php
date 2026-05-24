<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\FinancialTransaction;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FinancialTransactionsController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $admin_view = 'dashbord.accounts.financial_transactions';
    protected $financialTransactionsRepository;
    // protected $revenueService;

    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->middleware('can:view_financial_transactions')->only('index');

        $this->financialTransactionsRepository = createRepository($basicRepository, new FinancialTransaction());
        // $this->revenueService = $revenueService;
    }


    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = FinancialTransaction::select([
                'tbl_financial_transactions.id',
                'tbl_financial_transactions.account_id',
                'tbl_financial_transactions.amount',
                'tbl_financial_transactions.date',
                'tbl_financial_transactions.time',
                'tbl_financial_transactions.type',
                'tbl_financial_transactions.notes',
                'tbl_financial_transactions.created_at'
            ])
            ->with(['account' => function($q) {
                $q->select('id', 'name')
                  ->with(['user' => function($uq) {
                      $uq->select('id', 'name', 'account_id');
                  }]);
            }])
            ->whereNull('tbl_financial_transactions.deleted_at')
            ->orderBy('tbl_financial_transactions.created_at', 'desc');

            return DataTables::of($query)
                ->addColumn('id', function ($row) {
                    return $row->id ?? 'N/A';
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount ?? 'N/A';
                })
                ->addColumn('account', function ($row) {
                    return $row->account ? $row->account->name : 'N/A';
                })
                ->addColumn('assigned_user', function ($row) {
                    return $row->account?->user ? $row->account->user->name : 'N/A';
                })
                ->addColumn('date', function ($row) {
                    return $row->date ?? 'N/A';
                })
                ->addColumn('time', function ($row) {
                    return $row->time ?? 'N/A';
                })
                ->addColumn('type', function ($row) {
                    return trans('accounts.' . $row->type);
                })
                // ->addColumn('notes', function ($row) {
                //     return $row->notes ?? 'N\A';
                // })
                ->addColumn('notes', function ($row) {
                    if ($row->notes) {
                        $pattern = '/#(\d+)/';
                        // $replacement = '<a href="javascript:void(0)" onclick="invoice_details(\'' . route('admin.invoice_details', '$1') . '\')"
                        //                 class="text-primary fw-bold" title="' . trans('invoices.view_details') . '">#$1</a>';

                        // return preg_replace($pattern, $replacement, $row->notes);
                        return preg_replace_callback($pattern, function ($matches) {
                            $invoiceId = $matches[1];
                            $url = route('admin.invoice_details', $invoiceId);

                            return '<a href="javascript:void(0)" onclick="invoice_details(\'' . $url . '\')"
                                    class="text-primary fw-bold" style="text-decoration: underline;" title="' . trans('invoices.view_details') . '">#' . $invoiceId . '</a>';
                        }, $row->notes);
                    }

                    return 'لا توجد ملاحظات';
                })
                ->rawColumns(['notes'])
                ->make(true);
        }
        return view($this->admin_view . '.index');
    }

    /***********************************************/

    public function getAccountBalance($id)
    {
        $balance = FinancialTransaction::where('account_id', $id)->sum('amount');

        return response()->json(['balance' => $balance]);
    }
}
