@extends('dashbord.layouts.mobile_master')

@section('toolbar')
@endsection

@section('content')
<div class="row g-5 g-xl-8 mb-5 mb-xl-10">
    <!-- Header with Back Button -->
    <div class="col-12 mb-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.mobile_clients') }}" class="btn btn-icon btn-light-primary rounded-circle w-40px h-40px">
                <i class="bi bi-arrow-right fs-2"></i>
            </a>
            <h3 class="mb-0 fw-bold">تفاصيل العميل</h3>
        </div>
    </div>

    <!-- Client Info Card -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary py-3 border-0">
                <h5 class="text-white mb-0 fw-bold">
                    <i class="bi bi-person-circle me-2"></i>
                    {{ $client->name }}
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex flex-column">
                            <span class="text-muted fs-7 mb-1">الهاتف</span>
                            <span class="fw-bold text-gray-800 dir-ltr">{{ $client->phone ?? 'غير متوفر' }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex flex-column">
                            <span class="text-muted fs-7 mb-1">النوع</span>
                            <span class="fw-bold text-gray-800">{{ $client->client_type ?? 'غير محدد' }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex flex-column">
                            <span class="text-muted fs-7 mb-1">الحالة</span>
                            <span class="badge {{ $client->is_active ? 'badge-light-success' : 'badge-light-danger' }} fs-7 fw-bold">
                                {{ $client->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex flex-column">
                            <span class="text-muted fs-7 mb-1">الرصيد المتبقي</span>
                            <span class="fw-bold text-primary fs-5 dir-ltr">{{ number_format($client->remaining_balance ?? 0, 2) }} $</span>
                        </div>
                    </div>
                    @if($client->address1)
                    <div class="col-12">
                        <div class="d-flex flex-column">
                            <span class="text-muted fs-7 mb-1">العنوان</span>
                            <span class="fw-bold text-gray-800">{{ $client->address1 }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Invoices -->
    <div class="col-12">
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-5 fw-bold" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active text-primary" data-bs-toggle="tab" href="#unpaid_invoices" role="tab">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    الفواتير غير المدفوعة
                    <span class="badge badge-light-danger ms-2">{{ $unpaidInvoices->count() }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link text-success" data-bs-toggle="tab" href="#paid_invoices" role="tab">
                    <i class="bi bi-check-circle me-2"></i>
                    الفواتير المدفوعة
                    <span class="badge badge-light-success ms-2">{{ $paidInvoices->count() }}</span>
                </a>
            </li>
        </ul>

        <div class="tab-content" id="invoicesTabContent">
            <!-- Unpaid Invoices Tab -->
            <div class="tab-pane fade show active" id="unpaid_invoices" role="tabpanel">
                <div class="row g-3">
                    @forelse($unpaidInvoices as $invoice)
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-body p-3">
                                @php
                                    $statusLabel = $invoice->status == 'partial' ? 'مدفوع جزئياً' : 'غير مدفوع';
                                @endphp
                                <div class="mb-1 text-muted fs-7">
                                    حالة الفاتورة: <span class="fw-bold {{ $invoice->status == 'partial' ? 'text-warning' : 'text-danger' }}">{{ $statusLabel }}</span>
                                </div>
                                <div class="mb-1 text-muted fs-7">
                                    المبلغ: <span class="fw-bold text-gray-800 dir-ltr">{{ number_format($invoice->amount, 2) }} $</span>
                                </div>
                                <div class="mb-1 text-muted fs-7">
                                    المتبقي: <span class="fw-bold text-primary dir-ltr">{{ number_format($invoice->remaining_amount, 2) }} $</span>
                                </div>
                                <div class="mb-1 text-muted fs-7">
                                    تاريخ الاستحقاق: <span class="fw-bold text-gray-800">{{ $invoice->due_date }}</span>
                                </div>
                                @if(!empty($invoice->notes))
                                <div class="mb-1 text-muted fs-7">
                                    ملاحظات: <span class="fw-bold text-gray-800">{{ $invoice->notes }}</span>
                                </div>
                                @endif
                                @if($invoice->status != 'paid')
                                <div class="mt-3">
                                    <button type="button"
                                        onclick="showPayModal('{{ route('admin.pay_invoice', $invoice->id) }}', {{ $invoice->remaining_amount }}, {{ $invoice->amount }}, `{{ str_replace('`', '\\`', $invoice->notes ?? '') }}`, `{{ $invoice->paid_date ?? '' }}`)"
                                        class="btn btn-sm btn-primary w-100 fw-bold">
                                        <i class="bi bi-cash-coin me-1"></i> دفع الفاتورة
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-check-circle fs-5x text-success mb-3 d-block"></i>
                        <div class="text-muted fs-5">لا توجد فواتير غير مدفوعة</div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Paid Invoices Tab -->
            <div class="tab-pane fade" id="paid_invoices" role="tabpanel">
                <div class="row g-3">
                    @forelse($paidInvoices as $invoice)
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-body p-3">
                                @php
                                    $collectorName = optional($invoice->revenues->sortByDesc('received_at')->first())->user->name ?? null;
                                @endphp
                                <div class="mb-1 text-muted fs-7">
                                    حالة الفاتورة: <span class="fw-bold text-success">مدفوع</span>
                                </div>
                                <div class="mb-1 text-muted fs-7">
                                    المبلغ: <span class="fw-bold text-success dir-ltr">{{ number_format($invoice->amount, 2) }} $</span>
                                </div>
                                <div class="mb-1 text-muted fs-7">
                                    تاريخ الاستحقاق: <span class="fw-bold text-gray-800">{{ $invoice->due_date ?? 'N/A' }}</span>
                                </div>
                                <div class="mb-1 text-muted fs-7">
                                    تاريخ الدفع: <span class="fw-bold text-gray-800">{{ formatDateDayDisplay($invoice->paid_date) }}</span>
                                </div>
                                @if($collectorName)
                                <div class="mb-1 text-muted fs-7">
                                    المحصل: <span class="fw-bold text-gray-800">{{ $collectorName }}</span>
                                </div>
                                @endif
                                @if(!empty($invoice->notes))
                                <div class="mb-1 text-muted fs-7">
                                    ملاحظات: <span class="fw-bold text-gray-800">{{ $invoice->notes }}</span>
                                </div>
                                @endif
                                @php
                                    $sanitizedPhone = preg_replace('/\D+/', '', $client->phone ?? '');
                                    $amountValue = number_format($invoice->paid_amount ?? $invoice->amount ?? 0, 2);
                                    $monthDate = formatDateDayDisplay($invoice->due_date);
                                    $userName = auth()->guard('admin')->user()->name ?? '';
                                    $contactPhone = $client->phone ?? '';
                                    $msg  = "وصلنا من السيد: {$client->name}\n";
                                    $msg .= "مبلغاً وقدره: {$amountValue} " . get_app_config_data('currency') . "\n";
                                    $msg .= "عن شهر: {$monthDate}\n";
                                    $msg .= "من المستخدم: {$userName} -------- للمراجعة\n";
                                    $msg .= "الاتصال على:\n";
                                    $msg .= get_app_config_data('phone_service');
                                    $encoded = rawurlencode($msg);
                                @endphp
                                <div class="mt-3 d-flex gap-2">
                                    @if(!empty($sanitizedPhone))
                                    <a href="https://wa.me/{{ $sanitizedPhone }}?text={{ $encoded }}"
                                       target="_blank"
                                       class="btn btn-sm btn-success fw-bold">
                                        <i class="bi bi-send me-1"></i> إرسال رسالة للعميل
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.print_invoice', $invoice->id) }}" class="btn btn-sm btn-light-secondary fw-bold text-dark">
                                        <i class="bi bi-printer me-1"></i> طباعة
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox fs-5x text-muted mb-3 d-block"></i>
                        <div class="text-muted fs-5">لا توجد فواتير مدفوعة</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal (same as invoices page) -->
<div class="modal fade" id="pay_modal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">دفع الفاتورة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pay_form" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">المبلغ الإجمالي</label>
                        <input type="number" step="0.01" class="form-control" name="invoice_amount" id="invoice_amount" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold required">المبلغ المدفوع</label>
                        <input type="number" step="0.01" class="form-control" name="paid_amount" id="paid_amount" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">تاريخ الدفع</label>
                        <input type="date" class="form-control" name="paid_date" id="paid_date" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تأكيد الدفع</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function showPayModal(url, remaining, total, notes, paid_date) {
        $('#pay_form').attr('action', url);
        $('#invoice_amount').val(remaining);
        $('#paid_amount').val(remaining);
        $('#notes').val(notes);
        if (paid_date) {
            $('#paid_date').val(paid_date.split(' ')[0]);
        }
        $('#pay_modal').modal('show');
    }
</script>
@endsection
