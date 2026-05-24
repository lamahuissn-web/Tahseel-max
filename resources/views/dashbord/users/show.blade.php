@extends('dashbord.layouts.master')

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('users.user_details');
    $breadcrumbs = [
    ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
    ['label' => trans('Toolbar.users'), 'link' => route('admin.users.index')],
    ['label' => trans('users.user_details'), 'link' => ''],
    ['label' => $user->name, 'link' => ''],
    ];

    PageTitle($title, $breadcrumbs);
    @endphp

    <div class="d-flex align-items-center gap-2 gap-lg-3">
        {{ BackButton(route('admin.users.index')) }}

        @can('update_user')
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil-square"></i> {{ trans('users.edit') }}
        </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-xxxl">
    <!-- User Information Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-person-fill text-primary"></i> {{ trans('users.user_information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <strong class="text-muted me-2">{{ trans('users.id') }}:</strong>
                                <span class="badge bg-primary">{{ $user->id }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <strong class="text-muted me-2">{{ trans('users.name') }}:</strong>
                                <span class="fw-bold text-primary">{{ $user->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <strong class="text-muted me-2">{{ trans('users.email') }}:</strong>
                                <span>{{ $user->email ?? trans('users.not_available') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <strong class="text-muted me-2">{{ trans('users.position') }}:</strong>
                                <span>{{ $user->position ?? trans('users.not_available') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <strong class="text-muted me-2">{{ trans('users.role') }}:</strong>
                                <span class="badge bg-info">
                                    {{ $user->roles->isNotEmpty() ? $user->roles->first()->getTranslation('title', app()->getLocale()) : trans('users.not_available') }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <strong class="text-muted me-2">{{ trans('users.status') }}:</strong>
                                @if($user->status == '1')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle-fill"></i> {{ trans('users.active') }}
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle-fill"></i> {{ trans('users.not_active') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-info-circle text-info"></i> {{ trans('users.additional_info') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">{{ trans('users.created_at') }}:</small>
                        <p class="mb-1">{{ $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : trans('users.not_available') }}</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">{{ trans('users.updated_at') }}:</small>
                        <p class="mb-1">{{ $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : trans('users.not_available') }}</p>
                    </div>
                    @if($user->created_by)
                    <div class="mb-3">
                        <small class="text-muted">{{ trans('users.created_by') }}:</small>
                        <p class="mb-1">{{ $user->user ? $user->user->name : trans('users.not_available') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- User Logs Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-journal-text text-success"></i> {{ trans('users.user_logs') }}
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Logs Table -->
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
            </div>
        </div>
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
                        <div class="table-responsive">
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
                    </div>
                    <div class="col-md-6">
                        <h6>{{ trans('logs.additional_information') }}</h6>
                        <div class="table-responsive">
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
                </div>
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
                url: "{{ route('admin.users.show', $user->id) }}",
                data: function(d) {
                    // No filters needed
                }
            },
            "columns": [{
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
                "lengthMenu": "{{ trans('reports.length_menu') }}",
                "zeroRecords": "{{ trans('reports.zero_records') }}",
                "info": "{{ trans('reports.info') }}",
                "infoEmpty": "{{ trans('reports.info_empty') }}",
                "infoFiltered": "{{ trans('reports.info_filtered') }}",
                "search": "{{ trans('reports.search') }}",
                "paginate": {
                    "first": "{{ trans('reports.first') }}",
                    "last": "{{ trans('reports.last') }}",
                    "next": "{{ trans('reports.next') }}",
                    "previous": "{{ trans('reports.previous') }}"
                }
            },
            "lengthMenu": [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "{{ trans('reports.all') }}"]
            ],
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