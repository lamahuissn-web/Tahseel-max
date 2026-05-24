<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasrofatResource;
use App\Models\Admin;
use App\Models\Admin\Employee;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Invoice;
use App\Models\Admin\Masrofat;
use App\Models\Clients;
use App\Traits\ResponseApi;
use Illuminate\Http\Request;

class MasrofatController extends Controller
{
    use ResponseApi;

    public function index(Request $request)
    {
        try{
            $query = Masrofat::with(['employee', 'sarf_band', 'user'])
                ->orderBy('created_at', 'desc');

            if ($request->has('from_date') && $request->from_date != '') {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->has('to_date') && $request->to_date != '') {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            if ($request->has('month') && $request->month != '') {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->has('year') && $request->year != '') {
                $query->whereYear('created_at', $request->year);
            }

            $masrofat = $query->get();
            $total=$masrofat->sum('value');

            $masrofat = MasrofatResource::collection($masrofat);
            return $this->responseApi_v2($masrofat, 'تم استرجاع المصروفات بنجاح',true,$total);
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    public function getSystemStatistics(){
        try {
            $internetClientsCount = Clients::where('client_type', 'internet')->where('is_active', 1)->count();
            $satelliteClientsCount = Clients::where('client_type', 'satellite')->where('is_active', 1)->count();

            $paidInvoices = Invoice::where('status', 'paid');
            $totalPaidInvoicesAmount = $paidInvoices->sum('amount');
            $totalActuallyPaid = $paidInvoices->sum('paid_amount');

            $unpaidInvoices = Invoice::where('status', 'unpaid');
            $totalUnpaidInvoicesAmount = $unpaidInvoices->sum('amount');
            $totalUnpaidRemaining = $unpaidInvoices->sum('remaining_amount');

            $partialInvoices = Invoice::where('status', 'partial');
            $totalPartialPaid = $partialInvoices->sum('paid_amount');
            $totalPartialRemaining = $partialInvoices->sum('remaining_amount');

            // $totalPaid = Invoice::sum('paid_amount');
            $allData = Admin::withSum('financialTransactions', 'amount')
                            ->get();
            $totalPaid = $allData->sum('financial_transactions_sum_amount');

            $totalRemaining = Invoice::sum('remaining_amount');

            $employeesCount = Employee::count();
            $usersCount = Admin::whereNull('deleted_at')
                            ->where('status', '1')
                            ->count();

            $accountantTotal = FinancialTransaction::where('account_id', 10)
                            ->sum('amount');
            $accountAndPaid = $accountantTotal + $totalPaid;
            return response()->json([
                'result' => true,
                'message' => 'تم استرجاع الإحصائيات بنجاح',
                'data' => [
                    [
                        'name' => 'عدد الزبائن الانترنت',
                        'count' => (string)$internetClientsCount
                    ],
                    [
                        'name' => 'عدد الزبائن الستاليت',
                        'count' => (string)$satelliteClientsCount
                    ],
                    [
                        'name' => 'اجمالى المدفوع',
                        'count' => (string)$totalPaid
                    ],
                    [
                        'name' => 'اجمالى الغير مدفوع',
                        'count' => (string)$totalRemaining
                    ],
                    [
                        'name' => 'عدد الموظفين',
                        'count' => (string)$employeesCount
                    ],
                    [
                        'name' => 'عدد المستخدمين',
                        'count' => (string)$usersCount
                    ],
                    [
                        'name' => 'اجمالى المدفوع + اجمالى المحاسب',
                        'count' => (string)$accountAndPaid
                    ],
                    // [
                    //     'name' => 'اجمالى حساب المحاسب',
                    //     'count' => (string)$accountantTotal
                    // ],

                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
