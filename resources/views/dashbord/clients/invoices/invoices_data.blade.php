<div class="" style="margin-top: 30px">
    <style>
        @media screen and (max-width: 767px) {
            #table1_wrapper .dataTable thead { display: none !important; }
            #table1_wrapper .dataTable tbody,
            #table1_wrapper .dataTable tbody tr,
            #table1_wrapper .dataTable tbody td { display: block; width: 100%; box-sizing: border-box; }
            #table1_wrapper .dataTable tbody tr {
                background: #fff; border: 1px solid #e0e0e0; border-radius: 12px;
                padding: 12px 14px; margin-bottom: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            }
            #table1_wrapper .dataTable tbody td {
                border: none !important; padding: 4px 0 !important;
                text-align: right !important; font-size: 13px;
            }
            #table1_wrapper .dataTable tbody td::before {
                content: attr(data-label);
                display: inline-block; font-weight: 600; color: #6b7280;
                font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;
                margin-left: 6px; min-width: 80px;
            }
            #table1_wrapper .dataTable tbody td[data-label=""]::before { display: none; }
            #table1_wrapper .dataTable tbody td:last-child {
                text-align: center !important; margin-top: 8px;
                padding-top: 8px !important; border-top: 1px solid #f0f0f0 !important;
            }
            #table1_wrapper .dataTable tbody td:last-child::before { display: none; }
            #table1_wrapper .dataTables_info,
            #table1_wrapper .dataTables_length,
            #table1_wrapper .dataTables_filter { text-align: center !important; float: none !important; margin-bottom: 8px; }
            #table1_wrapper .dataTables_paginate { text-align: center !important; float: none !important; margin-top: 8px; }
            #table1_wrapper .dataTables_filter input { width: 100% !important; max-width: 100% !important; }
            .filter-row .col-md-4 { width: 50% !important; flex: 0 0 50% !important; max-width: 50% !important; }
            #table1_wrapper .dt-buttons { text-align: center !important; }
        }
        @media screen and (max-width: 480px) {
            .filter-row .col-md-4 { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }
        }
    </style>
    <div class="card-body">
        <div class="col-md-12 row filter-row">

            <div class="col-md-4 col-sm-6">
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


            <div class="col-md-4 col-sm-6">
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
            <div class="col-md-4 col-sm-6">
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

            <div class="col-md-4 col-sm-6 mb-3" style="margin-top: 10px;">
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

            <div class="col-md-4 col-sm-6 mb-3" style="margin-top: 10px;">
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
                "createdRow": function(row, data, dataIndex) {
                    var labels = {
                        0: '#',
                        1: '{{ trans("invoices.invoice_number") }}',
                        2: '{{ trans("invoices.amount") }}',
                        3: '{{ trans("invoices.paid_amount") }}',
                        4: '{{ trans("invoices.remaining_amount") }}',
                        5: '{{ trans("invoices.due_date") }}',
                        6: '{{ trans("invoices.paid_date") }}',
                        7: '{{ trans("invoices.collected_by") }}',
                        8: '{{ trans("invoices.status") }}',
                        9: '{{ trans("invoices.subscription") }}',
                        10: '{{ trans("invoices.notes") }}',
                        11: ''
                    };
                    $('td', row).each(function(i) {
                        $(this).attr('data-label', labels[i] || '');
                    });
                },
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
                        className: 'text-center'
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
