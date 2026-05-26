<style>
.inv-list-item {
    display: flex;
    align-items: center;
    padding: 12px 14px;
    gap: 8px;
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.inv-list-item:last-child {
    margin-bottom: 0;
}
.inv-list-item .inv-info {
    flex: 1;
    min-width: 0;
}
.inv-list-item .inv-info .inv-number {
    font-weight: 700;
    font-size: 14px;
    color: #0d6efd;
    display: block;
}
.inv-list-item .inv-info .inv-meta {
    font-size: 12px;
    color: #888;
    margin-top: 2px;
}
.inv-list-item .inv-remaining {
    font-weight: 700;
    font-size: 15px;
    color: #dc3545;
    white-space: nowrap;
    margin-left: 8px;
}
.inv-list-item .btn-pay {
    background: #198754;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
}
.inv-list-item .btn-pay:active {
    opacity: 0.8;
}
.inv-card-wrapper {
    margin-bottom: 10px;
}
.inv-details {
    background: #f9fafc;
    border: 1px solid #e8e8e8;
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 10px 14px;
    font-size: 13px;
    color: #555;
    margin-top: -10px;
}
.inv-details table {
    width: 100%;
    border-collapse: collapse;
}
.inv-details table td {
    padding: 4px 0;
}
.inv-details table td:first-child {
    font-weight: 600;
    color: #333;
    width: 40%;
}
.inv-header-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 12px;
    border-bottom: 2px solid #ffc107;
    margin-bottom: 4px;
}
.inv-header-summary .client-name {
    font-weight: 700;
    font-size: 16px;
}
.inv-header-summary .total-remaining {
    background: #fff3cd;
    color: #856404;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
</style>

<div class="inv-header-summary">
    <span class="client-name">{{ $client->name ?? trans('clients.client_details') }}</span>
    <div class="d-flex align-items-center gap-2">
        <span class="total-remaining">
            {{ number_format($unpaidInvoices->sum('remaining_amount'), 2) }} {{ get_app_config_data('currency') }}
        </span>
        @php
            $cleanPhone = preg_replace('/[^0-9]/', '', $client->phone ?? '');
            $hasValidPhone = strlen($cleanPhone) >= 7 && !preg_match('/^0+$/', $cleanPhone);
        @endphp
        @if($hasValidPhone)
        <button class="btn btn-sm btn-success d-flex align-items-center gap-1 whatsapp-reminder-btn" onclick="sendWhatsAppReminder({{ $client->id }})">
            <i class="bi bi-whatsapp fs-6"></i>
            <span class="d-none d-sm-inline">{{ trans('clients.whatsapp_send_reminder') }}</span>
        </button>
        @endif
    </div>
</div>

@if($unpaidInvoices->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-check-circle-fill fs-1 text-success"></i>
        <h5 class="mt-2 text-muted">{{ trans('clients.no_unpaid_invoices') }}</h5>
    </div>
@else
    @foreach($unpaidInvoices as $invoice)
        <div class="inv-card-wrapper">
            <div class="inv-list-item" onclick="toggleInvDetails({{ $invoice->id }})" style="cursor:pointer;">
                <div class="inv-info">
                    <span class="inv-number">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</span>
                    <span class="inv-meta">
                        {{ number_format($invoice->amount, 2) }} —
                        @if($invoice->status == 'unpaid')
                            <span style="color:#dc3545;">{{ trans('invoices.unpaid') }}</span>
                        @elseif($invoice->status == 'partial')
                            <span style="color:#856404;">{{ trans('invoices.partial') }}</span>
                        @endif
                        @if($invoice->due_date)
                            &nbsp;|&nbsp; {{ trans('invoices.due_date') }}: {{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') }}
                        @endif
                    </span>
                </div>
                <span class="inv-remaining">{{ number_format($invoice->remaining_amount, 2) }}</span>
                @can('pay_invoice')
                    <button class="btn-pay" data-invoice-id="{{ $invoice->id }}" data-remaining="{{ $invoice->remaining_amount }}" onclick="event.stopPropagation(); quickPay(this)">
                        <i class="bi bi-currency-dollar"></i> {{ trans('invoices.pay') }}
                    </button>
                @endcan
        </div>
        <div class="inv-details" id="inv-details-{{ $invoice->id }}" style="display:none;"></div>
        </div>
    @endforeach
@endif

<script>
    $('#remainingInvoicesModal').data('client-id', {{ $client->id }});

    function toggleInvDetails(invoiceId) {
        var $details = $('#inv-details-' + invoiceId);
        if ($details.is(':visible')) {
            $details.slideUp(200);
            return;
        }
        if ($details.html().trim() !== '') {
            $details.slideDown(200);
            return;
        }
        $.get('/ar/admin/invoices/' + invoiceId + '/details-partial', function(html) {
            $details.html(html).slideDown(200);
        });
    }

    function quickPay(el) {
        var $btn = $(el);
        var invoiceId = $btn.data('invoice-id');
        var remaining = $btn.data('remaining');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.post('/ar/admin/invoice/' + invoiceId + '/pay', {
            _token: '{{ csrf_token() }}',
            paid_amount: remaining,
            invoice_amount: remaining
        }, function(res) {
            var $wrapper = $btn.closest('.inv-card-wrapper');
            $wrapper.remove();
            var $remaining = $('.total-remaining');
            if (res.new_remaining !== undefined) {
                $remaining.html(parseFloat(res.new_remaining).toFixed(2) + ' {{ get_app_config_data("currency") }}');
            }
            if ($('.inv-card-wrapper').length === 0) {
                $('#remainingInvoicesModal .modal-body').html(
                    '<div class="text-center py-5"><i class="bi bi-check-circle-fill fs-1 text-success"></i><h5 class="mt-2 text-muted">{{ trans('clients.no_unpaid_invoices') }}</h5></div>'
                );
            }
            Swal.fire({
                icon: 'success',
                title: '{{ trans("forms.success") }}',
                timer: 1500,
                showConfirmButton: false
            });
            if (window.LaravelDataTables && window.LaravelDataTables['clients-table']) {
                window.LaravelDataTables['clients-table'].ajax.reload(null, false);
            }
        }).fail(function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: xhr.responseJSON ? xhr.responseJSON.message : '{{ trans("forms.delete_error") }}'
            });
            $btn.prop('disabled', false).html('<i class="bi bi-currency-dollar"></i> {{ trans('invoices.pay') }}');
        });
    }

    function sendWhatsAppReminder(clientId) {
        var $btn = $('.whatsapp-reminder-btn');
        var originalHtml = $btn.html();

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '{{ route('admin.clients.whatsapp_reminder', ['id' => '__ID__']) }}'.replace('__ID__', clientId),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ trans("clients.whatsapp_reminder_sent") }}',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ trans("forms.error") }}',
                        text: res.error
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: '{{ trans("forms.error") }}',
                    text: '{{ trans("clients.whatsapp_send_failed") }}'
                });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    }
</script>