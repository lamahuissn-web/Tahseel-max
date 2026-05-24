<div class="" style="margin-top: 30px">
    @if(isset($unpaid_data) && !empty($unpaid_data))
        <table id="table" class="example table table-bordered responsive nowrap text-center" cellspacing="0"
               width="70%">
            <thead>
            <tr class="greentd" style="background-color: lightgrey" >
                <th>{{trans('invoices.hash') }}</th>
                <th>{{ trans('invoices.invoice_number') }}</th>
                <th>{{ trans('invoices.subscription') }}</th>
                <th>{{ trans('invoices.amount') }}</th>
                <th>{{ trans('invoices.paid_amount') }}</th>
                <th>{{ trans('invoices.paid_date') }}</th>
                <th>{{ trans('invoices.enshaa_date') }}</th>
                <th>{{ trans('invoices.due_date') }}</th>
                <th>{{ trans('invoices.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @php
                $x = 1;
            @endphp
            @foreach ($unpaid_data as $invoice)
                <tr>
                    <td>{{ $x++ }}</td>
                    <td>{{ $invoice->client && $invoice->client->client_type == 'satellite' ? 'SA-' : 'IN-' }}{{ $invoice->invoice_number }}
                    </td>
                    <td>{{ $invoice->subscription ? $invoice->subscription->name : 'خدمة' }}</td>
                    <td>{{ $invoice->amount ?? 'N/A' }}</td>
                    <td>{{ $invoice->paid_amount ?? 'N/A'}}</td>
                    <td class="fnt_center_black">{{ $invoice->paid_date ? \Illuminate\Support\Carbon::parse($invoice->paid_date)->format('Y-m-d h:i A') : 'N\A'}}</td>
                    <td class="fnt_center_black">{{ $invoice->enshaa_date ? \Illuminate\Support\Carbon::parse($invoice->enshaa_date)->format('Y-m-d') : 'N\A'}}</td>
                    <td class="fnt_center_black">{{ $invoice->due_date ? \Illuminate\Support\Carbon::parse($invoice->due_date)->format('Y-m-d') : 'N\A'}}</td>
                    <td>
                        <div class="btn-group">
                            {{-- <a href="{{ route('admin.employee_delete_invoiceat', $invoice->id) }}" onclick="return confirm('Are You Sure To Delete?')" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </a> --}}
                            <a href="javascript:void(0)"
                                    onclick="invoice_details('{{ route('admin.invoice_details', $invoice->id) }}')"
                                    class="btn btn-info" title="{{ trans('invoices.view_details') }}"
                                    style="padding: 2px 4px; font-size: 20px; line-height: 1; margin-right: 2px;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="javascript:void(0)"
                                    onclick="print_invoice('{{ route('admin.print_invoice', $invoice->id) }}')"
                                    class="btn btn-warning" title="{{ trans('invoices.print') }}"
                                    style="padding: 2px 4px; font-size: 20px; line-height: 1; margin-right: 2px;">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @if ($invoice->status == 'unpaid' || $invoice->status == 'partial')
                                    <a href="javascript:void(0)"
                                        onclick="showPayModal('{{ route('admin.pay_invoice', $invoice->id) }}', {{ $invoice->remaining_amount }}, {{ $invoice->amount }}, {{ $invoice->notes }}, {{ $invoice->due_date }})"
                                        class="btn btn-success" title="{{ trans('invoices.mark_as_paid') }}"
                                        style="padding: 2px 4px; font-size: 20px; line-height: 1; margin-right: 2px;">
                                        <i class="bi bi-check-circle"></i>
                                    </a>
                                @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="modal fade" id="payInvoiceModal" tabindex="-1" aria-labelledby="payInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payInvoiceModalLabel">{{ trans('invoices.enter_payment_amount') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="payInvoiceForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="invoice_amount" class="form-label">{{ trans('invoices.invoice_amount') }}</label>
                        <input type="number" class="form-control" id="invoice_amount" name="invoice_amount" required
                            min="1">
                    </div>
                    <div class="mb-3">
                        <label for="paid_amount" class="form-label">{{ trans('invoices.invoice_paid_amount') }}</label>
                        <input type="number" class="form-control" id="paid_amount" name="paid_amount">
                    </div>
                    <div class="mb-3">
                        <label for="paid_date" class="form-label">{{ trans('invoices.paid_date') }}</label>
                        <input type="date" class="form-control" id="paid_date" name="paid_date">
                        <small class="text-muted">{{ trans('invoices.optional_paid_date_update') }}</small>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ trans('invoices.notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ trans('invoices.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('invoices.pay') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('js')
    <script>
        function showPayModal(url, remainingAmount, invoiceAmount) {
            $('#payInvoiceForm').attr('action', url);
            $('#invoice_amount').val(invoiceAmount);
            // $('#paid_amount').val(remainingAmount);
            $('#payInvoiceModal').modal('show');
        }

        function validateAmount() {
            let amount = $('#paid_amount').val();
            if (amount <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid amount',
                    text: 'Please enter a valid amount greater than 0.',
                });
                return false;
            }
            return true;
        }
    </script>
    <script>
        function invoice_details(url) {
            $.get(url, function(data) {
                $('#result_info').html(data);
                $('#modaldetails').modal('show');
            });
        }

        function print_invoice(url) {
            var printWindow = window.open(url, '_blank');
            printWindow.focus();
        }
    </script>
@endsection





