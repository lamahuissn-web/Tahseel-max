@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('invoices.monthly_due_invoices');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.invoices'), 'link' => route('admin.invoices.index')],
                ['label' => trans('invoices.monthly_due_invoices_table'), 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp

    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="app-container container-xxxl">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                $headers = [
                    'invoices.ID',
                    'invoices.invoice_number',
                    'invoices.client',
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

@stop
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
                "order": [],
                "deferRender": true,
                "stateSave": false,
                "pagingType": "simple_numbers",
                "pageLength": 10,
                "ajax": {
                    url: "{{ route('admin.due_monthly_invoices') }}",
                },
                "columns": [{
                        data: 'id',
                        className: 'text-center no-export'
                    },
                    {
                        data: 'invoice_number',
                        className: 'text-center'
                    },
                    {
                        data: 'client',
                        className: 'text-center'
                    },
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
                        className: 'text-center'
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
                        "targets": [2, 8],
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
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "الكل"]
                ],
            });

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
        function confirmDelete(clientId) {
            Swal.fire({
                title: '{{ trans('employees.confirm_delete') }}',
                text: '{{ trans('clients.delete_warning') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ trans('employees.yes_delete') }}',
                cancelButtonText: '{{ trans('employees.cancel') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + clientId).submit();
                }
            });
        }
    </script>

    <script>
        function showPayModal(url, remainingAmount, invoiceAmount, notes, currentPaidDate) {
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

            $(document).on('input', '#paid_amount', function() {
                let enteredAmount = parseFloat($(this).val()) || 0;
                let remainingAmount = parseFloat($('#remaining_amount_note').text());

                if (enteredAmount > remainingAmount) {
                    $(this).val(remainingAmount);
                    showAmountWarning();
                }
            });
        }

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
