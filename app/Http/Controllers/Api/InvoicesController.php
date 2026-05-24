<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Admin;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Invoice;
use App\Models\Admin\Revenue;
use App\Models\Log;
use App\Notifications\InvoicePaidNotification;
use App\Traits\ResponseApi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoicesController extends Controller
{
    use ResponseApi;

    public function index(Request $request)
    {
        try {
            $query = Invoice::query();
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('invoice_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('subscription', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('address1', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

                $query->whereBetween('paid_date', [$startDate, $endDate]);
                // dd($startDate, $endDate, $query);
                $user = auth('api')->user();

                $query->whereHas('revenues', function ($q) use ($user) {
                    $q->where('collected_by', $user->id);
                });
            }


            $invoices = $query->whereNull('deleted_at')->orderBy('created_at', 'desc')->get();
            $data = [
                'invoices' => InvoiceResource::collection($invoices)
            ];
            return $this->responseApi($data, 'تم استرجاع الفواتير بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    public function unpaidInvoices(Request $request)
    {
        try {
            $query = Invoice::with(['client', 'subscription'])
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereNull('deleted_at');

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('invoice_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('subscription', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('client', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('address1', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            }

            $invoices = $query->orderBy('due_date', 'asc')->get();

            $data = [
                'invoices' => InvoiceResource::collection($invoices)
            ];

            return $this->responseApi($data, 'تم استرجاع الفواتير غير المدفوعة بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    public function paidInvoices(Request $request)
    {
        $rules = [
            'start_date' => 'required',
            'end_date' => 'required|after:start_date',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseApiError($validator->errors()->first());
        }

        try {
            $query = Invoice::with(['client', 'subscription', 'revenues.user', 'revenues' => function($q) {
                    $q->orderBy('received_at', 'desc');
                }])
                ->whereIn('status', ['paid', 'partial'])
                ->whereNull('deleted_at');

            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                if ($endDate->isFuture()) {
                    $endDate = Carbon::today()->endOfDay();
                }
                $query->whereHas('revenues', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('received_at', [$startDate, $endDate]);
                });
            }

            if ($user = auth('api')->user()) {
                $query->whereHas('revenues', function ($q) use ($user) {
                    $q->where('collected_by', $user->id);
                });
            }

            $invoices = $query->orderBy('paid_date', 'desc')->get();

            $processedInvoices = [];
            foreach ($invoices as $invoice) {
                foreach ($invoice->revenues as $revenue) {

                    // $paidBeforeThisRevenue = $revenue->amount + $revenue->remaining_amount;

                    $processedInvoices[] = [
                        'id' => $invoice->id,
                        'invoice_number' => ($invoice->client->client_type == 'satellite' ? 'SA-' : 'IN-') . $invoice->invoice_number,
                        'client_id' => $invoice->client->id,
                        'client_name' => $invoice->client->name,
                        'client_phone' => $invoice->client->phone,
                        'client_address' => $invoice->client->address1,
                        'subscription_id' => $invoice->subscription_id,
                        'subscription' => $invoice->subscription ? $invoice->subscription->name : trans('invoices.service'),
                        'amount' => $invoice->amount,
                        'paid_amount' => $revenue->amount,
                        // 'remaining_before_payment' => $paidBeforeThisRevenue,
                        'remaining_amount' => $revenue->remaining_amount,
                        'due_date' => $invoice->due_date ?? 'N/A',
                        'paid_date' => $revenue->received_at,
                        'collected_by' => $revenue->user->name,
                        // 'status' => $revenue->status,
                        'status' => 'paid',
                        'invoice_type' => $invoice->invoice_type,
                        'notes' => $revenue->notes,
                        'currency' => get_app_config_data('currency')
                    ];
                }
            }

            $data = [
                'invoices' => $processedInvoices
            ];
            return $this->responseApi($data, 'تم استرجاع الفواتير المدفوعة بنجاح');

        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    public function show($id)
    {
        try {
            $invoice = Invoice::with(['client', 'subscription', 'employee'])
                ->whereNull('deleted_at')
                ->findOrFail($id);

            $data = [
                'invoice' => new InvoiceResource($invoice)
            ];

            return $this->responseApi($data, 'تم استرجاع الفاتورة بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('الفاتورة غير موجودة.');
        }
    }


    public function payInvoice1($id, Request $request)
    {
        $rules = [
            'paid_amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseApiError($validator->errors()->first());
        }

        try {
            $invoice = Invoice::findOrFail($id);
            if (!$invoice) {
                return $this->responseApiError('الفاتورة غير موجودة');
            }

            $totalPaid = $invoice->paid_amount + $request->paid_amount;
            $remainingAmount = $invoice->amount - $totalPaid;

            if ($totalPaid > $invoice->amount) {
                return $this->responseApiError('لا يمكن أن يكون مبلغ الدفع أكبر من المبلغ المتبقي');
            }

            $invoice->paid_amount = $totalPaid;
            $invoice->remaining_amount = max($remainingAmount, 0);

            if ($remainingAmount == 0) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
                $invoice->status = 'partial';
            } else {
                $invoice->status = 'unpaid';
            }

            $invoice->paid_date = now();
            $invoice->notes = $request->notes ?? null;

            try {
                Revenue::create([
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'amount' => $request->paid_amount,
                    'collected_by' => auth('api')->id(),
                    'received_at' => now(),
                ]);
            } catch (\Exception $e) {
                return $this->responseApiError('حدث خطأ أثناء إنشاء الإيراد');
            }

            $accountId = auth('api')->user()->account_id ?? null;
            if (!$accountId) {
                return $this->responseApiError('هذا الحساب غير موجود');
            }

            try {
                FinancialTransaction::create([
                    'account_id'    => $accountId,
                    'amount'        => $request->paid_amount,
                    'date'          => now()->toDateString(),
                    'time'          => now()->toTimeString(),
                    'month'         => now()->month,
                    'year'          => now()->year,
                    'notes'         => 'سداد مستحقات الفاتورة رقم #' . $invoice->id,
                    'type'          => 'qapd',
                    'created_by'    => auth('api')->id(),
                ]);
            } catch (\Exception $e) {
                return $this->responseApiError('حدث خطأ أثناء إنشاء الحركة المالية');
            }

            $invoice->save();

            $data = [
                'invoice' => new InvoiceResource($invoice)
            ];

            return $this->responseApi($data, 'تم دفع الفاتورة بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما');
        }
    }
    public function payInvoice($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $invoice = Invoice::with('client')->findOrFail($id);

            if ($invoice->status === 'paid') {
                DB::rollBack();
                return $this->responseApiError('الفاتورة مدفوعة بالفعل');
            }

            if ($invoice->remaining_amount <= 0) {
                DB::rollBack();
                return $this->responseApiError('لا يوجد مبلغ متبق للدفع');
            }

            // Save old invoice data for log
            $oldInvoiceData = [
                'paid_amount' => $invoice->paid_amount,
                'remaining_amount' => $invoice->remaining_amount,
                'status' => $invoice->status,
                'paid_date' => $invoice->paid_date,
            ];

            $paidAmount = $invoice->remaining_amount;

            $invoice->paid_amount += $paidAmount;
            $invoice->remaining_amount = 0;
            $invoice->status = 'paid';
            $invoice->paid_date = now();
        //    dd($invoice->save());
            if (!$invoice->save()) {

                DB::rollBack();
                return $this->responseApiError('فشل في تحديث الفاتورة');
            }

            Revenue::create([
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'amount' => $paidAmount,
                'collected_by' => auth('api')->id(),
                'status' => 'paid',
                'remaining_amount' => 0,
                'received_at' => now(),
            ]);

            $accountId = auth('api')->user()->account_id ?? null;
          //  dd($accountId);
            if (!$accountId) {
                DB::rollBack();
                return $this->responseApiError('هذا الحساب غير موجود');
            }

            FinancialTransaction::create([
                'account_id'    => $accountId,
                'amount'        => $paidAmount,
                'date'          => now()->toDateString(),
                'time'          => now()->toTimeString(),
                'month'         => now()->month,
                'year'          => now()->year,
                'notes'         => 'سداد مستحقات الفاتورة رقم #' . $invoice->id,
                'type'          => 'qapd',
                'created_by'    => auth('api')->id(),
            ]);
            // $notificationMessage = 'تم دفع فاتورة رقم ' . $invoice->invoice_number . ' بقيمة ' . $paidAmount . ' جنيه';

            // $notificationMessage = sprintf(
            //     'تم دفع فاتورة رقم %s بقيمة %s جنيه للعميل %s بواسطة %s',
            //     $invoice->invoice_number,
            //     $paidAmount,
            //     $invoice->client->name ?? 'غير معروف',
            //     auth('api')->user()->name
            // );
            // $notificationMessage = sprintf(
            //     'تم تسديد مبلغ %s %s للفاتورة رقم %s (العميل: %s) - تم الدفع بواسطة %s في %s',
            //     number_format($paidAmount, 2),
            //     get_app_config_data('currency'),
            //     $invoice->invoice_number,
            //     $invoice->client->name ?? 'غير محدد',
            //     auth('api')->user()->name,
            //     // now()->format('Y-m-d H:i')
            //     $invoice->due_date
            // );
              // dd('ddd');
            $notificationMessage = sprintf(
                'تم دفع مبلغ %s %s للعميل %s، وكان تاريخ الاستحقاق %s. (تمت العملية بواسطة: %s)',
                number_format($paidAmount, 2),
                get_app_config_data('currency'),
                optional($invoice->client)->name ?? 'غير محدد',
                $invoice->due_date,
                auth('api')->user()->name
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
                    $paidAmount,
                    auth('api')->user(),
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
                        'amount' => $paidAmount,
                        'initiator' => auth('api')->user()->name,
                        'invoice_details' => [
                            'number' => $invoice->invoice_number,
                            'date' => $invoice->paid_date,
                            'client' => optional($invoice->client)->name ,
                        ]
                    ],
                    null
                );
            }

            DB::commit();

            // Refresh invoice to get latest data
            $invoice->refresh();

            // Create log for invoice payment
            $logDescription = sprintf(
                'تم دفع مبلغ %s %s للعميل %s للفاتورة رقم %s (تمت العملية بواسطة: %s)',
                number_format($paidAmount, 2),
                get_app_config_data('currency') ?? 'جنيه',
                optional($invoice->client)->name ?? 'غير محدد',
                $invoice->invoice_number,
                auth('api')->user()->name
            );

            Log::create([
                'action' => 'invoice_paid',
                'description' => $logDescription,
                'old_data' => json_encode($oldInvoiceData, JSON_UNESCAPED_UNICODE),
                'new_data' => json_encode([
                    'paid_amount' => $invoice->paid_amount,
                    'remaining_amount' => $invoice->remaining_amount,
                    'status' => $invoice->status,
                    'paid_date' => $invoice->paid_date,
                ], JSON_UNESCAPED_UNICODE),
                'model_type' => get_class($invoice),
                'model_id' => $invoice->id,
                'user_id' => auth('api')->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return $this->responseApi(null, 'تم دفع الفاتورة بنجاح');
        } catch (ModelNotFoundException $e) {
           // dd($e);
            DB::rollBack();
            return $this->responseApiError('الفاتورة غير موجودة');
        } catch (\Exception $e) {
          //  dd($e);
            DB::rollBack();
            return $this->responseApiError('حدث خطأ ما: ' . $e->getMessage());
        }
    }

    public function print_invoice($id)
    {
        try {
            $printLink = route('print_invoice', $id);

            return $this->responseApi($printLink);

        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما');
        }
    }
}
