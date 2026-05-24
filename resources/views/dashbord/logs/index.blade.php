@extends('dashbord.layouts.master')

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('logs.system_logs');
    $breadcrumbs = [
    ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
    ['label' => trans('Toolbar.logs'), 'link' => ''],
    ['label' => trans('logs.system_logs'), 'link' => ''],
    ];

    PageTitle($title, $breadcrumbs);
    @endphp

    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <button type="button" id="clearOldLogsBtn" class="btn btn-warning btn-sm d-flex align-items-center gap-2">
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 6L18 18M6 18L18 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <span>{{ trans('logs.clear_old_logs') }}</span>
        </button>
    </div>
</div>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card-body">
        <div class="col-md-12 row">
            <div class="col-md-3">
                <label for="action" class="form-label">{{ trans('logs.action_type') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text">{!! form_icon('select1') !!}</span>
                    <select class="form-select filter-select" name="action" id="action">
                        <option value="">{{ trans('reports.select') }}</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}">{{ trans('logs.' . $action) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <label for="user_id" class="form-label">{{ trans('logs.user') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text">{!! form_icon('select1') !!}</span>
                    <select class="form-select filter-select" name="user_id" id="user_id">
                        <option value="">{{ trans('reports.select') }}</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <label for="model_type" class="form-label">{{ trans('logs.model_type') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text">{!! form_icon('select1') !!}</span>
                    <select class="form-select filter-select" name="model_type" id="model_type">
                        <option value="">{{ trans('reports.select') }}</option>
                        @foreach($model_types as $model_type)
                        <option value="{{ $model_type }}">{{ class_basename($model_type) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <label for="date_from" class="form-label">{{ trans('reports.from_date') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text">{!! form_icon('date') !!}</span>
                    <input type="date" class="form-control filter-input" name="date_from" id="date_from"
                        value="{{ old('date_from') }}">
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <label for="date_to" class="form-label">{{ trans('reports.to_date') }}</label>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text">{!! form_icon('date') !!}</span>
                    <input type="date" class="form-control filter-input" name="date_to" id="date_to"
                        value="{{ old('date_to') }}">
                </div>
            </div>

            <!-- Reset Button -->
            <div class="col-md-12 mt-3" style="margin-bottom: 10px;">
                <button type="button" id="reset-btn" class="btn btn-secondary">
                    <i class="bi bi-redo"></i> {{ trans('reports.reset') }}
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
        @php
        $headers = [
        'logs.id',
        'logs.operation_type',
        'logs.description',
        'logs.user',
        'logs.ip_address',
        'logs.date',
        'logs.action',
        ];

        generateTable($headers);
        @endphp
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" tabindex="-1" id="logDetailsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{ trans('logs.log_details') }}</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1">&times;</i>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ trans('logs.basic_information') }}</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">{{ trans('logs.operation_type') }}:</th>
                                <td id="modalAction"></td>
                            </tr>
                            <tr>
                                <th>{{ trans('logs.description') }}:</th>
                                <td id="modalDescription"></td>
                            </tr>
                            <tr>
                                <th>{{ trans('logs.user') }}:</th>
                                <td id="modalUser"></td>
                            </tr>
                            <tr>
                                <th>{{ trans('logs.ip_address') }}:</th>
                                <td id="modalIp"></td>
                            </tr>
                            <tr>
                                <th>{{ trans('logs.browser') }}:</th>
                                <td id="modalUserAgent"></td>
                            </tr>
                            <tr>
                                <th>{{ trans('logs.date') }}:</th>
                                <td id="modalDate"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>{{ trans('logs.additional_information') }}</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">{{ trans('logs.model') }}:</th>
                                <td id="modalModelType"></td>
                            </tr>
                            <tr>
                                <th>{{ trans('logs.model_id') }}:</th>
                                <td id="modalModelId"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- <div class="row mt-3">
                    <div class="col-12">
                        <h6>{{ trans('logs.data_changes') }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light text-dark">
                                        <h6 class="mb-0">{{ trans('logs.old_data') }}</h6>
                                    </div>
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                        <div id="modalOldData">
                                            <div class="text-center text-muted py-3">
                                                {{ trans('logs.no_old_data') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light text-dark">
                                        <h6 class="mb-0">{{ trans('logs.new_data') }}</h6>
                                    </div>
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                        <div id="modalNewData">
                                            <div class="text-center text-muted py-3">
                                                {{ trans('logs.no_new_data') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('logs.close') }}</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        var table = $('#table1').DataTable({
            "language": {
                url: "{{ asset('assets/Arabic.json') }}"
            },
            "processing": true,
            "serverSide": true,
            "order": [],
            "pageLength": 10,
            "ajax": {
                url: "{{ route('admin.logs.index') }}",
                data: function(d) {
                    d.action = $('#action').val();
                    d.user_id = $('#user_id').val();
                    d.model_type = $('#model_type').val();
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                }
            },
            "columns": [
                {
                    data: 'id',
                    className: 'text-center no-export'
                },
                {
                    data: 'action_type',
                    className: 'text-center'
                },
                {
                    data: 'description',
                    className: 'text-center'
                },
                {
                    data: 'user',
                    className: 'text-center'
                },
                {
                    data: 'ip_address',
                    className: 'text-center'
                },
                {
                    data: 'created_at',
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
                            'vertical-align': 'middle',
                        });
                    }
                },
                {
                    "targets": [2, 3, 4],
                    "createdCell": function(td, cellData, rowData, row, col) {
                        $(td).css({
                            'font-weight': '600',
                            'text-align': 'center',
                            'vertical-align': 'middle',
                        });
                    }
                }
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

        $('.filter-select').change(function() {
            table.ajax.reload();
        });

        var filterTimeout;
        $('.filter-input').on('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                table.ajax.reload();
            }, 500);
        });

        $('#reset-btn').click(function() {
            $('#action').val('');
            $('#user_id').val('');
            $('#model_type').val('');
            $('#date_from').val('');
            $('#date_to').val('');
            table.ajax.reload();
        });

        $('#clearOldLogsBtn').click(function() {
            if (confirm('{{ trans('logs.confirm_clear') }}')) {
                var $btn = $(this);
                var originalText = $btn.html();

                $btn.prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    جاري المعالجة...
                `);

                $.ajax({
                    url: "{{ route('admin.logs.clear') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        days: 30
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            table.ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        var message = '{{ trans('logs.clear_error') }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr.error(message);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });

        $(document).on('click', '.view-log-details', function() {
            var logId = $(this).data('log-id');

            $.ajax({
                url: "{{ route('admin.logs.show', ['id' => '__id__']) }}".replace('__id__', logId),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;

                        $('#modalAction').text(getActionLabel(data.action));
                        $('#modalDescription').text(data.description);
                        $('#modalUser').text(data.user);
                        $('#modalIp').text(data.ip_address);
                        $('#modalUserAgent').text(data.user_agent || '{{ trans('logs.not_available') }}');
                        $('#modalDate').text(data.created_at);
                        $('#modalModelType').text(data.model_type || '{{ trans('logs.not_available') }}');
                        $('#modalModelId').text(data.model_id || '{{ trans('logs.not_available') }}');

                        // if (data.old_data) {
                        //     $('#modalOldData').html(data.old_data);
                        // } else {
                        //     $('#modalOldData').html('<div class="text-center text-muted py-3">{{ trans('logs.no_old_data') }}</div>');
                        // }

                        // if (data.new_data) {
                        //     $('#modalNewData').html(data.new_data);
                        // } else {
                        //     $('#modalNewData').html('<div class="text-center text-muted py-3">{{ trans('logs.no_new_data') }}</div>');
                        // }

                        $('#logDetailsModal').modal('show');
                    } else {
                        toastr.error('{{ trans('logs.load_error') }}');
                    }
                },
                error: function(xhr) {
                    toastr.error('{{ trans('logs.load_error') }}');
                }
            });
        });

        function getActionLabel(action) {
            var labels = {
                'invoice_paid': '{{ trans('logs.invoice_paid') }}',
                'invoice_redo': '{{ trans('logs.invoice_redo') }}',
                'invoice_created': '{{ trans('logs.invoice_created') }}',
                'invoice_deleted': '{{ trans('logs.invoice_deleted') }}',
                'client_created': '{{ trans('logs.client_created') }}',
                'client_updated': '{{ trans('logs.client_updated') }}',
                'client_deleted': '{{ trans('logs.client_deleted') }}',
                'clients_imported': '{{ trans('logs.clients_imported') }}',
                'user_login': '{{ trans('logs.user_login') }}',
                'financial_transaction_created': '{{ trans('logs.financial_transaction_created') }}',
                'financial_transaction_deleted': '{{ trans('logs.financial_transaction_deleted') }}'
            };
            return labels[action] || action;
        }
    });
</script>

<style>
.json-data {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 10px;
    font-size: 12px;
    max-height: 200px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.badge {
    font-size: 11px;
}

.card-header h6 {
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
</style>
@endsection
