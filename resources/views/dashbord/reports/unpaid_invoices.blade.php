@extends('dashbord.layouts.master')

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('reports.unpaid_invoices_report');
    $breadcrumbs = [
    ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
    ['label' => trans('Toolbar.reports'), 'link' => ''],
    ['label' => trans('reports.unpaid_invoices_report'), 'link' => ''],
    ];

    PageTitle($title, $breadcrumbs);
    @endphp
</div>

@endsection
@section('content')

<div id="kt_app_content_container" class="app-container container-xxxl">

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="col-md-12 row">

                <div class="col-md-3">
                    <label for="client_id" class="form-label">{{ trans('reports.client_id') }}</label>
                    <div class="input-group flex-nowrap ">
                        <span class="input-group-text" id="basic-addon3">{!! form_icon('select1') !!}</i></span>
                        <div class="overflow-hidden flex-grow-1">
                            <select class="form-select rounded-start-0" name="client_id" id="client_id"
                                data-placeholder="{{ trans('reports.select') }}">
                                <option value="">{{ trans('reports.select') }}</option>
                                @foreach ($clients as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('client_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @error('client_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3">
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

                <div class="col-md-3">
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

                <div class="col-md-3 mb-3" style="margin-top: 10px">
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

                <div class="col-md-3 mb-3" style="margin-top: 10px;">
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
                <div class="col-md-12">
                    <div class="d-flex justify-content-end gap-3">
                        <div class="bg-primary bg-opacity-10 rounded p-3 d-inline-block">
                            <span class="text-primary fw-bold">{{ trans('reports.total_amount') }}:</span>
                            <span class="text-primary fw-bold" id="total_amount"></span>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded p-3 d-inline-block">
                            <span class="text-danger fw-bold">{{ trans('reports.total_unpaid') }}:</span>
                            <span class="text-danger fw-bold" id="total_unpaid"></span>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded p-3 d-inline-block">
                            <span class="text-success fw-bold">{{ trans('reports.total_paid') }}:</span>
                            <span class="text-success fw-bold" id="total_paid"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        'invoices.status',
        'invoices.subscription',
        'invoices.month_year',
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
            "deferRender": true,
            "order": [],
            "ajax": {
                url: "{{ route('admin.reports.unpaid_invoices.data') }}",
                type: "POST",
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.client_id = $('#client_id').val();
                    d.type = $('#type').val();
                    d.month = $('#month').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                },
                dataSrc: function(json) {
                    $('#total_amount').text(json.totals.total_amount || '0');
                    $('#total_unpaid').text(json.totals.total_unpaid || '0');
                    $('#total_paid').text(json.totals.total_paid || '0');

                    return json.data;
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
                {
                    data: 'due_date',
                    className: 'text-center'
                },
                {
                    data: 'paid_date',
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
                    data: 'month_year',
                    className: 'text-center'
                },
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

        $('#client_id, #type, #month, #from_date, #to_date').on('change', function() {
            table.ajax.reload();
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

    function showPayModal(url, remainingAmount, totalAmount, notes, paidDate) {
        // You can implement the payment modal here or use existing modal
        // This function should be defined in your main layout or invoices views
        if (typeof window.showPayModal === 'function') {
            window.showPayModal(url, remainingAmount, totalAmount, notes, paidDate);
        } else {
            // Fallback: redirect to payment page
            window.location.href = url;
        }
    }
</script>
@endsection