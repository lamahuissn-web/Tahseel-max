@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('reports.overdue_invoices_report') ?? 'تقرير الفواتير المتأخرة';
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.reports'), 'link' => ''],
                ['label' => $title, 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp
    </div>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card shadow-sm" style="margin-bottom: 20px;">
        <div class="card-body">
            <div class="col-md-12 row">
            <div class="col-md-3">
                    <label for="client_id" class="form-label">{{ trans('reports.client_id') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('select1') !!}</span>
                        <select class="form-select filter-select selectpicker" name="client_id" id="client_id" data-live-search="true">
                            <option value="">{{ trans('reports.select') }}</option>
                            @foreach ($clients as $item)
                            <option value="{{ $item->id }}" {{ old('client_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
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
                            <span class="text-primary fw-bold">{{ trans('reports.total_invoices') }}:</span>
                            <span class="text-primary fw-bold" id="total_invoices">0</span>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded p-3 d-inline-block">
                            <span class="text-danger fw-bold">{{ trans('reports.total_remaining') }}:</span>
                            <span class="text-danger fw-bold" id="total_remaining">0.00</span>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded p-3 d-inline-block">
                            <span class="text-info fw-bold">{{ trans('reports.total_amount') }}:</span>
                            <span class="text-info fw-bold" id="total_amount">0.00</span>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded p-3 d-inline-block">
                            <span class="text-success fw-bold">{{ trans('reports.total_paid') }}:</span>
                            <span class="text-success fw-bold" id="total_paid">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="card shadow-sm" style="border-top: 3px solid #dc3545;">
            @php
                $headers = [
                    'invoices.ID',
                    'invoices.invoice_number',
                    'invoices.client',
                    'invoices.amount',
                    'invoices.paid_amount',
                    'invoices.remaining_amount',
                    'invoices.due_date',
                    'reports.days_overdue',
                    'invoices.subscription',
                    'invoices.status',
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
        });
    </script>
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
                    url: "{{ route('admin.reports.overdue_invoices_data') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.client_id = $('#client_id').val();
                        d.type = $('#type').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    },
                    dataSrc: function(json) {
                        $('#total_invoices').text(json.totals.total_invoices || '0');
                        $('#total_remaining').text(parseFloat(json.totals.total_remaining || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#total_amount').text(parseFloat(json.totals.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#total_paid').text(parseFloat(json.totals.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

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
                        data: 'days_overdue',
                        className: 'text-center'
                    },
                    {
                        data: 'subscription',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        className: 'text-center'
                    },
                ],
                "columnDefs": [{
                        "targets": [1],
                        "orderable": false,
                    },
                    {
                        "targets": [3, 4, 5],
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

            $('#client_id, #type, #from_date, #to_date').on('change', function() {
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
    </script>
@endsection

