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
            <h3 class="card-title">{{ trans('clients.whatsapp_new_broadcast') ?? 'إرسال رسالة جديدة' }}</h3>
        </div>
        <div class="card-body">
            <form id="broadcastForm">
                @csrf

                {{-- ═══════════ Step 1: Template ═══════════ --}}
                <div class="mb-8">
                    <div class="row g-4 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</label>
                            <select class="form-select" name="template_type" id="templateSelect" required>
                                <option value="">{{ trans('clients.whatsapp_select_template') ?? 'اختر القالب...' }}</option>
                                @foreach($templates as $type => $template)
                                <option value="{{ $type }}">{{ app()->getLocale() == 'ar' ? $template['label'] : $template['label_en'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 d-none" id="customMessageGroup">
                            <label class="form-label fw-bold">{{ trans('clients.whatsapp_custom_message') ?? 'نص الرسالة المخصصة' }}</label>
                            <textarea class="form-control" name="custom_message" id="customMessage" rows="1"
                                      placeholder="{{ trans('clients.whatsapp_custom_message_placeholder') ?? 'اكتب رسالتك هنا...' }}"></textarea>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-light-primary w-100" id="previewBtn">
                                <i class="bi bi-eye"></i> {{ trans('clients.whatsapp_preview') ?? 'معاينة' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                {{-- ═══════════ Step 2: Search + Filters + Results ═══════════ --}}
                <div class="mb-6">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <label class="form-label fw-bold mb-0">{{ trans('clients.whatsapp_recipients') ?? 'المستلمون' }}</label>
                        <span class="badge badge-light-primary fs-7" id="selectedCount">
                            {{ trans('clients.whatsapp_no_selected') ?? 'لم يتم اختيار أي زبون' }}
                        </span>
                    </div>

                    {{-- Search Bar --}}
                    <div class="mb-4">
                        <div class="input-group input-group-solid">
                            <span class="input-group-text"><i class="bi bi-search fs-3"></i></span>
                            <input type="text" class="form-control form-control-solid" id="searchInput"
                                   placeholder="{{ trans('clients.whatsapp_search_clients') ?? 'ابحث باسم الزبون أو الرقم...' }}"
                                   autocomplete="off">
                            <button class="btn btn-icon btn-light" type="button" id="clearSearch">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="text-muted fs-7 mt-1">
                            <i class="bi bi-info-circle"></i> {{ trans('clients.whatsapp_search_hint') ?? 'يبحث في الاسم، الرقم، والمعرف — حتى رموز مثل VIP, *, _' }}
                        </div>
                    </div>

                    {{-- Smart Filters --}}
                    <div class="mb-4">
                        <button class="btn btn-sm btn-light-primary" type="button" id="toggleFilters">
                            <i class="bi bi-funnel"></i> {{ trans('clients.whatsapp_filter_select') ?? 'فلاتر ذكية' }}
                            <i class="bi bi-chevron-down ms-1" id="filterArrow"></i>
                        </button>
                    </div>

                    <div class="d-none" id="filterPanel">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Client Type --}}
                                    <div class="col-md-3">
                                        <label class="form-label fs-7 fw-bold">{{ trans('clients.whatsapp_client_type') ?? 'نوع العميل' }}</label>
                                        <select class="form-select form-select-sm" name="filter_client_type" id="filterClientType">
                                            <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                                            <option value="internet">{{ trans('clients.whatsapp_internet') ?? 'إنترنت' }}</option>
                                            <option value="satellite">{{ trans('clients.whatsapp_satellite') ?? 'ساتلايت' }}</option>
                                        </select>
                                    </div>

                                    {{-- Subscription --}}
                                    <div class="col-md-3">
                                        <label class="form-label fs-7 fw-bold">{{ trans('clients.whatsapp_subscription') ?? 'الاشتراك' }}</label>
                                        <select class="form-select form-select-sm" name="filter_subscription" id="filterSubscription">
                                            <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                                            @foreach(\App\Models\Admin\Subscription::all() as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Unpaid Bills --}}
                                    <div class="col-md-3">
                                        <label class="form-label fs-7 fw-bold">{{ trans('clients.whatsapp_unpaid_bills') ?? 'فواتير غير مدفوعة' }}</label>
                                        <select class="form-select form-select-sm" name="filter_unpaid" id="filterUnpaid">
                                            <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                                            <option value="1">≥ 1</option>
                                            <option value="2">≥ 2</option>
                                            <option value="3">≥ 3</option>
                                            <option value="5">≥ 5</option>
                                        </select>
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-md-3">
                                        <label class="form-label fs-7 fw-bold">{{ trans('clients.status') ?? 'الحالة' }}</label>
                                        <select class="form-select form-select-sm" name="filter_status" id="filterStatus">
                                            <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                                            <option value="1">{{ trans('clients.active') ?? 'نشط' }}</option>
                                            <option value="0">{{ trans('clients.inactive') ?? 'غير نشط' }}</option>
                                        </select>
                                    </div>

                                    {{-- Last Payment --}}
                                    <div class="col-md-6">
                                        <label class="form-label fs-7 fw-bold">{{ trans('clients.whatsapp_last_payment') ?? 'آخر دفعة قبل' }}</label>
                                        <input type="date" class="form-control form-control-sm" name="filter_last_payment" id="filterLastPayment">
                                    </div>

                                    {{-- Actions --}}
                                    <div class="col-md-6 d-flex align-items-end gap-2">
                                        <button type="button" class="btn btn-sm btn-primary" id="applyFilter">
                                            <i class="bi bi-funnel"></i> {{ trans('clients.whatsapp_apply_filter') ?? 'تطبيق' }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light" id="clearFilter">
                                            <i class="bi bi-x-lg"></i> {{ trans('clients.whatsapp_clear') ?? 'مسح' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Results Table --}}
                    <div class="d-none" id="resultsSection">
                        <div class="table-responsive mb-4">
                            <table class="table table-row-bordered table-align-middle">
                                <thead>
                                    <tr class="fw-bold fs-7 text-gray-600">
                                        <th class="w-40px">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th>{{ trans('clients.name') ?? 'الاسم' }}</th>
                                        <th>{{ trans('clients.phone') ?? 'الرقم' }}</th>
                                        <th>{{ trans('clients.whatsapp_unpaid_bills') ?? 'فواتير' }}</th>
                                        <th>{{ trans('clients.status') ?? 'الحالة' }}</th>
                                    </tr>
                                </thead>
                                <tbody id="resultsBody">
                                    {{-- Populated by JS --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted fs-7" id="resultsInfo"></span>
                            <div class="d-flex gap-2" id="paginationControls"></div>
                        </div>
                    </div>

                    {{-- Selected Clients Chips --}}
                    <div id="selectedClients" class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                        <span class="text-muted fs-7">{{ trans('clients.whatsapp_no_selected') ?? 'لم يتم اختيار أي زبون' }}</span>
                    </div>
                </div>

                {{-- ═══════════ Step 3: Send ═══════════ --}}
                <div class="separator separator-dashed my-6"></div>
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

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('clients.whatsapp_preview') ?? 'معاينة الرسالة' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent" class="bg-light rounded p-4" style="white-space: pre-wrap; direction: rtl; text-align: right; font-family: 'Tajawal', sans-serif;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('clients.close') ?? 'إغلاق' }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Result Modal --}}
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
    let selectedClients = new Map();
    let searchResults = [];
    let currentPage = 1;
    const perPage = 20;

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
        const body = templates[type] || '{{ trans("clients.whatsapp_preview_placeholder") ?? "لا توجد معاينة متاحة" }}';
        let preview = body
            .replace(/{name}/g, '{{ trans("clients.whatsapp_sample_name") ?? "أحمد محمد" }}')
            .replace(/{total_amount}/g, '50.00')
            .replace(/{invoice_details_list}/g, '❌ 07 / 2026      $20.00\n❌ 06 / 2026      $20.00')
            .replace(/{message_body}/g, $('#customMessage').val() || '{{ trans("clients.whatsapp_sample_custom") ?? "نص الرسالة" }}')
            .replace(/{amount}/g, '15.00')
            .replace(/{month}/g, '07')
            .replace(/{year}/g, '2026')
            .replace(/{collector}/g, '{{ trans("clients.whatsapp_sample_collector") ?? "أحمد" }}')
            .replace(/{datetime}/g, new Date().toLocaleString('ar-SA'))
            .replace(/{balance_status}/g, '{{ trans("clients.whatsapp_sample_balance") ?? "الرصيد الحالي: $0.00" }}')
            .replace(/{due_date}/g, new Date(Date.now() + 3*86400000).toLocaleDateString('ar-SA'))
            .replace(/{support_phone}/g, '96170781562');
        $('#previewContent').html(preview.replace(/\n/g, '<br>'));
        $('#previewModal').modal('show');
    });

    // ── Toggle Filters Panel ──
    $('#toggleFilters').on('click', function() {
        $('#filterPanel').toggleClass('d-none');
        $('#filterArrow').toggleClass('bi-chevron-down bi-chevron-up');
    });

    // ── Search (debounced) ──
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const q = $(this).val().trim();
        if (q.length < 2 && q.length > 0) return;
        searchTimeout = setTimeout(() => doSearch(q), 300);
    });

    $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        clearResults();
    });

    // ── Apply Filter ──
    $('#applyFilter').on('click', function() {
        doSearch($('#searchInput').val().trim());
    });

    $('#clearFilter').on('click', function() {
        $('#filterClientType').val('');
        $('#filterSubscription').val('');
        $('#filterUnpaid').val('');
        $('#filterStatus').val('');
        $('#filterLastPayment').val('');
        doSearch($('#searchInput').val().trim());
    });

    // ── Select All ──
    $('#selectAll').on('change', function() {
        const checked = $(this).is(':checked');
        $('.client-checkbox').prop('checked', checked);
        searchResults.forEach(r => {
            if (checked) addClient(r.id, r.name, r.phone);
            else removeClient(r.id);
        });
    });

    // ── Pagination ──
    $(document).on('click', '.page-btn', function() {
        currentPage = parseInt($(this).data('page'));
        renderTable();
    });

    // ── Core: Search ──
    function doSearch(q) {
        const data = {
            _token: '{{ csrf_token() }}',
            preview: true,
            q: q,
            client_type: $('#filterClientType').val(),
            subscription: $('#filterSubscription').val(),
            unpaid: $('#filterUnpaid').val(),
            status: $('#filterStatus').val(),
            last_payment: $('#filterLastPayment').val(),
        };

        $.post('{{ route("admin.whatsapp.send.broadcast") }}', data)
            .done(function(res) {
                searchResults = res.clients || [];
                currentPage = 1;
                if (searchResults.length > 0) {
                    renderTable();
                    $('#resultsSection').removeClass('d-none');
                } else {
                    clearResults();
                    Swal.fire({ icon: 'info', text: '{{ trans("clients.whatsapp_no_matching") ?? "لا يوجد زبائن متطابقين" }}', timer: 2000, showConfirmButton: false });
                }
            })
            .fail(function() {
                Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
            });
    }

    // ── Core: Render Table ──
    function renderTable() {
        const start = (currentPage - 1) * perPage;
        const pageItems = searchResults.slice(start, start + perPage);
        const totalPages = Math.ceil(searchResults.length / perPage);
        const tbody = $('#resultsBody');
        tbody.empty();

        pageItems.forEach(function(c) {
            const checked = selectedClients.has(c.id) ? 'checked' : '';
            const unpaidBadge = c.unpaid_count > 0
                ? `<span class="badge badge-danger fs-8">${c.unpaid_count} unpaid</span>`
                : `<span class="text-muted fs-8">0</span>`;
            tbody.append(`
                <tr>
                    <td>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input client-checkbox" type="checkbox"
                                   data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone}" ${checked}>
                        </div>
                    </td>
                    <td><span class="fw-bold text-gray-800">${c.name}</span></td>
                    <td><span class="text-muted fs-7">${c.phone}</span></td>
                    <td>${unpaidBadge}</td>
                    <td>
                        ${c.is_active == '1'
                            ? '<span class="badge badge-light-success fs-8">{{ trans("clients.active") ?? "نشط" }}</span>'
                            : '<span class="badge badge-light-secondary fs-8">{{ trans("clients.inactive") ?? "غير نشط" }}</span>'}
                    </td>
                </tr>
            `);
        });

        // Bind checkbox handlers
        $('.client-checkbox').on('change', function() {
            const id = parseInt($(this).data('id'));
            const name = $(this).data('name');
            const phone = $(this).data('phone');
            if ($(this).is(':checked')) addClient(id, name, phone);
            else removeClient(id);
            // Update select-all
            updateSelectAll();
        });

        // Update results info
        const totalMatching = searchResults.length;
        const selectedCount = selectedClients.size;
        $('#resultsInfo').text(`{{ trans("clients.whatsapp_total_results") ?? "إجمالي" }}: ${totalMatching} | {{ trans("clients.whatsapp_selected") ?? "مختار" }}: ${selectedCount}`);

        // Pagination
        const pager = $('#paginationControls');
        pager.empty();
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                pager.append(`<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-light'} page-btn" data-page="${i}">${i}</button>`);
            }
        }
    }

    // ── Selection Management ──
    function addClient(id, name, phone) {
        if (selectedClients.has(id)) return;
        selectedClients.set(id, { name, phone });
        renderSelected();
        // Check the corresponding row if visible
        $(`.client-checkbox[data-id="${id}"]`).prop('checked', true);
    }

    function removeClient(id) {
        selectedClients.delete(id);
        renderSelected();
        // Uncheck the corresponding row if visible
        $(`.client-checkbox[data-id="${id}"]`).prop('checked', false);
        updateSelectAll();
    }

    function updateSelectAll() {
        const total = searchResults.length;
        const checked = $('.client-checkbox:checked').length;
        $('#selectAll').prop('checked', total > 0 && checked === total);
    }

    function renderSelected() {
        const container = $('#selectedClients');
        container.empty();

        if (selectedClients.size === 0) {
            container.append('<span class="text-muted fs-7">{{ trans("clients.whatsapp_no_selected") ?? "لم يتم اختيار أي زبون" }}</span>');
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
                <div class="d-flex align-items-center bg-white border rounded px-3 py-2">
                    <span class="fw-bold me-2 fs-7">${client.name}</span>
                    <span class="text-muted fs-7 ms-2">${client.phone}</span>
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger ms-2 remove-chip" data-id="${id}">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `);
        });

        $('.remove-chip').on('click', function() {
            removeClient(parseInt($(this).data('id')));
        });

        // Update results info if visible
        if ($('#resultsSection').is(':visible')) {
            $('#resultsInfo').text(`{{ trans("clients.whatsapp_total_results") ?? "إجمالي" }}: ${searchResults.length} | {{ trans("clients.whatsapp_selected") ?? "مختار" }}: ${selectedClients.size}`);
        }
    }

    function clearResults() {
        $('#resultsSection').addClass('d-none');
        $('#resultsBody').empty();
        searchResults = [];
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
            renderSelected();
            clearResults();
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ أثناء الإرسال" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-send fs-2"></i> {{ trans("clients.whatsapp_send_now") ?? "إرسال الآن" }}');
        });
    });

    // ── Auto-search on load: show all clients with phone ──
    doSearch('');
});
</script>
@endsection
