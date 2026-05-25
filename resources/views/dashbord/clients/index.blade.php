@extends('dashbord.layouts.master')

@section('css')
<style>
    .actions-column .btn-group {
        width: 100%;
    }

    .actions-column .btn {
        padding: 2px 6px !important;
        font-size: 12px !important;
    }

    /* تنسيق badge حالة العميل */
    .status-badge {
        font-size: 13px;
        padding: 6px 12px;
        border-radius: 50px;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .status-badge:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .status-badge i {
        font-size: 14px;
    }

    /* مؤشر النقر على badge القابل للنقر */
    .cursor-pointer {
        cursor: pointer;
    }

    .cursor-pointer:hover .status-badge {
        opacity: 0.8;
    }

    /* Responsive DataTable: hide default + icon, use name as trigger */
    table.table-bordered > tbody > tr > td > .dtr-title {
        display: none !important;
    }
    table.table-bordered > tbody > tr > td > .dtr-data {
        display: block !important;
    }

    @media (max-width: 991.98px) {
        td.name-trigger {
            cursor: pointer;
            color: #0d6efd !important;
        }
        td.name-trigger::before {
            display: none !important;
        }
        .remaining-mobile-trigger {
            cursor: pointer;
            color: #0d6efd !important;
            font-weight: 600;
        }
    }

    /* Name truncation */
    .name-cell {
        max-width: 180px;
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

</style>
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('client.clients');
    $breadcrumbs = [
    ['label' => trans('Toolbar.home'), 'link' => route('admin.clients.create')],
    ['label' => trans('Toolbar.clients'), 'link' => ''],
    ['label' => trans('client.clients_table'), 'link' => ''],
    ];

    PageTitle($title, $breadcrumbs);
    @endphp


    <div class="d-flex align-items-center gap-2 gap-lg-3">

        @can('create_client')
        {{ AddButton(route('admin.clients.create')) }}
        @endcan
    </div>
</div>

@endsection
@section('content')

<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card shadow-sm mb-4 border-top border-4 border-primary">
        <div class="card-body text-center">
            <div>
                <span class="fs-6 text-gray-600 d-block mb-2">إجمالي العملاء</span>
                <span class="fs-1 fw-bolder text-primary" id="clientsCount">0</span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm" style="margin-bottom: 20px;">
        <div class="card-body">
            <div class="col-md-12 row">

                <div class="col-md-3" style="margin-bottom: 10px;">
                    <label for="nameSearch" class="form-label">{{ trans('clients.search_by_name') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('text') !!}</span>
                        <input type="text" id="nameSearch" class="form-control"
                            placeholder="{{ trans('clients.enter_client_name') }}">
                    </div>
                </div>

                <div class="col-md-3" style="margin-bottom: 10px;">
                    <label for="otherFieldsSearch" class="form-label">{{ trans('clients.search_other_fields') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('text') !!}</span>
                        <input type="text" id="otherFieldsSearch" class="form-control search-input"
                            placeholder="{{ trans('clients.search_phone_address_notes') }}">

                    </div>
                </div>

                <div class="col-md-3" style="margin-bottom: 10px;">
                    <label for="clientTypeFilter" class="form-label">{{ trans('clients.client_type') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('select2') !!}</span>
                        <select class="form-select" id="clientTypeFilter">
                            <option value="">{{ trans('clients.all_types') }}</option>
                            <option value="internet">{{ trans('clients.internet') }}</option>
                            <option value="satellite">{{ trans('clients.satellite') }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3" style="margin-bottom: 10px;">
                    <label class="form-check-label ms-2" for="showInactiveOnly" style="cursor: pointer; ">
                        <strong>{{ trans('clients.show_inactive_only') }}</strong>
                    </label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="showInactiveOnly" style="width: 50px; height: 25px; cursor: pointer;">

                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
        @php
        $headers = [
        'clients.ID',
        'clients.name',
        'clients.phone',
        // 'clients.email',
        'clients.user',
        'clients.box_switch',
        'clients.client_type',
        'clients.address1',
        'clients.subscription',
        'clients.price',
        // 'clients.subscription_date',
        'clients.notes',
        'clients.start_date',
        'clients.remaining_amount',
        'clients.status',
        'clients.sas4_column',
        'clients.action',
        ];

        generateTable($headers);
        @endphp
    </div>

</div>


<div class="modal fade" id="clientDetailsModal" tabindex="-1" aria-labelledby="clientDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="clientDetailsModalLabel">
                    <i class="bi bi-person-circle"></i> {{ trans('clients.client_details') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="clientDetailsContent">
                <div class="text-center py-5" id="modalLoader">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">{{ trans('clients.loading_details') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> {{ trans('clients.close') }}
                </button>
                <a href="#" id="editClientBtn" class="btn btn-primary" target="_blank" style="display: none;">
                    <i class="bi bi-pencil-square"></i> {{ trans('clients.edit_clients') }}
                </a>
            </div>
        </div>
    </div>
</div>







<!-- Remaining Invoices Modal -->
<div class="modal fade" id="remainingInvoicesModal" tabindex="-1" aria-labelledby="remainingInvoicesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="remainingInvoicesModalLabel">
                    <i class="bi bi-receipt-cutoff"></i> {{ trans('clients.client_unpaid_invoices') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="remainingInvoicesContent">
                <div class="text-center py-5" id="remainingModalLoader">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">{{ trans('clients.loading_details') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> {{ trans('clients.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Pay Invoice Modal (for AJAX pay within remaining invoices modal) -->
<div class="modal fade" id="ajaxPayInvoiceModal" tabindex="-1" aria-labelledby="ajaxPayInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="ajaxPayInvoiceModalLabel">{{ trans('invoices.enter_payment_amount') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ajaxPayInvoiceForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ajax_invoice_amount" class="form-label">{{ trans('invoices.invoice_amount') }}</label>
                        <input type="number" step="0.01" class="form-control" id="ajax_invoice_amount" name="invoice_amount" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="ajax_paid_amount" class="form-label">{{ trans('invoices.invoice_paid_amount') }}</label>
                        <input type="number" step="0.01" class="form-control" id="ajax_paid_amount" name="paid_amount">
                        <small class="text-muted">{{ trans('invoices.remaining_amount') }}: <span id="ajax_remaining_amount_note"></span></small>
                    </div>
                    <div class="mb-3">
                        <label for="ajax_paid_date" class="form-label">{{ trans('invoices.paid_date') }}</label>
                        <input type="date" class="form-control" id="ajax_paid_date" name="paid_date">
                        <small class="text-muted">{{ trans('invoices.optional_paid_date_update') }}</small>
                    </div>
                    <div class="mb-3">
                        <label for="ajax_notes" class="form-label">{{ trans('invoices.notes') }}</label>
                        <textarea class="form-control" id="ajax_notes" name="notes" rows="2"></textarea>
                    </div>
                    <div id="ajaxPayError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('invoices.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="ajaxPaySubmitBtn">{{ trans('invoices.pay') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop
@section('js')

<script>
    var sas4Labels = {!! json_encode([
        'internetInfo' => trans('clients.sas4_internet_info'),
        'username' => trans('clients.sas4_username'),
        'status' => trans('clients.sas4_status'),
        'plan' => trans('clients.sas4_plan'),
        'speed' => trans('clients.sas4_speed'),
        'balance' => trans('clients.sas4_balance'),
        'expiration' => trans('clients.sas4_expiration'),
        'lastOnline' => trans('clients.sas4_last_online'),
        'lastIp' => trans('clients.sas4_last_ip'),
        'created' => trans('clients.sas4_created'),
        'download' => trans('clients.sas4_download'),
        'upload' => trans('clients.sas4_upload'),
        'total' => trans('clients.sas4_total'),
        'uptime' => trans('clients.sas4_uptime'),
        'control' => trans('clients.sas4_control'),
        'enable' => trans('clients.sas4_enable'),
        'disable' => trans('clients.sas4_disable'),
        'disconnect' => trans('clients.sas4_disconnect'),
        'selectPlan' => trans('clients.sas4_select_plan'),
        'profile' => trans('clients.sas4_profile'),
        'changePlan' => trans('clients.sas4_change_plan'),
        'confirmEnable' => trans('clients.sas4_confirm_enable'),
        'confirmDisable' => trans('clients.sas4_confirm_disable'),
        'confirmDisconnect' => trans('clients.sas4_confirm_disconnect'),
        'confirmChangePlan' => trans('clients.sas4_confirm_change_plan'),
        'profileRequired' => trans('clients.sas4_profile_required'),
        'actionFailed' => trans('clients.sas4_action_failed'),
        'actionSuccess' => trans('clients.sas4_action_success'),
        'cancel' => trans('clients.cancel'),
        'confirmBtn' => trans('forms.action_yes'),
    ]) !!};

    var sas4StatusLabels = {!! json_encode([
        'online' => trans('clients.sas4_online'),
        'disabled' => trans('clients.sas4_disabled'),
        'expired' => trans('clients.sas4_expired'),
        'offline' => trans('clients.sas4_offline'),
    ]) !!};
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
            "searching": true,
            "order": [],
            "deferRender": true,
            "stateSave": false,
            "responsive": {
                "details": {
                    "type": 'inline',
                    "target": 'td.name-trigger'
                }
            },
            "pagingType": "simple_numbers",
            "ajax": {
                url: "{{ route('admin.clients.index') }}",
                data: function(d) {
                    d.name_search = $('#nameSearch').val();
                    d.other_fields_search = $('#otherFieldsSearch').val();
                    d.client_type_filter = $('#clientTypeFilter').val();
                    d.show_inactive_only = $('#showInactiveOnly').is(':checked') ? 1 : 0;
                },
                dataSrc: function(json) {
                    $('#clientsCount').text(json.recordsFiltered);
                    $('#clientsCountSummary').text(json.recordsFiltered);
                    const totalRemaining = json.total_remaining ?? 0;
                    const currency = "{{ get_app_config_data('currency') }}";
                    const formatNumber = (num) => {
                        return new Intl.NumberFormat('ar-EG').format(num);
                    };
                    $('#clientsRemainingSummary').html(formatNumber(totalRemaining.toFixed(2)) + ' ' + currency);
                    const avgRemaining = json.recordsFiltered > 0 ? (totalRemaining / json.recordsFiltered) : 0;
                    $('#clientsAvgSummary').html(formatNumber(avgRemaining.toFixed(2)) + ' ' + currency);
                    return json.data;
                }
            },
            "columns": [{
                    data: 'id',
                    className: 'text-center no-export',
                    responsivePriority: 10001
                },
                {
                    data: 'name',
                    className: 'text-center name-trigger',
                    responsivePriority: 1,
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            return '<span class="name-cell" title="' + $('<span>').text(data).html() + '">' + $('<span>').text(data).html() + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'phone',
                    className: 'text-center',
                    responsivePriority: 10002
                },
                // {data: 'email', className: 'text-center'},
                {
                    data: 'user',
                    className: 'text-center',
                    responsivePriority: 10003
                },
                {
                    data: 'box_switch',
                    className: 'text-center',
                    responsivePriority: 10004
                },
                {
                    data: 'client_type',
                    className: 'text-center',
                    responsivePriority: 10005
                },
                {
                    data: 'address1',
                    className: 'text-center',
                    width: "200px",
                    responsivePriority: 10006
                },
                {
                    data: 'subscription',
                    className: 'text-center',
                    responsivePriority: 10007
                },
                {
                    data: 'price',
                    className: 'text-center',
                    responsivePriority: 10008
                },
                {
                    data: 'notes',
                    className: 'text-center',
                    width: "200px",
                    responsivePriority: 10009,
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            return data.length > 60
                                ? '<span title="' + $('<span>').text(data).html() + '">' + $('<span>').text(data.substring(0, 60)).html() + '...</span>'
                                : $('<span>').text(data).html();
                        }
                        return data;
                    }
                },
                {
                    data: 'start_date',
                    className: 'text-center',
                    responsivePriority: 10010
                },
                {
                    data: 'remaining_amount',
                    className: 'text-center',
                    responsivePriority: 2,
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var displayVal = data || '0.00';
                            return '<span class="d-none d-lg-block">' + displayVal + '</span>' +
                                '<span class="d-lg-none remaining-mobile-trigger" onclick="showRemainingInvoices(' + row.id + ')">' + displayVal + ' &#9654;</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    orderable: false,
                    responsivePriority: 3
                },
                {
                    data: 'sas4_status',
                    className: 'text-center all',
                    orderable: false,
                    responsivePriority: 1
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    className: 'text-center no-export',
                    width: "30px",
                    responsivePriority: 4
                },
            ],
            "columnDefs": [{
                    "targets": [-1], //last column
                    "orderable": false, //set not orderable
                    "width": "25px",
                    "className": "text-center no-export actions-column"
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
                            'direction': 'ltr'
                        });
                    }
                },

                {
                    "targets": [5, 11],
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

        // استخدام debounce لتقليل عدد الطلبات
        let searchTimeout;
        $('#nameSearch, #otherFieldsSearch').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                table.ajax.reload(null, false); // false = لا يعيد تعيين صفحة واحدة
            }, 500); // انتظر 500ms قبل البحث
        });

        $('#nameSearch, #otherFieldsSearch').on('change', function() {
            table.ajax.reload(null, false);
        });

        // تحديث الجدول عند تغيير نوع العميل
        $('#clientTypeFilter').on('change', function() {
            table.ajax.reload(null, false);
        });

        // تحديث الجدول عند تغيير checkbox
        $('#showInactiveOnly').on('change', function() {
            table.ajax.reload(null, false);
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

        // Fetch SAS4 online status after table draws
        table.on('draw', function() {
            loadSas4TableStatus();
        });
    });

    function loadSas4TableStatus() {
        var indicators = $('.sas4-indicator');
        if (!indicators.length) return;

        var usernames = [];
        var map = {};
        indicators.each(function() {
            var username = $(this).data('username');
            var id = $(this).data('id');
            if (username) {
                usernames.push(username);
                map[username] = $(this);
            }
        });

        if (!usernames.length) return;

        $.ajax({
            url: '{{ route('admin.sas4.online_status') }}',
            type: 'POST',
            data: { usernames: usernames },
            dataType: 'json',
            success: function(res) {
                $.each(res, function(username, info) {
                    var el = map[username];
                    if (!el) return;

                    if (info.online == 1 && info.enabled == 1) {
                        el.html('<i class="bi bi-wifi text-success" title="Online"></i> ' + username);
                    } else if (info.enabled == 0) {
                        el.html('<i class="bi bi-wifi-off text-danger" title="Disabled"></i> ' + username);
                    } else {
                        el.html('<i class="bi bi-wifi text-secondary" title="Offline"></i> ' + username);
                    }
                });
            }
        });
    }
</script>

<script>
    function confirmDelete(clientId) {
        Swal.fire({
            title: '{{ trans("employees.confirm_delete") }}',
            text: '{{ trans("clients.delete_warning") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{ trans("employees.yes_delete") }}',
            cancelButtonText: '{{ trans("employees.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + clientId).submit();
            }
        });
    }
</script>

<script>
    function showClientDetails(clientId) {
        $('#clientDetailsModal').modal('show');

        $('#modalLoader').show();
        $('#clientDetailsContent').html($('#modalLoader'));
        $('#editClientBtn').hide();

        $.ajax({
            url: "{{ route('admin.clients.details', '') }}/" + clientId,
            type: 'GET',
            success: function(response) {
                $('#modalLoader').hide();
                $('#clientDetailsContent').html(response);
                $('#editClientBtn').attr('href', "{{ route('admin.clients.edit', '') }}/" + clientId).show();
                loadSas4Info(clientId);
            },
            error: function(xhr, status, error) {
                $('#modalLoader').hide();
                $('#clientDetailsContent').html(
                    '<div class="alert alert-danger text-center">' +
                    '<i class="bi bi-exclamation-triangle fs-1 text-danger"></i>' +
                    '<h4 class="mt-3">{{ trans("clients.error_loading_details") }}</h4>' +
                    '<p class="mb-0">{{ trans("clients.please_try_again") }}</p>' +
                    '</div>'
                );
            }
        });
    }

    function loadSas4Info(clientId) {
        $.ajax({
            url: '{{ route('admin.clients.sas4_info', ['id' => '__ID__']) }}'.replace('__ID__', clientId),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                var user = res.user || {};
                var overview = res.overview || {};
                var traffic = res.traffic || {};
                var trafficData = traffic.data || traffic.daily || [];

                var statusBadge = '';
                if (user.enabled == 1 && user.online == 1) {
                    statusBadge = '<span class="badge bg-success">' + sas4StatusLabels.online + '</span>';
                } else if (user.enabled == 0) {
                    statusBadge = '<span class="badge bg-danger">' + sas4StatusLabels.disabled + '</span>';
                } else if (user.expired == 1) {
                    statusBadge = '<span class="badge bg-warning text-dark">' + sas4StatusLabels.expired + '</span>';
                } else {
                    statusBadge = '<span class="badge bg-secondary">' + sas4StatusLabels.offline + '</span>';
                }

                var profileName = user.profile_name || user.profile || 'N/A';
                var speedDown = user.speed_down || overview.speed_down || 'N/A';
                var speedUp = user.speed_up || overview.speed_up || 'N/A';
                var speed = speedDown + ' / ' + speedUp + ' Mbps';

                var t = sas4Labels;

                var html = '<div id="sas4Container_' + clientId + '">' +
                    '<div class="card mt-3" style="border:1px solid #0d6efd;">' +
                    '<div class="card-header" style="background:#0d6efd;color:#fff;padding:8px 16px;">' +
                    '<i class="bi bi-wifi"></i> ' + t.internetInfo + '</div>' +
                    '<div class="card-body p-2">' +
                    '<div class="row g-2" style="font-size:13px;">' +
                    '<div class="col-6"><strong>' + t.username + ':</strong> ' + (user.username || 'N/A') + '</div>' +
                    '<div class="col-6"><strong>' + t.status + ':</strong> ' + statusBadge + '</div>' +
                    '<div class="col-6"><strong>' + t.plan + ':</strong> ' + profileName + '</div>' +
                    '<div class="col-6"><strong>' + t.speed + ':</strong> ' + speed + '</div>' +
                    '<div class="col-6"><strong>' + t.balance + ':</strong> ' + (user.balance || '0.00') + '</div>' +
                    '<div class="col-6"><strong>' + t.expiration + ':</strong> ' + (user.expiration || 'N/A') + '</div>' +
                    '<div class="col-6"><strong>' + t.lastOnline + ':</strong> ' + (user.last_login || 'N/A') + '</div>' +
                    '<div class="col-6"><strong>' + t.lastIp + ':</strong> ' + (user.last_ip || 'N/A') + '</div>' +
                    '<div class="col-6"><strong>' + t.created + ':</strong> ' + (user.created_at || 'N/A') + '</div>' +
                    '</div>';

                if (trafficData && trafficData.length > 0) {
                    html += '<table class="table table-sm table-bordered mt-2" style="font-size:12px;"><thead class="table-light"><tr>' +
                        '<th>' + t.download + '</th>' +
                        '<th>' + t.upload + '</th>' +
                        '<th>' + t.total + '</th>' +
                        '<th>' + t.uptime + '</th>' +
                        '</tr></thead><tbody>';
                    var last7 = trafficData.slice(-7);
                    last7.forEach(function(day) {
                        html += '<tr>' +
                            '<td>' + (day.download || day.bytes_in || '0 B') + '</td>' +
                            '<td>' + (day.upload || day.bytes_out || '0 B') + '</td>' +
                            '<td>' + (day.total || day.bytes_total || '0 B') + '</td>' +
                            '<td>' + (day.uptime || day.session_time || 'N/A') + '</td>' +
                            '</tr>';
                    });
                    html += '</tbody></table>';
                }

                html += '</div></div>';

                html += '<div class="card mt-2" style="border:1px solid #6c757d;">' +
                    '<div class="card-header" style="background:#6c757d;color:#fff;padding:8px 16px;">' +
                    '<i class="bi bi-gear-fill"></i> ' + t.control + '</div>' +
                    '<div class="card-body p-2">' +
                    '<div class="d-flex flex-wrap gap-2">' +
                    '<button class="btn btn-sm btn-success" onclick="sas4ControlAction(' + clientId + ', \'enable\')">' +
                    '<i class="bi bi-check-circle"></i> ' + t.enable + '</button>' +
                    '<button class="btn btn-sm btn-danger" onclick="sas4ControlAction(' + clientId + ', \'disable\')">' +
                    '<i class="bi bi-x-circle"></i> ' + t.disable + '</button>' +
                    '<button class="btn btn-sm btn-warning" onclick="sas4ControlAction(' + clientId + ', \'disconnect\')">' +
                    '<i class="bi bi-plug-fill"></i> ' + t.disconnect + '</button>' +
                    '</div>' +
                    '<div class="d-flex flex-wrap gap-2 mt-2 align-items-end">' +
                    '<div class="flex-grow-1">' +
                    '<label class="form-label mb-1" style="font-size:12px;">' + t.selectPlan + '</label>' +
                    '<select class="form-select form-select-sm" id="sas4ProfileSelect_' + clientId + '" style="font-size:12px;">' +
                    '<option value="">-- ' + t.profile + ' --</option>' +
                    '</select>' +
                    '</div>' +
                    '<div>' +
                    '<label class="form-label mb-1" style="font-size:12px;">@lang('clients.sas4_expiration')</label>' +
                    '<input type="date" class="form-control form-control-sm" id="sas4ExpirationInput_' + clientId + '" style="font-size:12px;width:150px;">' +
                    '</div>' +
                    '<button class="btn btn-sm btn-primary" onclick="sas4ControlAction(' + clientId + ', \'change_profile\')">' +
                    '<i class="bi bi-arrow-repeat"></i> ' + t.changePlan + '</button>' +
                    '</div>' +
                    '</div></div></div>';

                $('#sas4Container_' + clientId).remove();
                $('#clientDetailsContent').append(html);

                var expDate = user.expiration || '';
                if (expDate) {
                    var dateStr = expDate.substring(0, 10);
                    $('#sas4ExpirationInput_' + clientId).val(dateStr);
                }

                loadSas4Profiles(clientId);
            },
            error: function(xhr) {
                console.log('SAS4 info load failed:', xhr.status);
            }
        });
    }

    function loadSas4Profiles(clientId) {
        $.ajax({
            url: '{{ route('admin.sas4.profiles') }}',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                var profiles = res.data || [];
                var select = $('#sas4ProfileSelect_' + clientId);
                if (!select.length) return;

                profiles.forEach(function(p) {
                    select.append('<option value="' + p.id + '">' + (p.name || p.profilename || p.profile_name || p.id) + '</option>');
                });
            },
            error: function() {}
        });
    }

    function sas4ControlAction(clientId, action) {
        var t = sas4Labels;

        var confirmText = '';
        var confirmIcon = 'question';

        switch (action) {
            case 'enable':
                confirmText = t.confirmEnable;
                confirmIcon = 'success';
                break;
            case 'disable':
                confirmText = t.confirmDisable;
                confirmIcon = 'warning';
                break;
            case 'disconnect':
                confirmText = t.confirmDisconnect;
                confirmIcon = 'warning';
                break;
            case 'change_profile':
                var profileId = $('#sas4ProfileSelect_' + clientId).val();
                if (!profileId) {
                    Swal.fire({
                        icon: 'warning',
                        title: t.profileRequired,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }
                confirmText = t.confirmChangePlan;
                confirmIcon = 'info';
                break;
        }

        Swal.fire({
            title: t.control,
            text: confirmText,
            icon: confirmIcon,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: t.confirmBtn,
            cancelButtonText: t.cancel,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                var data = { action: action };
                if (action === 'change_profile') {
                    data.profile_id = $('#sas4ProfileSelect_' + clientId).val();
                    var expDate = $('#sas4ExpirationInput_' + clientId).val();
                    if (expDate) {
                        data.expiration_date = expDate;
                    }
                }

                return fetch('{{ route('admin.clients.sas4_control', ['id' => '__ID__']) }}'.replace('__ID__', clientId), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: new URLSearchParams(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return data;
                    } else {
                        Swal.showValidationMessage(data.message || t.actionFailed);
                    }
                })
                .catch(error => {
                    Swal.showValidationMessage(t.actionFailed);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    icon: 'success',
                    title: t.actionSuccess,
                    text: result.value.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    loadSas4Info(clientId);
                });
            }
        });
    }
</script>

<script>
    function changeClientStatus(clientId, currentStatus) {
        // عرض تأكيد
        Swal.fire({
            title: '{{ trans("clients.change_status") }}',
            text: '{{ trans("clients.change_status_msg") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '{{ trans("clients.yes_change_status") }}',
            cancelButtonText: '{{ trans("clients.cancel") }}',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch(`{{ route('admin.clients.change_status', ['id' => ':id', 'status' => ':status']) }}`
                        .replace(':id', clientId)
                        .replace(':status', currentStatus), {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // تحديث الـ badge في الجدول
                            updateStatusBadge(clientId, data.new_status, data.new_status_text);
                            Swal.fire({
                                icon: 'success',
                                title: '{{ trans("forms.success") }}',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ trans("forms.error") }}',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ trans("forms.error") }}',
                            text: '{{ trans("clients.error_changing_status") }}'
                        });
                        console.error('Error:', error);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    }

    function updateStatusBadge(clientId, newStatus, newStatusText) {
        // البحث عن الصف في الجدول وتحديث badge مباشرة
        const table = $('#table1').DataTable();

        // البحث عن الصف بناءً على ID
        table.rows().every(function() {
            const rowData = this.data();
            if (rowData.id == clientId) {
                // تحديث حالة العميل في البيانات
                rowData.is_active = newStatus;

                // إنشاء badge جديد
                const statusClass = newStatus == '1' ? 'badge bg-success text-white' : 'badge bg-danger text-white';
                const statusIcon = newStatus == '1' ?
                    '<i class="bi bi-check-circle-fill me-1"></i>' :
                    '<i class="bi bi-x-circle-fill me-1"></i>';

                const badge = `<a href="javascript:void(0)" 
                        onclick="changeClientStatus(${clientId}, '${newStatus}')"
                        class="text-decoration-none cursor-pointer"
                        title="تغيير الحالة">
                        <span class="${statusClass} px-3 py-2 rounded-pill fw-bold status-badge">${statusIcon}${newStatusText}</span>
                    </a>`;

                // تحديث عمود status في الصف
                rowData.status = badge;

                // تحديث البيانات وإعادة رسم الصف
                this.data(rowData);
                return false; // إيقاف التكرار
            }
        });

        // إعادة رسم الجدول بدون refresh
        table.draw(false);
    }

    function showRemainingInvoices(clientId) {
        $('#remainingInvoicesModal').modal('show');
        $('#remainingModalLoader').show();
        $('#remainingInvoicesContent').html($('#remainingModalLoader'));

        $.ajax({
            url: '{{ route('admin.clients.remaining_invoices', ['id' => '__CLIENT_ID__']) }}'.replace('__CLIENT_ID__', clientId),
            type: 'GET',
            success: function(response) {
                $('#remainingModalLoader').hide();
                $('#remainingInvoicesContent').html(response);
            },
            error: function() {
                $('#remainingModalLoader').hide();
                $('#remainingInvoicesContent').html(
                    '<div class="alert alert-danger text-center">' +
                    '<i class="bi bi-exclamation-triangle fs-1 text-danger"></i>' +
                    '<h4 class="mt-3">{{ trans("clients.error_loading_details") }}</h4>' +
                    '<p class="mb-0">{{ trans("clients.please_try_again") }}</p>' +
                    '</div>'
                );
            }
        });
    }

    function showAjaxPayModal(invoiceId, invoiceAmount, remainingAmount) {
        $('#ajaxPayInvoiceModal').modal('show');
        $('#ajaxPayInvoiceForm').attr('data-invoice-id', invoiceId);
        $('#ajax_invoice_amount').val(invoiceAmount);
        $('#ajax_paid_amount').val('').attr('max', invoiceAmount);
        $('#ajax_remaining_amount_note').text(remainingAmount);
        $('#ajax_paid_date').val(new Date().toISOString().split('T')[0]);
        $('#ajax_notes').val('');
        $('#ajaxPayError').addClass('d-none').text('');
        $('#ajaxPaySubmitBtn').prop('disabled', false);
    }

    $(document).ready(function() {
        $('#ajaxPayInvoiceForm').on('submit', function(e) {
            e.preventDefault();
            var invoiceId = $(this).attr('data-invoice-id');
            var formData = $(this).serialize();
            $('#ajaxPayError').addClass('d-none').text('');
            $('#ajaxPaySubmitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> {{ trans("forms.Loading") }}');

            $.ajax({
                url: '{{ route('admin.pay_invoice', '') }}/' + invoiceId,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#ajaxPayInvoiceModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '{{ trans("forms.success") }}',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        // Reload remaining invoices modal content
                        var clientId = $('#remainingInvoicesModal').data('client-id');
                        if (clientId) {
                            showRemainingInvoices(clientId);
                        }
                        // Reload main DataTable to update remaining_amount
                        $('#table1').DataTable().ajax.reload(null, false);
                    } else {
                        $('#ajaxPayError').removeClass('d-none').text(response.message);
                        $('#ajaxPaySubmitBtn').prop('disabled', false).html('{{ trans("invoices.pay") }}');
                    }
                },
                error: function(xhr) {
                    var message = '{{ trans("forms.error") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    $('#ajaxPayError').removeClass('d-none').text(message);
                    $('#ajaxPaySubmitBtn').prop('disabled', false).html('{{ trans("invoices.pay") }}');
                }
            });
        });

        // Store client-id on modal show for reload
        $(document).on('show.bs.modal', '#remainingInvoicesModal', function() {
            // data-client-id is set in the partial view
        });
    });
</script>
@endsection