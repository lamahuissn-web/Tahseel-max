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

                {{-- Step 1: Template --}}
                <div class="mb-6">
                    <label class="form-label fw-bold">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</label>
                    <select class="form-select" name="template_type" id="templateSelect" required>
                        <option value="">{{ trans('clients.whatsapp_select_template') ?? 'اختر القالب...' }}</option>
                        @foreach($templates as $type => $template)
                        <option value="{{ $type }}">{{ app()->getLocale() == 'ar' ? $template['label'] : $template['label_en'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Custom message body (shown when "custom" is selected) --}}
                <div class="mb-6 d-none" id="customMessageGroup">
                    <label class="form-label fw-bold">{{ trans('clients.whatsapp_custom_message') ?? 'نص الرسالة المخصصة' }}</label>
                    <textarea class="form-control" name="custom_message" id="customMessage" rows="5"
                              placeholder="{{ trans('clients.whatsapp_custom_message_placeholder') ?? 'اكتب رسالتك هنا...' }}"></textarea>
                </div>

                {{-- Step 2: Select Recipients --}}
                <div class="mb-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <label class="form-label fw-bold mb-0">{{ trans('clients.whatsapp_recipients') ?? 'المستلمون' }}</label>
                        <span class="text-muted fs-7" id="selectedCount">{{ trans('clients.whatsapp_no_selected') ?? 'لم يتم اختيار أي زبون' }}</span>
                    </div>

                    {{-- Tabs: Manual / Filter --}}
                    <ul class="nav nav-tabs nav-line-tabs mb-4" id="recipientTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="manualTab" data-bs-toggle="tab" data-bs-target="#manualPanel" type="button">
                                <i class="bi bi-search"></i> {{ trans('clients.whatsapp_manual_select') ?? 'اختيار يدوي' }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="filterTab" data-bs-toggle="tab" data-bs-target="#filterPanel" type="button">
                                <i class="bi bi-funnel"></i> {{ trans('clients.whatsapp_filter_select') ?? 'تصفية ذكية' }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- Manual Selection --}}
                        <div class="tab-pane fade show active" id="manualPanel">
                            <div class="mb-3">
                                <select class="form-select" id="clientSelect" multiple="multiple" style="width: 100%;"></select>
                            </div>
                            <div class="text-muted fs-7">
                                <i class="bi bi-info-circle"></i> {{ trans('clients.whatsapp_search_hint') ?? 'ابحث باسم الزبون أو الرقم أو المعرف' }}
                            </div>
                        </div>

                        {{-- Smart Filter --}}
                        <div class="tab-pane fade" id="filterPanel">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fs-7">{{ trans('clients.whatsapp_unpaid_bills') ?? 'فواتير غير مدفوعة' }}</label>
                                    <select class="form-select form-select-sm" name="filter_unpaid" id="filterUnpaid">
                                        <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                                        <option value="1">≥ 1</option>
                                        <option value="2">≥ 2</option>
                                        <option value="3">≥ 3</option>
                                        <option value="5">≥ 5</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-7">{{ trans('clients.whatsapp_address') ?? 'العنوان' }}</label>
                                    <input type="text" class="form-control form-control-sm" name="filter_address" id="filterAddress"
                                           placeholder="{{ trans('clients.whatsapp_address_placeholder') ?? 'ابحث بالعنوان...' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-7">{{ trans('clients.whatsapp_subscription') ?? 'الاشتراك' }}</label>
                                    <select class="form-select form-select-sm" name="filter_subscription" id="filterSubscription">
                                        <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                                        @foreach(\App\Models\Admin\Subscription::all() as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7">{{ trans('clients.whatsapp_last_payment') ?? 'آخر دفعة قبل' }}</label>
                                    <input type="date" class="form-control form-control-sm" name="filter_last_payment" id="filterLastPayment">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" class="btn btn-sm btn-primary" id="applyFilter">
                                        <i class="bi bi-search"></i> {{ trans('clients.whatsapp_apply_filter') ?? 'تطبيق التصفية' }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light ms-2" id="clearFilter">
                                        <i class="bi bi-x-lg"></i> {{ trans('clients.whatsapp_clear') ?? 'مسح' }}
                                    </button>
                                </div>
                            </div>
                            <div id="filterResults" class="mt-3 d-none">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="bi bi-people fs-2 me-3"></i>
                                    <div>
                                        <strong id="filterCount">0</strong> {{ trans('clients.whatsapp_matching_clients') ?? 'زبون متطابق مع الفلتر' }}
                                    </div>
                                    <div class="ms-auto">
                                        <button type="button" class="btn btn-sm btn-success" id="addFilterResults">
                                            <i class="bi bi-plus-lg"></i> {{ trans('clients.whatsapp_add_selection') ?? 'إضافة للاختيار' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Selected Clients Chips --}}
                    <div id="selectedClients" class="d-flex flex-wrap gap-2 mt-4 p-3 bg-light rounded">
                        <span class="text-muted fs-7">{{ trans('clients.whatsapp_no_selected') ?? 'لم يتم اختيار أي زبون' }}</span>
                    </div>
                </div>

                {{-- Step 3: Send --}}
                <div class="separator separator-dashed my-6"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold fs-6" id="finalCount">{{ trans('clients.whatsapp_no_selected') ?? 'لم يتم اختيار أي زبون' }}</span>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg" id="sendBtn" disabled>
                        <i class="bi bi-send fs-2"></i> {{ trans('clients.whatsapp_send_now') ?? 'إرسال الآن' }}
                    </button>
                </div>
            </form>
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
$(document).ready(function() {
    let selectedClients = new Map(); // id -> { name, phone }

    // Initialize Select2 for client search
    $('#clientSelect').select2({
        ajax: {
            url: '{{ route("admin.whatsapp.send.search_clients") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return { results: data.results };
            },
            cache: true
        },
        minimumInputLength: 2,
        placeholder: '{{ trans("clients.whatsapp_search_clients") ?? "ابحث عن زبون..." }}',
        language: {
            inputTooShort: function() {
                return '{{ trans("clients.whatsapp_min_chars") ?? "أدخل حرفين على الأقل" }}';
            },
            noResults: function() {
                return '{{ trans("clients.whatsapp_no_results") ?? "لا توجد نتائج" }}';
            }
        }
    });

    // Add selected client from Select2
    $('#clientSelect').on('select2:select', function(e) {
        const data = e.params.data;
        addClient(data.id, data.text.split('|')[0].trim(), data.text.split('|')[1]?.trim() || '');
        $('#clientSelect').val(null).trigger('change');
    });

    // Show custom message field when "custom" template selected
    $('#templateSelect').on('change', function() {
        $('#customMessageGroup').toggleClass('d-none', $(this).val() !== 'custom');
    });

    // Add clients from filter results
    $('#addFilterResults').on('click', function() {
        const clients = $(this).data('clients') || [];
        clients.forEach(c => addClient(c.id, c.name, c.phone));
        $('#filterResults').addClass('d-none');
    });

    // Apply filter
    $('#applyFilter').on('click', function() {
        const data = {
            _token: '{{ csrf_token() }}',
            unpaid: $('#filterUnpaid').val(),
            address: $('#filterAddress').val(),
            subscription: $('#filterSubscription').val(),
            last_payment: $('#filterLastPayment').val(),
        };

        $.post('{{ route("admin.whatsapp.send.broadcast") }}', {
            _token: '{{ csrf_token() }}',
            preview: true,
            ...data
        }).done(function(res) {
            if (res.clients && res.clients.length > 0) {
                $('#filterCount').text(res.clients.length);
                $('#addFilterResults').data('clients', res.clients);
                $('#filterResults').removeClass('d-none');
            } else {
                Swal.fire({ icon: 'info', text: '{{ trans("clients.whatsapp_no_matching") ?? "لا يوجد زبائن متطابقين" }}' });
            }
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        });
    });

    // Clear filter
    $('#clearFilter').on('click', function() {
        $('#filterUnpaid').val('');
        $('#filterAddress').val('');
        $('#filterSubscription').val('');
        $('#filterLastPayment').val('');
        $('#filterResults').addClass('d-none');
    });

    // Add a client to selection
    function addClient(id, name, phone) {
        if (selectedClients.has(id)) return;
        selectedClients.set(id, { name, phone });
        renderSelected();
    }

    // Remove a client
    function removeClient(id) {
        selectedClients.delete(id);
        renderSelected();
    }

    // Render selected clients chips
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
        $('#selectedCount').text(selectedClients.size + ' {{ trans("clients.whatsapp_selected") ?? "مختار" }}');
        $('#finalCount').text('{{ trans("clients.whatsapp_sending_to") ?? "سيتم الإرسال إلى" }} ' + selectedClients.size + ' {{ trans("clients.whatsapp_recipient") ?? "مستلم" }}');

        selectedClients.forEach((client, id) => {
            container.append(`
                <div class="d-flex align-items-center bg-white border rounded px-3 py-2">
                    <span class="fw-bold me-2">${client.name}</span>
                    <span class="text-muted fs-7 ms-2">${client.phone}</span>
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger ms-2 remove-client" data-id="${id}">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `);
        });

        $('.remove-client').on('click', function() {
            removeClient($(this).data('id'));
        });
    }

    // Submit broadcast
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
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ أثناء الإرسال" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-send fs-2"></i> {{ trans("clients.whatsapp_send_now") ?? "إرسال الآن" }}');
        });
    });
});
</script>
@endsection
