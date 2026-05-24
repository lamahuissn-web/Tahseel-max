@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('reports.clients_remaining_report');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.reports'), 'link' => route('admin.reports.reports')],
                ['label' => $title, 'link' => ''],
            ];
            PageTitle($title, $breadcrumbs);
        @endphp
    </div>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-xxxl">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        <label for="monthFilter" class="form-label">{{ trans('reports.month') }}</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('date') !!}</span>
                            <input type="month" id="monthFilter" class="form-control report-filter">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="fromDate" class="form-label">{{ trans('reports.from_date') }}</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('date') !!}</span>
                            <input type="date" id="fromDate" class="form-control report-filter">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="toDate" class="form-label">{{ trans('reports.to_date') }}</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('date') !!}</span>
                            <input type="date" id="toDate" class="form-control report-filter">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">{{ trans('reports.client_status') }}</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('select1') !!}</span>
                            <select id="statusFilter" class="form-select report-filter">
                                <option value="">{{ trans('reports.select') }}</option>
                                <option value="1">{{ trans('clients.active') }}</option>
                                <option value="0">{{ trans('clients.inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="sortDirection" class="form-label">{{ trans('reports.sort_direction') }}</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('select1') !!}</span>
                            <select id="sortDirection" class="form-select report-filter">
                                <option value="desc">{{ trans('reports.descending') }}</option>
                                <option value="asc">{{ trans('reports.ascending') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-2">
                        <div class="bg-light border rounded p-3 text-center">
                            <span class="text-muted d-block">{{ trans('reports.total_clients') }}</span>
                            <strong class="fs-4" id="total_clients">0</strong>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-light border rounded p-3 text-center">
                            <span class="text-muted d-block">{{ trans('reports.total_invoices') }}</span>
                            <strong class="fs-4" id="total_invoices">0</strong>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-light border rounded p-3 text-center">
                            <span class="text-muted d-block">{{ trans('reports.total_amount') }}</span>
                            <strong class="fs-4 text-primary" id="total_amount">0.00</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light border rounded p-3 text-center">
                            <span class="text-muted d-block">{{ trans('reports.total_paid') }}</span>
                            <strong class="fs-4 text-success" id="total_paid">0.00</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light border rounded p-3 text-center">
                            <span class="text-muted d-block">{{ trans('reports.total_remaining') }}</span>
                            <strong class="fs-4 text-danger" id="total_remaining">0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        {{ trans('reports.clients_remaining_hint') }}
                    </div>
                    <div class="btn-group">
                        <a id="exportExcelBtn" class="btn btn-success btn-sm" target="_blank">
                            <i class="bi bi-file-earmark-spreadsheet"></i> {{ trans('reports.export_excel') }}
                        </a>
                        <button type="button" id="printPdfBtn" class="btn btn-primary btn-sm">
                            <i class="bi bi-printer"></i> {{ trans('reports.export_pdf') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" style="border-top: 3px solid #0d6efd;">
            @php
                $headers = [
                    'clients.ID',
                    'clients.name',
                    'clients.phone',
                    'clients.client_type',
                    'clients.subscription',
                    'reports.total_invoices',
                    'reports.total_amount',
                    'reports.total_paid',
                    'reports.total_remaining',
                    'reports.last_due_date',
                    'clients.status',
                    'clients.action',
                ];

                generateTable($headers);
            @endphp
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            const table = $('#table1').DataTable({
                language: {
                    url: "{{ asset('assets/Arabic.json') }}"
                },
                processing: true,
                serverSide: true,
                deferRender: true,
                order: [],
                ajax: {
                    url: "{{ route('admin.reports.clients_remaining_data') }}",
                    type: 'POST',
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.month = $('#monthFilter').val();
                        d.from_date = $('#fromDate').val();
                        d.to_date = $('#toDate').val();
                        d.status_filter = $('#statusFilter').val();
                        d.sort_direction = $('#sortDirection').val();
                    },
                    dataSrc: function(json) {
                        $('#total_clients').text(json.totals?.total_clients ?? 0);
                        $('#total_invoices').text(json.totals?.total_invoices ?? 0);
                        $('#total_amount').text(json.totals?.total_amount ?? '0.00');
                        $('#total_paid').text(json.totals?.total_paid ?? '0.00');
                        $('#total_remaining').text(json.totals?.total_remaining ?? '0.00');
                        return json.data;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center' },
                    { data: 'name', className: 'text-center' },
                    { data: 'phone', className: 'text-center' },
                    { data: 'client_type', className: 'text-center' },
                    { data: 'subscription', className: 'text-center' },
                    { data: 'invoices_count', className: 'text-center' },
                    { data: 'total_amount', className: 'text-center' },
                    { data: 'total_paid', className: 'text-center' },
                    { data: 'total_remaining', className: 'text-center' },
                    { data: 'latest_due_date', className: 'text-center' },
                    { data: 'status', className: 'text-center' },
                    { data: 'action', className: 'text-center no-export', orderable: false }
                ],
                columnDefs: [
                    {
                        targets: [6, 7, 8],
                        orderable: false
                    }
                ],
                dom: '<"row align-items-center"<"col-md-3"l><"col-md-6"f><"col-md-3"B>>rt<"row align-items-center"<"col-md-6"i><"col-md-6"p>>',
                buttons: [
                    { extend: 'copy' }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "{{ trans('reports.all_records') }}"]]
            });

            function getFilters() {
                return {
                    month: $('#monthFilter').val() || '',
                    from_date: $('#fromDate').val() || '',
                    to_date: $('#toDate').val() || '',
                    status_filter: $('#statusFilter').val() || '',
                    sort_direction: $('#sortDirection').val() || ''
                };
            }

            function updateExportLinks() {
                const params = new URLSearchParams(getFilters()).toString();
                $('#exportExcelBtn').attr('href',
                    "{{ route('admin.reports.clients_remaining_export_excel') }}?" + params);
            }

            $('.report-filter').on('change', function() {
                table.ajax.reload(null, false);
                updateExportLinks();
            });

            $('#printPdfBtn').on('click', function() {
                const params = new URLSearchParams(getFilters()).toString();
                const url = "{{ route('admin.reports.clients_remaining_print') }}?" + params;
                window.open(url, '_blank');
            });

            updateExportLinks();
        });
    </script>
@endsection

