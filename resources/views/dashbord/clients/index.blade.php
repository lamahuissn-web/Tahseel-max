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

    /* Responsive DataTable adjustments */

    @media (max-width: 991.98px) {
        td.name-trigger {
            cursor: pointer;
            color: #0d6efd !important;
        }
        td.name-trigger::before {
            display: none !important;
        }
        .name-mobile-trigger {
            cursor: pointer;
            color: #0d6efd !important;
            font-weight: 600;
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

    #clientDetailTabs .nav-link {
        font-size: 14px;
        padding: 8px 16px;
    }
    #clientDetailTabs .nav-link.active {
        font-weight: 600;
    }
    #clientDetailTabs .nav-link i {
        margin-left: 4px;
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

<div id="kt_app_content_container" class="app-container container-xxl">
    <div class="card shadow-sm mb-4 border-top border-4 border-primary">
        <div class="card-body text-center">
            <div>
                <span class="fs-6 text-gray-600 d-block mb-2">إجمالي العملاء</span>
                <span class="fs-1 fw-bolder text-primary" id="clientsCount">0</span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <label for="nameSearch" class="form-label">{{ trans('clients.search_by_name') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('text') !!}</span>
                        <input type="text" id="nameSearch" class="form-control"
                            placeholder="{{ trans('clients.enter_client_name') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="otherFieldsSearch" class="form-label">{{ trans('clients.search_other_fields') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('text') !!}</span>
                        <input type="text" id="otherFieldsSearch" class="form-control search-input"
                            placeholder="{{ trans('clients.search_phone_address_notes') }}">

                    </div>
                </div>

                <div class="col-md-3">
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

                <div class="col-md-3">
                    <label class="form-check-label ms-2" for="showInactiveOnly" style="cursor: pointer;">
                        <strong>{{ trans('clients.show_inactive_only') }}</strong>
                    </label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input toggle-switch-sm" type="checkbox" id="showInactiveOnly" style="cursor: pointer;">
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="card shadow-sm card-border-top-primary">
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
        'clients.radius_status',
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
                    <div class="skeleton-loader">
                        <div class="skeleton skeleton-avatar mx-auto mb-3"></div>
                        <div class="skeleton skeleton-title mx-auto mb-3"></div>
                        <div class="skeleton skeleton-text mx-auto mb-2" style="width: 80%;"></div>
                        <div class="skeleton skeleton-text mx-auto mb-2" style="width: 60%;"></div>
                        <div class="skeleton skeleton-text mx-auto mb-2" style="width: 70%;"></div>
                        <div class="skeleton skeleton-text mx-auto" style="width: 50%;"></div>
                    </div>
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
                    <div class="skeleton-loader">
                        <div class="skeleton skeleton-title mx-auto mb-3"></div>
                        <div class="skeleton skeleton-text mx-auto mb-2" style="width: 90%;"></div>
                        <div class="skeleton skeleton-text mx-auto mb-2" style="width: 85%;"></div>
                        <div class="skeleton skeleton-text mx-auto mb-2" style="width: 80%;"></div>
                        <div class="skeleton skeleton-text mx-auto" style="width: 75%;"></div>
                    </div>
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

<!-- SAS 4 Quick Panel Modal -->


 
@stop

@section('js')

<script>
    var datatable;
    var globalDataTableConfig = {
        scrollX: true,
        select: false,
        dom: "<'row mb-2'" +
            "<'col-sm-6 d-flex align-items-center justify-conten-start dt-toolbar'l>" +
            "<'col-sm-6 d-flex align-items-center justify-content-end dt-toolbar'f>" +
            ">" +
            "<'table-responsive'tr>" +
            "<'row'" +
            "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
            "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
            ">",
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            type: 'GET',
            data: function(d) {
                d.name_search = $('#nameSearch').val();
                d.other_fields_search = $('#otherFieldsSearch').val();
                d.client_type_filter = $('#clientTypeFilter').val();
                d.show_inactive_only = $('#showInactiveOnly').is(':checked') ? '1' : '0';
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'phone', name: 'phone' },
            { data: 'user', name: 'user' },
            { data: 'box_switch', name: 'box_switch' },
            { data: 'client_type', name: 'client_type' },
            { data: 'address1', name: 'address1' },
            { data: 'subscription', name: 'subscription' },
            { data: 'price', name: 'price' },
            { data: 'notes', name: 'notes' },
            { data: 'start_date', name: 'start_date' },
            { data: 'remaining_amount', name: 'remaining_amount', orderable: true, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'radius_username', name: 'radius_username', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
        }
    };

    $(document).ready(function() {
        if ('{{ app()->getLocale() }}' != 'ar') {
            delete globalDataTableConfig.language;
        }

        datatable = $('#table1').DataTable(globalDataTableConfig);

        $('#nameSearch').on('keyup', function() {
            datatable.ajax.reload();
        });
        $('#otherFieldsSearch').on('keyup', function() {
            datatable.ajax.reload();
        });
        $('#clientTypeFilter').on('change', function() {
            datatable.ajax.reload();
        });
        $('#showInactiveOnly').on('change', function() {
            datatable.ajax.reload();
        });

        datatable.on('draw', function() {
            var info = datatable.page.info();
            $('#clientsCount').text(info.recordsDisplay || info.recordsTotal);
        });
    });

    function showClientDetails(clientId) {
        $('#clientDetailsModal').modal('show');
        $('#clientDetailsContent').html($('#modalLoader').html());
        $('#editClientBtn').hide();
        $.ajax({
            url: '{{ route("admin.clients.show", ["id" => "__ID__"]) }}'.replace('__ID__', clientId),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.html) {
                    $('#clientDetailsContent').html(res.html);
                } else {
                    $('#clientDetailsContent').html('<div class="alert alert-info">' + res.message + '</div>');
                }
                $('#editClientBtn').attr('href', '{{ route("admin.clients.edit", ["id" => "__ID__"]) }}'.replace('__ID__', clientId)).show();
            },
            error: function() {
                $('#clientDetailsContent').html('<div class="alert alert-danger text-center">Error loading details</div>');
            }
        });
    }

    function changeClientStatus(clientId, currentStatus) {
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
            preConfirm: function() {
                return $.ajax({
                    url: '{{ route("admin.clients.change_status", ["id" => "__ID__"]) }}'.replace('__ID__', clientId),
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json'
                }).then(function(response) {
                    if (!response.success) {
                        throw new Error(response.message || 'Error updating status');
                    }
                    return response;
                }).catch(function(error) {
                    Swal.showValidationMessage(error.message || 'Error updating status');
                });
            },
            allowOutsideClick: function() { return !Swal.isLoading(); }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    icon: 'success',
                    title: 'Status updated',
                    timer: 1500,
                    showConfirmButton: false
                });
                datatable.ajax.reload();
            }
        });
    }

    $(document).on('submit', '#ajaxPayInvoiceForm', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = $('#ajaxPaySubmitBtn');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#ajaxPayInvoiceModal').modal('hide');
                    Swal.fire({ icon: 'success', title: res.message || 'Payment successful', timer: 1500, showConfirmButton: false });
                    datatable.ajax.reload();
                } else {
                    $('#ajaxPayError').removeClass('d-none').text(res.message || 'Payment failed');
                }
            },
            error: function(xhr) {
                var msg = 'Payment failed';
                try { var res = JSON.parse(xhr.responseText); if (res.message) msg = res.message; } catch(e) {}
                $('#ajaxPayError').removeClass('d-none').text(msg);
            },
            complete: function() { submitBtn.prop('disabled', false).html('Pay'); }
        });
    });

    $('#ajaxPayInvoiceModal').on('hidden.bs.modal', function() {
        $('#ajaxPayInvoiceForm')[0].reset();
        $('#ajaxPayError').addClass('d-none');
    });
</script>

<script>
    $(document).ready(function() {
        $('.dataTables_filter input').css('text-align', 'right');
    });
</script>

@stop
