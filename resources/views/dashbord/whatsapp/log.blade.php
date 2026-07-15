@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_message_logs') ?? 'سجل الرسائل' }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_message_logs') ?? 'سجل الرسائل';
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
        ['label' => trans('clients.whatsapp_message_logs') ?? 'سجل الرسائل', 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')

@include('dashbord.whatsapp._partials.tab-nav')
<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('clients.whatsapp_message_logs') ?? 'سجل الرسائل' }}</h3>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <div class="row g-3 mb-6 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fs-7">{{ trans('clients.search') ?? 'بحث' }}</label>
                    <input type="text" class="form-control form-control-sm" id="logSearch"
                           placeholder="{{ trans('clients.whatsapp_search_placeholder') ?? 'ابحث باسم الزبون أو الرقم...' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7">{{ trans('clients.status') ?? 'الحالة' }}</label>
                    <select class="form-select form-select-sm" id="logStatus">
                        <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                        <option value="sent">✅ {{ trans('clients.whatsapp_sent') ?? 'تم الإرسال' }}</option>
                        <option value="failed">❌ {{ trans('clients.whatsapp_failed_send') ?? 'فشل' }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7">{{ trans('clients.whatsapp_template_type') ?? 'القالب' }}</label>
                    <select class="form-select form-select-sm" id="logTemplate">
                        <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                        <option value="reminder">{{ trans('clients.whatsapp_reminder') ?? 'تذكير' }}</option>
                        <option value="receipt">{{ trans('clients.whatsapp_receipt') ?? 'إيصال' }}</option>
                        <option value="disconnection">{{ trans('clients.whatsapp_disconnection') ?? 'قطع خدمة' }}</option>
                        <option value="custom">{{ trans('clients.whatsapp_custom') ?? 'مخصص' }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7">{{ trans('clients.from') ?? 'من' }}</label>
                    <input type="date" class="form-control form-control-sm" id="logDateFrom">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-7">{{ trans('clients.to') ?? 'إلى' }}</label>
                    <input type="date" class="form-control form-control-sm" id="logDateTo">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-primary w-100" id="logFilterBtn">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-row-bordered table-align-middle" id="logTable">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>{{ trans('clients.client_name') ?? 'الزبون' }}</th>
                            <th>{{ trans('clients.phone') ?? 'الهاتف' }}</th>
                            <th>{{ trans('clients.whatsapp_template_type') ?? 'القالب' }}</th>
                            <th>{{ trans('clients.status') ?? 'الحالة' }}</th>
                            <th>{{ trans('clients.date') ?? 'التاريخ' }}</th>
                            <th>{{ trans('clients.actions') ?? 'إجراءات' }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Message Detail Modal --}}
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('clients.whatsapp_message_details') ?? 'تفاصيل الرسالة' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ trans('clients.whatsapp_message_content') ?? 'محتوى الرسالة' }}</label>
                    <div id="modalMessageBody" class="bg-light p-4 rounded border"
                         style="white-space: pre-wrap; direction: rtl; text-align: right; font-family: 'Tajawal', sans-serif;"></div>
                </div>
                <div id="modalError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('clients.close') ?? 'إغلاق' }}</button>
                <button type="button" class="btn btn-success" id="resendBtn">
                    <i class="bi bi-arrow-clockwise"></i> {{ trans('clients.whatsapp_resend') ?? 'إعادة إرسال' }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    let logTable = $('#logTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.whatsapp.log.data") }}',
            data: function(d) {
                d.search = $('#logSearch').val();
                d.status = $('#logStatus').val();
                d.template_type = $('#logTemplate').val();
                d.date_from = $('#logDateFrom').val();
                d.date_to = $('#logDateTo').val();
            }
        },
        columns: [
            { data: 'client_name', name: 'client_name' },
            { data: 'phone', name: 'phone' },
            {
                data: 'template_type',
                name: 'template_type',
                render: function(data) {
                    const labels = {
                        'reminder': '{{ trans("clients.whatsapp_reminder") ?? "تذكير" }}',
                        'receipt': '{{ trans("clients.whatsapp_receipt") ?? "إيصال" }}',
                        'disconnection': '{{ trans("clients.whatsapp_disconnection") ?? "قطع خدمة" }}',
                        'custom': '{{ trans("clients.whatsapp_custom") ?? "مخصص" }}',
                    };
                    return labels[data] || data || '-';
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    return data === 'sent'
                        ? '<span class="badge badge-success">✅ {{ trans("clients.whatsapp_sent") ?? "تم الإرسال" }}</span>'
                        : '<span class="badge badge-danger">❌ {{ trans("clients.whatsapp_failed_send") ?? "فشل" }}</span>';
                }
            },
            { data: 'created_at', name: 'created_at' },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-light view-message" data-id="${data.id}"
                                data-message='${$('<div>').text(data.message_full || '').html()}'
                                data-error='${$('<div>').text(data.error || '').html()}'
                                data-status="${data.status}">
                            <i class="bi bi-eye"></i>
                        </button>
                        ${data.status === 'failed' ? `
                        <button class="btn btn-sm btn-warning resend-message" data-id="${data.id}">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>` : ''}
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        },
        order: [[4, 'desc']],
        pageLength: 25,
        lengthMenu: [25, 50, 100],
        dom: '<"d-flex justify-content-between align-items-center"lf>t<"d-flex justify-content-between"ip>',
    });

    // Filter button
    $('#logFilterBtn').on('click', function() {
        logTable.ajax.reload();
    });

    // Enter key in search
    $('#logSearch').on('keyup', function(e) {
        if (e.key === 'Enter') logTable.ajax.reload();
    });

    // View message details
    $(document).on('click', '.view-message', function() {
        const msg = $(this).data('message');
        const err = $(this).data('error');
        const status = $(this).data('status');
        $('#modalMessageBody').html(msg.replace(/\n/g, '<br>'));
        if (err && status === 'failed') {
            $('#modalError').removeClass('d-none').text('{{ trans("clients.whatsapp_error") ?? "الخطأ" }}: ' + err);
        } else {
            $('#modalError').addClass('d-none');
        }
        $('#resendBtn').data('id', $(this).data('id'));
        $('#messageModal').modal('show');
    });

    // Resend from modal
    $('#resendBtn').on('click', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        $.post('{{ url("admin/whatsapp/log") }}/' + id + '/resend', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            Swal.fire({ icon: res.success ? 'success' : 'error', text: res.message });
            logTable.ajax.reload();
            $('#messageModal').modal('hide');
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> {{ trans("clients.whatsapp_resend") ?? "إعادة إرسال" }}');
        });
    });

    // Resend from table
    $(document).on('click', '.resend-message', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        $.post('{{ url("admin/whatsapp/log") }}/' + id + '/resend', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            Swal.fire({ icon: res.success ? 'success' : 'error', text: res.message });
            logTable.ajax.reload();
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
        });
    });
});
</script>
@endsection
