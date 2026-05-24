@extends('dashbord.layouts.master')
<style>
    .btn:not(.btn-outline):not(.btn-dashed):not(.border-hover):not(.border-active):not(.btn-flush):not(.btn-icon).btn-sm,
    .btn-group-sm>.btn:not(.btn-outline):not(.btn-dashed):not(.border-hover):not(.border-active):not(.btn-flush):not(.btn-icon) {
        padding: 10px 12px !important;
    }
</style>
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('invoices.invoices');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.invoices'), 'link' => ''],
                ['label' => trans('invoices.invoices_table'), 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp


        {{-- <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ AddButton(route('admin.invoices.create'))}}

        </div> --}}
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
                    'invoices.remaining_amount',
                    'invoices.enshaa_date',
                    'invoices.due_date',
                    'invoices.status',
                    'invoices.subscription',
                    // 'invoices.employee',
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
                <form id="payInvoiceForm" method="POST" action="" onsubmit="return validateAmount()">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="amount" class="form-label">{{ trans('invoices.amount') }}</label>
                            <input type="number" class="form-control" id="amount" name="amount" required min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('invoices.cancel') }}</button>
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
                "ajax": {
                    url: "{{ route('admin.outstanding_invoice') }}",
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
                        data: 'remaining_amount',
                        className: 'text-center'
                    },
                    {
                        data: 'enshaa_date',
                        className: 'text-center'
                    },
                    {
                        data: 'due_date',
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
                    // {
                    //     data: 'employee',
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
                        "text": '<i class="bi bi-file-earmark-excel"></i>إكسل',
                        "className": 'btn btn-dark'
                    },
                    {
                        "extend": 'copy',
                        "text": '<i class="bi bi-clipboard"></i>نسخ',
                        "className": 'btn btn-primary'
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
        function showPayModal(url) {
            $('#payInvoiceForm').attr('action', url);
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
