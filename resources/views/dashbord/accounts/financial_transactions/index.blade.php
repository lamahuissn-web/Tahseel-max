@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('accounts.financial_transactions');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.financial_transactions'), 'link' => ''],
                ['label' => trans('accounts.financial_transactions_table'), 'link' => ''],
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
                    'accounts.ID',
                    'accounts.amount',
                    'accounts.account',
                    'accounts.assigned_user',
                    'accounts.date',
                    'accounts.time',
                    'accounts.type',
                    'accounts.notes',
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
                    url: "{{ route('admin.financial_transactions.index') }}",
                },
                "columns": [{
                        data: 'id',
                        className: 'text-center no-export'
                    },
                    {
                        data: 'amount',
                        className: 'text-center'
                    },
                    {
                        data: 'account',
                        className: 'text-center'
                    },
                    {
                        data: 'assigned_user',
                        className: 'text-center'
                    },
                    {
                        data: 'date',
                        className: 'text-center'
                    },
                    {
                        data: 'time',
                        className: 'text-center'
                    },
                    {
                        data: 'type',
                        className: 'text-center'
                    },
                    {
                        data: 'notes',
                        className: 'text-center'
                    },
                ],
                "columnDefs": [{
                    "targets": [1, -1],
                    "orderable": false,
                }],
                "rowCallback": function(row, data, index) {
                    if (data.type === "قبض") {
                        $(row).css("background-color", "#d4edda");
                    } else if (data.type === "صرف") {
                        $(row).css("background-color", "#f8d7da");
                    } else {
                        $(row).css("background-color", "#fff3cd");
                    }
                },
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
        });
    </script>

    <script>
        function invoice_details(url) {
            $.get(url, function(data) {
                $('#result_info').html(data);
                $('#modaldetails').modal('show');
            });
        }
    </script>
@endsection
