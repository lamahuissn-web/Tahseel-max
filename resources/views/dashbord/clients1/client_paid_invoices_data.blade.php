<div class="card-header d-flex justify-content-between align-items-center">
    <div class="d-flex gap-4">
        <span class="badge bg-success fs-6 px-3 py-2">
            {{ trans('clients.total_paid') }}: {{ $total_paid }}
        </span>
        <span class="badge bg-danger fs-6 px-3 py-2">
            {{ trans('clients.total_unpaid') }}: {{ $total_unpaid }}
        </span>
    </div>
</div>
<div class="" style="margin-top: 30px">
    {{-- <table id="table" class="example table table-bordered responsive nowrap text-center" cellspacing="0"
            width="70%">
            <thead>
                <tr class="greentd" style="background-color: lightgrey">
                    <th>{{ trans('invoices.hash') }}</th>
                    <th>{{ trans('invoices.invoice_number') }}</th>
                    <th>{{ trans('invoices.subscription') }}</th>
                    <th>{{ trans('invoices.amount') }}</th>
                    <th>{{ trans('invoices.paid_amount') }}</th>
                    <th>{{ trans('invoices.paid_date') }}</th>
                    <th>{{ trans('invoices.enshaa_date') }}</th>
                    <th>{{ trans('invoices.due_date') }}</th>
                    <th>{{ trans('invoices.status') }}</th>
                    <th>{{ trans('invoices.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $x = 1;
                @endphp
                @foreach ($paid_data as $invoice)
                    <tr>
                        <td>{{ $x++ }}</td>
                        <td>{{ $invoice->client && $invoice->client->client_type == 'satellite' ? 'SA-' : 'IN-' }}{{ $invoice->invoice_number }}
                        </td>
                        <td>{{ $invoice->subscription ? $invoice->subscription->name : 'خدمة' }}</td>
                        <td>{{ $invoice->amount ?? 'N/A' }}</td>
                        <td>{{ $invoice->paid_amount ?? 'N/A' }}</td>
                        <td class="fnt_center_black">
                            {{ $invoice->paid_date ? \Illuminate\Support\Carbon::parse($invoice->paid_date)->format('Y-m-d h:i A') : 'N\A' }}
                        </td>
                        <td class="fnt_center_black">
                            {{ $invoice->enshaa_date ? \Illuminate\Support\Carbon::parse($invoice->enshaa_date)->format('Y-m-d') : 'N\A' }}
                        </td>
                        <td class="fnt_center_black">
                            {{ $invoice->due_date ? \Illuminate\Support\Carbon::parse($invoice->due_date)->format('Y-m-d') : 'N\A' }}
                        </td>
                        <td>
                            <span
                                class="badge
                            @if ($invoice->status == 'paid') bg-success text-white
                            @elseif($invoice->status == 'partial') bg-warning text-dark @endif
                            px-4 py-3 rounded-pill fw-bold fs-5">
                                {{ trans('invoices.' . ($invoice->status ?? 'N/A')) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
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
                                        onclick="showPayModal('{{ route('admin.pay_invoice', $invoice->id) }}', {{ $invoice->remaining_amount }}, {{ $invoice->amount }})"
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
        </table> --}}
    <div class="card-body">
        <div class="col-md-12 row">

            <div class="col-md-4">
                <label for="type" class="form-label">{{ trans('reports.type') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon4">{!! form_icon('select1') !!}</span>
                    <select class="form-select" name="type" id="type">
                        <option value="">
                            {{ trans('reports.select') }}
                        </option>
                        <option value="subscription" {{ old('type') == 'subscription' ? 'selected' : '' }}>
                            {{ trans('reports.subscription') }}
                        </option>
                        <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>
                            {{ trans('reports.service') }}
                        </option>
                    </select>
                </div>
                @error('type')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>


            <div class="col-md-4">
                <label for="status" class="form-label">{{ trans('reports.status') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon4">{!! form_icon('select1') !!}</span>
                    <select class="form-select" name="status" id="status">
                        <option value="">
                            {{ trans('reports.select') }}
                        </option>
                        <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>
                            {{ trans('reports.paid') }}
                        </option>
                        <option value="partial" {{ old('status') == 'partial' ? 'selected' : '' }}>
                            {{ trans('reports.partial') }}
                        </option>
                        <option value="unpaid" {{ old('status') == 'unpaid' ? 'selected' : '' }}>
                            {{ trans('reports.unpaid') }}
                        </option>
                    </select>
                </div>
                @error('status')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="month" class="form-label">{{ trans('reports.month') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text">{!! form_icon('date') !!}</span>
                    <input type="month" class="form-control" name="month" id="month"
                        value="{{ old('month') }}">
                </div>
                @error('month')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-4 mb-3" style="margin-top: 10px;">
                <label for="from_date" class="form-label">{{ trans('reports.from_date') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text">{!! form_icon('date') !!}</span>
                    <input type="date" class="form-control" name="from_date" id="from_date"
                        value="{{ old('from_date') }}">
                </div>
                @error('from_date')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-4 mb-3" style="margin-top: 10px;">
                <label for="to_date" class="form-label">{{ trans('reports.to_date') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text">{!! form_icon('date') !!}</span>
                    <input type="date" class="form-control" name="to_date" id="to_date"
                        value="{{ old('to_date') }}">
                </div>
                @error('to_date')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
    <div style="overflow-x: auto;">
        @php
            $headers = [
                'invoices.ID',
                'invoices.invoice_number',
                // 'invoices.client',
                'invoices.amount',
                'invoices.paid_amount',
                'invoices.remaining_amount',
                'invoices.due_date',
                'invoices.paid_date',
                'invoices.collected_by',
                'invoices.status',
                'invoices.subscription',
                'invoices.notes',
                // 'invoices.employee',
                // 'invoices.month_year',
                'invoices.action',
            ];

            generateTable($headers);
        @endphp
    </div>
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
                        <small class="text-muted">{{ trans('invoices.remaining_amount') }}: <span id="remaining_amount_note"></span></small>
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

<div class="modal fade" tabindex="-1" id="modaldetails">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= trans('invoices.invoice_details') ?></h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1">&times;</i>
                </div>

            </div>

            <div id="result_info">

            </div>

        </div>
    </div>
</div>


@section('js')
    <script>
        $(document).ready(function() {
            //datatables
            table = $('#table1').DataTable({
                "language": {
                    url: "{{ asset('assets/Arabic.json') }}"
                },
                "processing": true,
                "serverSide": true,
                "deferRender": true,
                "order": [],
                "ajax": {
                    url: "{{ route('admin.reports.index') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        // d.client_id = $('#client_id').val();
                        d.client_id = "{{ $all_data->id }}";
                        d.type = $('#type').val();
                        d.status = $('#status').val();
                        d.month = $('#month').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        console.log("Response Text:", xhr.responseText);
                    }
                },
                "columns": [{
                        data: 'id',
                        className: 'text-center no-export'
                    },
                    {
                        data: 'invoice_number',
                        className: 'text-center'
                    },
                    // {
                    //     data: 'client',
                    //     className: 'text-center'
                    // },
                    {
                        data: 'amount',
                        className: 'text-center'
                    },
                    {
                        data: 'paid_amount',
                        className: 'text-center'
                    },
                    {
                        data: 'remaining_amount',
                        className: 'text-center'
                    },
                    // {
                    //     data: 'enshaa_date',
                    //     className: 'text-center'
                    // },
                    {
                        data: 'due_date',
                        className: 'text-center'
                    },
                    {
                        data: 'paid_date',
                        className: 'text-center'
                    },
                    {
                        data: 'collected_by',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return data ? data : 'N/A';
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'subscription',
                        className: 'text-center'
                    },
                    {
                        data: 'notes',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return data ? data : 'N/A';
                        }
                    },
                    // {
                    //     data: 'employee',
                    //     className: 'text-center'
                    // },
                    // {
                    //     data: 'month_year',
                    //     className: 'text-center'
                    // },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        className: 'text-center no-export'
                    },
                ],
                "columnDefs": [{
                        "targets": [1, -1],
                        "orderable": false,
                    },
                    {
                        "targets": [1],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'color': '#6610f2',

                                'vertical-align': 'middle',
                            });
                        }
                    },
                    {
                        "targets": [3, 4],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'vertical-align': 'middle',
                            });
                        }
                    },
                    {
                        "targets": [2],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'color': 'green',
                                'vertical-align': 'middle',
                            });
                        }
                    },

                    {
                        "targets": [5],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'color': 'red',
                                'vertical-align': 'middle',
                            });
                        }
                    },



                ],
                "order": [],
                "dom": '<"row align-items-center"<"col-md-3"l><"col-md-6"f><"col-md-3"B>>rt<"row align-items-center"<"col-md-6"i><"col-md-6"p>>',
                "buttons": [{
                        "extend": 'excel',
                    },
                    {
                        "extend": 'copy',
                    },
                    {
                        "extend": 'pdf'
                    }
                ],

                "language": {
                    "lengthMenu": "عرض _MENU_ سجلات",
                    "zeroRecords": "لا توجد سجلات",
                    "info": "عرض الصفحة _PAGE_ من _PAGES_",
                    "infoEmpty": "لا توجد سجلات",
                    "infoFiltered": "(مرشح من _MAX_ إجمالي السجلات)",
                    "search": "بحث:",
                    "paginate": {
                        "first": "الأول",
                        "last": "الأخير",
                        "next": "التالي",
                        "previous": "السابق"
                    }
                },
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "الكل"]
                ],
                "pageLength": 10,
            });

            $('#client_id, #type, #status, #month, #from_date, #to_date').on('change', function() {
                table.ajax.reload();
            });

            // $('#client_id, #type, #status, #month, #from_date, #to_date').on('change', function() {
            //     table.draw();
            // });

            $("input").change(function() {
                $(this).parent().parent().removeClass('has-error');
                $(this).next().empty();
            });
            $("textarea").change(function() {
                $(this).parent().parent().removeClass('has-error');
                $(this).next().empty();
            });
            $("select").change(function() {
                $(this).parent().parent().removeClass('has-error');
                $(this).next().empty();
            });
        });
    </script>
    <script>
        function showPayModal(url, remainingAmount, invoiceAmount, notes, currentPaidDate) {
            // console.log(notes)
            $('#payInvoiceForm').attr('action', url);
            $('#invoice_amount').val(invoiceAmount);
            // $('#paid_amount').val(remainingAmount);
            $('#remaining_amount_note').text(remainingAmount);
            if (currentPaidDate && currentPaidDate !== 'N/A') {
                $('#paid_date').val(currentPaidDate);
            } else {
                $('#paid_date').val('');
            }
            let notesValue = notes;
            if (notesValue === 'undefined' || notesValue === null || notesValue === 'null') {
                notesValue = '';
            }

            $('#notes').val(notesValue);
            $('#paid_amount').val('').attr({
                'max': remainingAmount,
                'placeholder': 'Max: ' + remainingAmount
            });
            $('#payInvoiceModal').modal('show');
        }

        $(document).on('input', '#paid_amount', function() {
            let enteredAmount = parseFloat($(this).val()) || 0;
            let remainingAmount = parseFloat($('#remaining_amount_note').text());

            if (enteredAmount > remainingAmount) {
                $(this).val(remainingAmount);
                showAmountWarning();
            }
        });

        function showAmountWarning() {
            let warning = $('#amount_warning');
            if (warning.length === 0) {
                $('<div id="amount_warning" class="text-danger mt-1 small">' +
                '<i class="bi bi-exclamation-triangle"></i> ' +
                'تم تعديل المبلغ إلى الحد الأقصى للمبلغ المتبقي' +
                '</div>').insertAfter('#paid_amount');
            } else {
                warning.show();
            }

            setTimeout(() => {
                $('#amount_warning').fadeOut();
            }, 3000);
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
