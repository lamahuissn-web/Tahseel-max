@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('invoices.new_paid_invoices');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.invoices'), 'link' => route('admin.invoices.index')],
                ['label' => trans('invoices.new_paid_invoices_table'), 'link' => ''],
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
                "pageLength": 10,
                "ajax": {
                    url: "{{ route('admin.new_paid_invoices') }}",
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
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "الكل"]
                ],
            });

            setInterval(function() {
                table.ajax.reload(null, false);
            }, 5000);

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
        function showPayModal(url, remainingAmount) {
            $('#payInvoiceForm').attr('action', url);
            $('#amount').val(remainingAmount);
            $('#payInvoiceModal').modal('show');
        }

        function validateAmount() {
            let amount = $('#amount').val();
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
