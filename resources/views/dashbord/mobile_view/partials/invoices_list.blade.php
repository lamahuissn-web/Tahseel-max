@forelse($invoices as $invoice)
<div class="col-12 mb-3">
    <div class="card bg-white shadow-sm border-0 rounded-3 overflow-hidden">
        <!-- Header -->
        <div class="card-header bg-primary py-2 min-h-auto px-3 border-0">
            <div class="d-flex justify-content-between w-100 text-white align-items-center">
                <span class="fs-7 fw-bold">{{ trans('invoices.due_date') ?? 'تاريخ الاستحقاق' }}: {{ $invoice->due_date ?? 'N/A' }}</span>
                <span class="fs-7 fw-bold dir-ltr text-end">{{ trans('invoices.amount') ?? 'المبلغ' }}: {{ number_format($invoice->amount ?? 0, 2) }} $</span>
            </div>
        </div>
        <!-- Body -->
        <div class="card-body p-3">
            <div class="d-flex flex-column gap-2 mb-3">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-person-fill fs-5 text-gray-500 me-2 ms-2"></i>
                    <span class="fw-bold fs-6 text-gray-800">{{ $invoice->client->name ?? 'N/A' }}</span>
                </div>

                @if(request('review') !== 'mine')
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-file-earmark-text fs-5 text-gray-500 me-2 ms-2"></i>
                    <span class="text-gray-600 fs-7">{{ trans('invoices.invoice_number') ?? 'رقم الفاتورة' }}: {{ $invoice->invoice_number }}</span>
                </div>
                @endif

                @if(request('review') !== 'mine')
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-tag fs-5 text-gray-500 me-2 ms-2"></i>
                    <span class="text-gray-600 fs-7">{{ 'نوع الفاتورة' }}: {{ $invoice->invoice_type == 'subscription' ? (trans('invoices.subscription') ?? 'الاشتراك') : (trans('invoices.service') ?? 'خدمة') }}</span>
                </div>
                @endif

                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-check-circle fs-5 text-gray-500 me-2 ms-2"></i>
                    <span class="badge {{ $invoice->status == 'paid' ? 'badge-light-success' : ($invoice->status == 'partial' ? 'badge-light-warning' : 'badge-light-danger') }} fs-8 fw-bold">
                        {{ trans('invoices.' . $invoice->status) ?? $invoice->status }}
                    </span>
                </div>

                @if(request('review') === 'mine')
                @php
                    $collectorRevenue = isset($invoice->revenues) ? $invoice->revenues->where('collected_by', auth()->guard('admin')->id())->sortByDesc('received_at')->first() : null;
                @endphp
                @if($collectorRevenue)
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-calendar-check fs-5 text-gray-500 me-2 ms-2"></i>
                    <span class="text-gray-600 fs-7">تاريخ التحصيل: {{ ($collectorRevenue->received_at) }}</span>
                </div>
                @endif
                @endif
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between gap-2 border-top pt-3 mt-2">
                @if(request('review') !== 'mine')
                <a href="{{ route('admin.print_invoice', $invoice->id) }}" class="btn btn-sm btn-light-secondary flex-grow-1 fw-bold fs-7 py-2 rounded-3 text-dark">
                    <i class="bi bi-printer fs-6 text-dark me-1"></i> {{ trans('invoices.print') ?? 'طباعة' }}
                </a>
                @endif

                @if($invoice->status != 'paid')
                <button type="button"
                    onclick="showPayModal('{{ route('admin.pay_invoice', $invoice->id) }}', {{ $invoice->remaining_amount }}, {{ $invoice->amount }}, `{{ str_replace('`', '\`', $invoice->notes ?? '') }}`, `{{ $invoice->paid_date ?? '' }}`)"
                    class="btn btn-sm btn-primary flex-grow-1 fw-bold fs-7 py-2 rounded-3 text-white">
                    <i class="bi bi-cash-coin fs-6 text-white me-1"></i> {{ trans('invoices.pay') ?? 'قبض' }}
                </button>
                @else
                @php
                    $phone = $invoice->client->phone ?? '';
                    $sanitizedPhone = preg_replace('/\D+/', '', $phone);
                    $invoiceUrl = URL::temporarySignedRoute('print_invoice', now()->addHours(48), ['id' => $invoice->id]);
                    $msg = 'فاتورة مدفوعة رقم ' . ($invoice->invoice_number) . ' بقيمة ' . number_format($invoice->paid_amount ?? 0, 2) . ' - رابط الفاتورة: ' . $invoiceUrl;
                    $encoded = rawurlencode($msg);
                @endphp
                @if(request('review') !== 'mine')
                    @if(!empty($sanitizedPhone))
                    <a href="https://wa.me/{{ $sanitizedPhone }}?text={{ $encoded }}"
                       target="_blank"
                       class="btn btn-sm btn-success flex-grow-1 fw-bold fs-7 py-2 rounded-3 text-white">
                        <i class="bi bi-send fs-6 text-white me-1"></i> إرسال عبر واتساب
                    </a>
                    @endif
                    @can('redo_invoice')
                    <a href="{{ route('admin.redo_invoice', $invoice->id) }}"
                       class="btn btn-sm btn-secondary flex-grow-1 fw-bold fs-7 py-2 rounded-3 text-white"
                       onclick="return confirm('{{ trans('invoices.confirm_redo') }}');">
                        <i class="bi bi-arrow-counterclockwise fs-6 text-white me-1"></i> {{ trans('invoices.redo_invoice') ?? 'تراجع' }}
                    </a>
                    @endcan
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="col-12 text-center py-5">
    <div class="text-muted fs-5">{{ trans('general.no_data_found') ?? 'لا توجد بيانات' }}</div>
</div>
@endforelse
