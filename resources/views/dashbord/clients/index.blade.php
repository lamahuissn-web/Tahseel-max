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
            "searching": true,
            "order": [],
            "deferRender": true,
            "stateSave": false,
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
                    className: 'text-center no-export'
                },
                {
                    data: 'name',
                    className: 'text-center'
                },
                {
                    data: 'phone',
                    className: 'text-center'
                },
                // {data: 'email', className: 'text-center'},
                {
                    data: 'user',
                    className: 'text-center'
                },
                {
                    data: 'box_switch',
                    className: 'text-center'
                },
                {
                    data: 'client_type',
                    className: 'text-center'
                },
                {
                    data: 'address1',
                    className: 'text-center',
                    width: "200px"
                },
                {
                    data: 'subscription',
                    className: 'text-center'
                },
                {
                    data: 'price',
                    className: 'text-center'
                },
                {
                    data: 'notes',
                    className: 'text-center',
                    width: "200px"
                },
                {
                    data: 'start_date',
                    className: 'text-center'
                },
                {
                    data: 'remaining_amount',
                    className: 'text-center'
                },
                {
                    data: 'status',
                    className: 'text-center',
                    orderable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    className: 'text-center no-export',
                    width: "30px"
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
    });
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
</script>
@endsection