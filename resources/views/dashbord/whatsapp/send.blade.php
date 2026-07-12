@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_send') ?? 'إرسال رسالة' }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_send') ?? 'إرسال رسالة';
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
        ['label' => trans('clients.whatsapp_send') ?? 'إرسال رسالة', 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-send me-2"></i> {{ trans('clients.whatsapp_new_broadcast') ?? 'إرسال رسالة WhatsApp' }}</h3>
        </div>
        <div class="card-body">
            <form id="broadcastForm">
                @csrf

                {{-- ═══════════ To: Field (Gmail-style) ═══════════ --}}
                <div class="mb-6">
                    <label class="form-label fw-bold mb-2">{{ trans('clients.whatsapp_to') ?? 'إلى' }}</label>
                    <div class="border rounded p-2 bg-white" id="toField" tabindex="0">
                        <div class="d-flex flex-wrap align-items-center gap-1" id="toChips"></div>
                        <div class="d-flex align-items-center gap-1 flex-grow-1" id="toInputRow">
                            <input type="text" class="form-control border-0 p-0 shadow-none flex-grow-1" id="toInput"
                                   placeholder="{{ trans('clients.whatsapp_search_clients') ?? 'ابحث باسم الزبون أو الرقم...' }}"
                                   autocomplete="off" style="min-width: 120px; outline: none;">
                            <button type="button" class="btn btn-sm btn-icon btn-light-primary ms-1" id="filterBtn"
                                    data-bs-toggle="modal" data-bs-target="#filterModal" title="{{ trans('clients.whatsapp_filter_select') ?? 'فلاتر ذكية' }}">
                                <i class="bi bi-funnel"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Typeahead Dropdown --}}
                    <div class="position-relative">
                        <div class="d-none position-absolute top-0 start-0 end-0 z-3 shadow-sm border rounded bg-white" id="typeaheadResults" style="max-height: 240px; overflow-y: auto;"></div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <span class="text-muted fs-7">
                            <i class="bi bi-info-circle"></i> {{ trans('clients.whatsapp_search_hint') ?? 'ابحث باسم، رقم، أو معرف — أو استخدم الفلاتر الذكية لإضافة مجموعة' }}
                        </span>
                        <span class="badge badge-light-primary fs-7" id="selectedCount">
                            {{ trans('clients.whatsapp_no_selected') ?? 'لم يتم اختيار أي زبون' }}
                        </span>
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                {{-- ═══════════ Template ═══════════ --}}
                <div class="mb-6">
                    <div class="row g-4 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</label>
                            <select class="form-select" name="template_type" id="templateSelect" required>
                                <option value="">{{ trans('clients.whatsapp_select_template') ?? 'اختر القالب...' }}</option>
                                @foreach($templates as $type => $template)
                                <option value="{{ $type }}">{{ app()->getLocale() == 'ar' ? $template['label'] : $template['label_en'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-none" id="customMessageGroup">
                            <label class="form-label fw-bold">{{ trans('clients.whatsapp_custom_message') ?? 'نص مخصص' }}</label>
                            <input type="text" class="form-control" name="custom_message" id="customMessage"
                                   placeholder="{{ trans('clients.whatsapp_custom_message_placeholder') ?? 'اكتب رسالتك...' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-light-primary w-100" id="previewBtn">
                                <i class="bi bi-eye"></i> {{ trans('clients.whatsapp_preview') ?? 'معاينة' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                {{-- ═══════════ Send Bar ═══════════ --}}
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold fs-6" id="finalCount">
                            {{ trans('clients.whatsapp_no_selected') ?? 'لم يتم اختيار أي زبون' }}
                        </span>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg" id="sendBtn" disabled>
                        <i class="bi bi-send fs-2"></i> {{ trans('clients.whatsapp_send_now') ?? 'إرسال الآن' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══ Smart Filters Modal ═══ --}}
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-funnel me-2"></i> {{ trans('clients.whatsapp_filter_select') ?? 'فلاتر ذكية' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_client_type') ?? 'نوع العميل' }}</label>
                        <select class="form-select" name="filter_client_type" id="filterClientType">
                            <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                            <option value="internet">{{ trans('clients.whatsapp_internet') ?? 'إنترنت' }}</option>
                            <option value="satellite">{{ trans('clients.whatsapp_satellite') ?? 'ساتلايت' }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_subscription') ?? 'الاشتراك' }}</label>
                        <select class="form-select" name="filter_subscription" id="filterSubscription">
                            <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                            @foreach(\App\Models\Admin\Subscription::all() as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_unpaid_bills') ?? 'فواتير unpaid' }}</label>
                        <select class="form-select" name="filter_unpaid" id="filterUnpaid">
                            <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                            <option value="1">≥ 1</option>
                            <option value="2">≥ 2</option>
                            <option value="3">≥ 3</option>
                            <option value="5">≥ 5</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold fs-7">{{ trans('clients.status') ?? 'الحالة' }}</label>
                        <select class="form-select" name="filter_status" id="filterStatus">
                            <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                            <option value="1">{{ trans('clients.active') ?? 'نشط' }}</option>
                            <option value="0">{{ trans('clients.inactive') ?? 'غير نشط' }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_last_payment') ?? 'آخر دفعة قبل' }}</label>
                        <input type="date" class="form-control" name="filter_last_payment" id="filterLastPayment">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('clients.cancel') ?? 'إلغاء' }}</button>
                <button type="button" class="btn btn-primary" id="applyFilters">
                    <i class="bi bi-funnel"></i> {{ trans('clients.whatsapp_apply_filter') ?? 'تطبيق وإضافة' }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Preview Modal ═══ --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('clients.whatsapp_preview') ?? 'معاينة الرسالة' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent" class="bg-light rounded p-4" style="white-space: pre-wrap; direction: rtl; text-align: right; font-family: 'Tajawal', sans-serif; font-size: 14px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('clients.close') ?? 'إغلاق' }}</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Result Modal ═══ --}}
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('clients.whatsapp_send_results') ?? 'نتيجة الإرسال' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-8">
                <div class="fs-1 fw-bold text-success" id="resultSent">0</div>
                <div class="text-muted mb-4">{{ trans('clients.whatsapp_sent_success') ?? 'تم الإرسال بنجاح' }}</div>
                <div class="fs-1 fw-bold text-danger" id="resultFailed">0</div>
                <div class="text-muted mb-4">{{ trans('clients.whatsapp_failed_send') ?? 'فشل الإرسال' }}</div>
                <div id="resultErrors" class="text-start d-none">
                    <hr>
                    <div class="fw-bold mb-2">{{ trans('clients.whatsapp_errors') ?? 'الأخطاء' }}</div>
                    <ul class="list-unstyled" id="errorList"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('clients.close') ?? 'إغلاق' }}</button>
                <a href="{{ route('admin.whatsapp.log') }}" class="btn btn-primary">{{ trans('clients.whatsapp_view_log') ?? 'عرض السجل' }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
// ── Template data for preview ──
const templates = @json(collect($templates)->mapWithKeys(fn($t, $k) => [$k => $t['body']]));

$(document).ready(function() {
    // ── State ──
    let selectedClients = new Map(); // id -> { name, phone }
    let searchTimeout;

    // ── Typeahead Search ──
    $('#toInput').on('input', function() {
        clearTimeout(searchTimeout);
        const q = $(this).val().trim();
        if (q.length < 2) {
            $('#typeaheadResults').addClass('d-none');
            return;
        }
        searchTimeout = setTimeout(() => {
            $.get('{{ route("admin.whatsapp.send.search_clients") }}', { q: q })
                .done(function(res) {
                    const results = res.results || [];
                    if (results.length === 0) {
                        $('#typeaheadResults').addClass('d-none');
                        return;
                    }
                    const html = results.map(r => {
                        const [name, phone] = r.text.split(' | ');
                        const isSelected = selectedClients.has(r.id);
                        return `
                            <div class="typeahead-item d-flex align-items-center justify-content-between px-3 py-2 border-bottom hover-bg-light"
                                 data-id="${r.id}" data-name="${name}" data-phone="${phone}"
                                 role="option" style="cursor: pointer;">
                                <div>
                                    <span class="fw-bold text-gray-800 fs-7">${name}</span>
                                    <span class="text-muted fs-7 me-2">${phone}</span>
                                </div>
                                ${isSelected
                                    ? '<span class="badge badge-light-success fs-8">مختار</span>'
                                    : '<span class="text-primary fs-7">+ إضافة</span>'}
                            </div>
                        `;
                    }).join('');
                    $('#typeaheadResults').html(html).removeClass('d-none');
                });
        }, 250);
    });

    // Typeahead item click
    $(document).on('click', '.typeahead-item', function() {
        const id = parseInt($(this).data('id'));
        const name = $(this).data('name');
        const phone = $(this).data('phone');
        addClient(id, name, phone);
        $('#toInput').val('').focus();
        $('#typeaheadResults').addClass('d-none');
    });

    // Close typeahead on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#toField, #typeaheadResults').length) {
            $('#typeaheadResults').addClass('d-none');
        }
    });

    // ── Remove chip via X ──
    $(document).on('click', '.chip-remove', function() {
        removeClient(parseInt($(this).data('id')));
    });

    // ── Template picker ──
    $('#templateSelect').on('change', function() {
        $('#customMessageGroup').toggleClass('d-none', $(this).val() !== 'custom');
    });

    // ── Preview ──
    $('#previewBtn').on('click', function() {
        const type = $('#templateSelect').val();
        if (!type) {
            Swal.fire({ icon: 'warning', text: '{{ trans("clients.whatsapp_select_template") ?? "يرجى اختيار قالب" }}' });
            return;
        }
        const body = templates[type] || '';
        let preview = body
            .replace(/{name}/g, '{{ trans("clients.whatsapp_sample_name") ?? "أحمد محمد" }}')
            .replace(/{total_amount}/g, '50.00')
            .replace(/{invoice_details_list}/g, '❌ 07 / 2026      $20.00\n❌ 06 / 2026      $20.00')
            .replace(/{message_body}/g, $('#customMessage').val() || '{{ trans("clients.whatsapp_sample_custom") ?? "نص الرسالة" }}')
            .replace(/{amount}/g, '15.00')
            .replace(/{month}/g, '07').replace(/{year}/g, '2026')
            .replace(/{collector}/g, '{{ trans("clients.whatsapp_sample_collector") ?? "أحمد" }}')
            .replace(/{datetime}/g, new Date().toLocaleString('ar-SA'))
            .replace(/{balance_status}/g, '{{ trans("clients.whatsapp_sample_balance") ?? "الرصيد الحالي: $0.00" }}')
            .replace(/{due_date}/g, new Date(Date.now() + 3*86400000).toLocaleDateString('ar-SA'))
            .replace(/{support_phone}/g, '96170781562');
        $('#previewContent').html(preview.replace(/\n/g, '<br>'));
        $('#previewModal').modal('show');
    });

    // ── Smart Filters — Apply ──
    $('#applyFilters').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');

        $.post('{{ route("admin.whatsapp.send.broadcast") }}', {
            _token: '{{ csrf_token() }}',
            preview: true,
            client_type: $('#filterClientType').val(),
            subscription: $('#filterSubscription').val(),
            unpaid: $('#filterUnpaid').val(),
            status: $('#filterStatus').val(),
            last_payment: $('#filterLastPayment').val(),
        }).done(function(res) {
            const clients = res.clients || [];
            if (clients.length === 0) {
                Swal.fire({ icon: 'info', text: '{{ trans("clients.whatsapp_no_matching") ?? "لا يوجد زبائن متطابقين" }}', timer: 2000, showConfirmButton: false });
            } else {
                clients.forEach(c => addClient(c.id, c.name, c.phone));
                Swal.fire({ icon: 'success', text: clients.length + ' {{ trans("clients.whatsapp_filter_added") ?? "تمت الإضافة" }}', timer: 1500, showConfirmButton: false });
                $('#filterModal').modal('hide');
            }
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-funnel"></i> {{ trans("clients.whatsapp_apply_filter") ?? "تطبيق وإضافة" }}');
        });
    });

    // ── Selection Management ──
    function addClient(id, name, phone) {
        if (selectedClients.has(id)) return;
        selectedClients.set(id, { name, phone });
        renderChips();
    }

    function removeClient(id) {
        selectedClients.delete(id);
        renderChips();
        // Reset typeahead result badge if visible
        $(`.typeahead-item[data-id="${id}"] .badge`).remove();
        $(`.typeahead-item[data-id="${id}"] span.text-primary`).remove();
        $(`.typeahead-item[data-id="${id}"] div:last-child`).append('<span class="text-primary fs-7">+ إضافة</span>');
    }

    function renderChips() {
        const container = $('#toChips');
        container.empty();

        if (selectedClients.size === 0) {
            container.html('<span class="text-muted fs-7 px-1">{{ trans("clients.whatsapp_no_selected") ?? "لم يتم اختيار أي زبون" }}</span>');
            $('#sendBtn').prop('disabled', true);
            $('#selectedCount').text('{{ trans("clients.whatsapp_no_selected") ?? "لم يتم اختيار أي زبون" }}');
            $('#finalCount').text('{{ trans("clients.whatsapp_no_selected") ?? "لم يتم اختيار أي زبون" }}');
            return;
        }

        $('#sendBtn').prop('disabled', false);
        const size = selectedClients.size;
        $('#selectedCount').text(size + ' {{ trans("clients.whatsapp_selected") ?? "مختار" }}');
        $('#finalCount').text('{{ trans("clients.whatsapp_sending_to") ?? "سيتم الإرسال إلى" }} ' + size + ' {{ trans("clients.whatsapp_recipient") ?? "مستلم" }}');

        selectedClients.forEach((client, id) => {
            container.append(`
                <span class="chip d-inline-flex align-items-center bg-primary-light text-primary-emphasis rounded px-2 py-1 fs-7 fw-semibold">
                    ${client.name}
                    <button type="button" class="btn btn-sm btn-icon p-0 ms-1 chip-remove" data-id="${id}" style="line-height: 1;">
                        <i class="bi bi-x-lg" style="font-size: 10px;"></i>
                    </button>
                </span>
            `);
        });

        // Update typeahead badges for visible results
        $('.typeahead-item').each(function() {
            const id = parseInt($(this).data('id'));
            const action = $(this).find('div:last-child');
            if (selectedClients.has(id)) {
                action.html('<span class="badge badge-light-success fs-8">مختار</span>');
            } else {
                action.html('<span class="text-primary fs-7">+ إضافة</span>');
            }
        });
    }

    // ── Submit Send ──
    $('#broadcastForm').on('submit', function(e) {
        e.preventDefault();

        if (selectedClients.size === 0) {
            Swal.fire({ icon: 'warning', text: '{{ trans("clients.whatsapp_select_recipients") ?? "يرجى اختيار مستلمين على الأقل" }}' });
            return;
        }

        if (!$('#templateSelect').val()) {
            Swal.fire({ icon: 'warning', text: '{{ trans("clients.whatsapp_select_template") ?? "يرجى اختيار قالب" }}' });
            return;
        }

        const btn = $('#sendBtn');
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i> {{ trans("clients.whatsapp_sending") ?? "جارٍ الإرسال..." }}');

        const clientIds = Array.from(selectedClients.keys());

        $.post('{{ route("admin.whatsapp.send.broadcast") }}', {
            _token: '{{ csrf_token() }}',
            template_type: $('#templateSelect').val(),
            custom_message: $('#customMessage').val(),
            client_ids: clientIds
        }).done(function(res) {
            $('#resultSent').text(res.sent || 0);
            $('#resultFailed').text(res.failed || 0);
            if (res.errors && res.errors.length > 0) {
                $('#resultErrors').removeClass('d-none');
                $('#errorList').empty();
                res.errors.forEach(function(e) {
                    $('#errorList').append('<li><i class="bi bi-x-circle text-danger me-2"></i>' + e + '</li>');
                });
            } else {
                $('#resultErrors').addClass('d-none');
            }
            $('#resultModal').modal('show');
            selectedClients.clear();
            renderChips();
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ أثناء الإرسال" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-send fs-2"></i> {{ trans("clients.whatsapp_send_now") ?? "إرسال الآن" }}');
        });
    });

    // ── Focus toField when clicking on it ──
    $('#toField').on('click', function(e) {
        if (!$(e.target).closest('.chip-remove').length) {
            $('#toInput').focus();
        }
    });
});
</script>

<style>
/* Gmail-style To: field */
#toField {
    min-height: 48px;
    cursor: text;
    transition: border-color 0.15s ease;
}
#toField:focus-within {
    border-color: var(--bs-primary) !important;
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.1);
}
.chip {
    line-height: 1.4;
}
.chip:hover {
    background-color: var(--bs-primary-light) !important;
}
.chip-remove {
    opacity: 0.6;
}
.chip-remove:hover {
    opacity: 1;
}
.typeahead-item:hover {
    background-color: #f5f8fa;
}
.typeahead-item:last-child {
    border-bottom: none !important;
}
.spinner {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endsection
