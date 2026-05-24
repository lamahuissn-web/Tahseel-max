<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Invoice;
use App\Models\Admin\MonthlyInvoiceGeneration;
use App\Models\Admin\Revenue;
use App\Models\Admin\Subscription;
use App\Models\Clients;
use App\Traits\ImageProcessing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InvoiceService
{

    use ImageProcessing;
    protected $InvoiceRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->InvoiceRepository   = createRepository($basicRepository, new Invoice());
    }
    /************************************************/
    public function store($request)
    {
        $validated_data = $request->validated();
        $validated_data['created_by'] = auth()->user()->id;
        // dd($validated_data);

        return $this->InvoiceRepository->create($validated_data);
    }
    /************************************************/
    public function get_client($id)
    {
        return $this->InvoiceRepository->getById($id);
    }
    /************************************************/
    public function update($request, $id)
    {
        $validated_data = $request->validated();
        $validated_data['updated_by'] = auth()->user()->id;
        // dd($validated_data);

        return $this->InvoiceRepository->update($id, $validated_data);
    }
    /**************************************************/
    // public function payInvoice($id, $request)
    // {
    //     $invoice = $this->InvoiceRepository->getById($id);
    //     // $invoice->amount = $request->invoice_amount;
    //     // dd($invoice);
    //     if (!$request->paid_amount && isset($request->invoice_amount) && $request->invoice_amount != $invoice->amount) {
    //         $invoice->amount = $request->invoice_amount;
    //         $invoice->remaining_amount = max($invoice->amount - $invoice->paid_amount, 0);
    //     }

    //     if ($request->paid_amount) {
    //         $totalPaid = $invoice->paid_amount + $request->paid_amount;
    //         $remainingAmount = $invoice->amount - $totalPaid;

    //         // dd($invoice, $remainingAmount, $totalPaid);
    //         if ($totalPaid > $invoice->amount) {
    //             return redirect()->back()->with('error', trans('invoices.payment_exceeds_invoice_amount'));
    //         }
    //         $invoice->paid_amount = $totalPaid;
    //         $invoice->remaining_amount = $remainingAmount;

    //         if ($remainingAmount == 0) {
    //             $invoice->status = 'paid';
    //         } elseif ($totalPaid > 0) {
    //             $invoice->status = 'partial';
    //         } else {
    //             $invoice->status = 'unpaid';
    //         }

    //         $invoice->paid_date = now();
    //         $invoice->notes = $request->notes ?? null;

    //         $collectedBy = auth()->check() && auth()->user()->is_employee
    //             ? auth()->user()->emp_id
    //             : auth()->id();

    //         Revenue::create([
    //             'invoice_id' => $invoice->id,
    //             'client_id' => $invoice->client_id,
    //             'amount' => $request->paid_amount,
    //             'collected_by' => $collectedBy,
    //             'received_at' => now(),
    //         ]);

    //         $accountId = auth()->user()->account_id ?? null;

    //         if (!$accountId) {
    //             return redirect()->back()->with('error', trans('invoices.no_account_found'));
    //         }

    //         FinancialTransaction::create([
    //             'account_id'    => $accountId,
    //             'amount'        => $request->paid_amount,
    //             'date'          => now()->toDateString(),
    //             'time'          => now()->toTimeString(),
    //             'month'         => now()->month,
    //             'year'          => now()->year,
    //             'notes'         => 'سداد مستحقات الفاتورة رقم ' . $invoice->id,
    //             'type'          => 'qapd',
    //             'created_by'    => auth()->id(),
    //         ]);
    //     }
    //     $invoice->save();


    //     return $invoice;
    // }

    public function payInvoice($id, $request)
    {
        $invoice = $this->InvoiceRepository->getById($id);
        if (!$invoice) {
            return redirect()->back()->with('error', trans('invoices.invoice_not_found'));
        }

        if ($request->paid_amount && !is_numeric($request->paid_amount)) {
            return redirect()->back()->with('error', trans('invoices.invalid_paid_amount'));
        }

        if ($request->invoice_amount && !is_numeric($request->invoice_amount)) {
            return redirect()->back()->with('error', trans('invoices.invalid_invoice_amount'));
        }

        if (!$request->paid_amount && isset($request->invoice_amount) && $request->invoice_amount != $invoice->amount) {
            $invoice->amount = $request->invoice_amount;
            $invoice->remaining_amount = max($invoice->amount - $invoice->paid_amount, 0);
        }
        if ($request->paid_date) {
            $invoice->paid_date = $request->paid_date;
        }
        if ($request->notes) {
            $invoice->notes = $request->notes;
        }

        if ($request->paid_amount) {
            $totalPaid = $invoice->paid_amount + $request->paid_amount;
            $remainingAmount = $invoice->amount - $totalPaid;

            if ($totalPaid > $invoice->amount) {
                return redirect()->back()->with('error', trans('invoices.payment_exceeds_invoice_amount'));
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
                    'remaining_amount' => $remainingAmount,
                    'status' => $remainingAmount > 0 ? 'partial' : 'paid',
                    'collected_by' => auth()->id(),
                    'received_at' => now(),
                ]);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', trans('invoices.revenue_creation_failed'));
            }

            $accountId = auth()->user()->account_id ?? null;
            if (!$accountId) {
                return redirect()->back()->with('error', trans('invoices.no_account_found'));
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
                    'created_by'    => auth()->id(),
                ]);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', trans('invoices.financial_transaction_creation_failed'));
            }
        }

        $invoice->save();

        return redirect()->back()->with('success', trans('forms.success'));
    }

    public function canGenerateInvoices(): bool
    {
        // $currentDay = now()->day;
        // if ($currentDay > 15) {
        //     return false;
        // }

        return !MonthlyInvoiceGeneration::where('year_month', now()->format('Y-m'))->exists();
    }

    public function generateMonthlyInvoices()
    {
        $currentYearMonth = Carbon::now()->format('Y-m');
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        if (!$this->canGenerateInvoices()) {
            return redirect()->back()->with('error', trans('clients.invoices_already_created'));
        }

        try {
            $clients = Clients::whereNull('deleted_at')
                                ->where('is_active', 1)
                                ->get();
            $invoicesCreated = 0;

            foreach ($clients as $client) {
                $clientStartDate = Carbon::parse($client->start_date);

                if ($clientStartDate->startOfMonth()->greaterThanOrEqualTo($currentMonthStart)) {
                    continue;
                }

                $existingInvoice = Invoice::where('client_id', $client->id)
                                        ->whereMonth('due_date', $currentMonth)
                                        ->whereYear('due_date', $currentYear)
                                        ->where('auto_generated', true)
                                        ->exists();

                if ($existingInvoice) {
                    continue;
                }

                $startDay = Carbon::parse($client->start_date)->day;
                $dueDate = Carbon::create($currentYear, $currentMonth, $startDay);

                if (!$dueDate->isValid()) {
                    $dueDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
                }


                $invoiceNumber = $this->getNextInvoiceNumber();

                Invoice::create([
                    'client_id' => $client->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $client->price,
                    'remaining_amount' => $client->price,
                    'subscription_id' => $client->subscription_id,
                    'enshaa_date' => Carbon::now(),
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                    'auto_generated' => true,
                ]);

                $invoicesCreated++;
            }

            MonthlyInvoiceGeneration::create([
                'year_month' => $currentYearMonth,
                'generated_at' => now(),
                'invoices_created' => $invoicesCreated,
                'generated_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'تم إنشاء ' . $invoicesCreated . ' فاتورة بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء إنشاء الفواتير: ' . $e->getMessage());
        }
    }

    private function getNextInvoiceNumber()
    {
        $lastInvoice = Invoice::withTrashed()->orderBy('id', 'desc')->first();

        if ($lastInvoice) {
            if (is_numeric($lastInvoice->invoice_number)) {
                return (int)$lastInvoice->invoice_number + 1;
            }
        }

        return (int)1;
    }
}
