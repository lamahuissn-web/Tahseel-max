@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_templates') ?? 'قوالب الرسائل' }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_templates') ?? 'قوالب الرسائل';
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
        ['label' => trans('clients.whatsapp_templates') ?? 'قوالب الرسائل', 'link' => ''],
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
            <h3 class="card-title">{{ trans('clients.whatsapp_message_templates') ?? 'قوالب الرسائل' }}</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" id="saveAllTemplates">
                    <i class="bi bi-check-lg"></i> {{ trans('clients.whatsapp_save_all') ?? 'حفظ الكل' }}
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- Template Tabs --}}
            <ul class="nav nav-tabs nav-line-tabs mb-5" id="templateTabs" role="tablist">
                @foreach($templates as $type => $template)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                            id="tab-{{ $type }}"
                            data-bs-toggle="tab"
                            data-bs-target="#panel-{{ $type }}"
                            type="button"
                            role="tab">
                        {{ app()->getLocale() == 'ar' ? $template['label'] : $template['label_en'] }}
                    </button>
                </li>
                @endforeach
            </ul>

            {{-- Template Panels --}}
            <div class="tab-content" id="templateTabContent">
                @foreach($templates as $type => $template)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                     id="panel-{{ $type }}"
                     role="tabpanel">

                    <div class="row g-6">
                        {{-- Editor --}}
                        <div class="col-lg-7">
                            <div class="mb-4">
                                <label class="form-label fw-bold">{{ trans('clients.whatsapp_template_body') ?? 'نص الرسالة' }}</label>
                                <textarea class="form-control template-body {{ app()->getLocale() == 'ar' ? 'text-end' : '' }}"
                                          id="body-{{ $type }}"
                                          data-type="{{ $type }}"
                                          rows="12"
                                          style="direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}; font-family: monospace;"
                                >{{ $template['body'] }}</textarea>
                            </div>

                            {{-- Variable Buttons --}}
                            <div class="mb-4">
                                <label class="form-label text-muted fs-7">{{ trans('clients.whatsapp_variables') ?? 'المتغيرات المتاحة' }}</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($template['variables'] as $variable)
                                    <button type="button"
                                            class="btn btn-sm btn-light-primary insert-variable"
                                            data-type="{{ $type }}"
                                            data-variable="{{ $variable }}">
                                        <code>{{ $variable }}</code>
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex gap-3">
                                <button type="button"
                                        class="btn btn-success save-template"
                                        data-type="{{ $type }}">
                                    <i class="bi bi-check-lg"></i> {{ trans('clients.whatsapp_save') ?? 'حفظ' }}
                                </button>
                                <button type="button"
                                        class="btn btn-light-primary test-template"
                                        data-type="{{ $type }}">
                                    <i class="bi bi-send"></i> {{ trans('clients.whatsapp_test_send') ?? 'إرسال تجريبي' }}
                                </button>
                                <button type="button"
                                        class="btn btn-light preview-template"
                                        data-type="{{ $type }}">
                                    <i class="bi bi-eye"></i> {{ trans('clients.whatsapp_preview') ?? 'معاينة' }}
                                </button>
                            </div>
                        </div>

                        {{-- Preview --}}
                        <div class="col-lg-5">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h4 class="card-title fs-6">{{ trans('clients.whatsapp_preview') ?? 'معاينة' }}</h4>
                                    <div class="card-toolbar">
                                        <button type="button" class="btn btn-sm btn-icon btn-light copy-preview" data-type="{{ $type }}">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="preview-{{ $type }}"
                                         class="template-preview bg-white border rounded p-4"
                                         style="white-space: pre-wrap; direction: rtl; text-align: right; font-size: 14px; min-height: 200px; font-family: 'Tajawal', sans-serif;">
                                        {{-- Preview will be rendered here via JS --}}
                                        <span class="text-muted">{{ trans('clients.whatsapp_preview_placeholder') ?? 'انقر على معاينة لعرض الرسالة' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Test Send Modal --}}
<div class="modal fade" id="testSendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('clients.whatsapp_test_send') ?? 'إرسال تجريبي' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="testSendForm">
                @csrf
                <input type="hidden" name="type" id="testType">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ trans('clients.whatsapp_test_phone') ?? 'رقم الهاتف' }}</label>
                        <input type="text" class="form-control" name="phone" id="testPhone"
                               placeholder="961XXXXXXXXX" value="96170781562">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('clients.cancel') ?? 'إلغاء' }}</button>
                    <button type="submit" class="btn btn-primary" id="sendTestBtn">
                        <i class="bi bi-send"></i> {{ trans('clients.whatsapp_send') ?? 'إرسال' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Insert variable at cursor position
    $('.insert-variable').on('click', function() {
        const type = $(this).data('type');
        const variable = $(this).data('variable');
        const textarea = document.getElementById('body-' + type);
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + variable + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + variable.length;
        // Auto-trigger preview
        updatePreview(type);
    });

    // Save single template
    $('.save-template').on('click', function() {
        const type = $(this).data('type');
        const body = $('#body-' + type).val();
        saveTemplate(type, body);
    });

    // Save all templates
    $('#saveAllTemplates').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        const promises = [];
        $('.template-body').each(function() {
            const type = $(this).data('type');
            const body = $(this).val();
            promises.push($.post('{{ route("admin.whatsapp.templates.save") }}', {
                _token: '{{ csrf_token() }}',
                type: type,
                body: body
            }));
        });
        Promise.all(promises).then(() => {
            Swal.fire({ icon: 'success', text: '{{ trans("clients.whatsapp_settings_saved") ?? "تم حفظ الكل" }}', timer: 2000, showConfirmButton: false });
        }).catch(() => {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> {{ trans("clients.whatsapp_save_all") ?? "حفظ الكل" }}');
        });
    });

    // Preview
    $('.preview-template').on('click', function() {
        const type = $(this).data('type');
        updatePreview(type);
    });

    // Test send - open modal
    $('.test-template').on('click', function() {
        const type = $(this).data('type');
        $('#testType').val(type);
        $('#testSendModal').modal('show');
    });

    // Test send - submit
    $('#testSendForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#sendTestBtn');
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        $.post('{{ route("admin.whatsapp.templates.test") }}', {
            _token: '{{ csrf_token() }}',
            type: $('#testType').val(),
            phone: $('#testPhone').val()
        }).done(function(res) {
            Swal.fire({ icon: res.success ? 'success' : 'error', text: res.message });
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-send"></i> {{ trans("clients.whatsapp_send") ?? "إرسال" }}');
            $('#testSendModal').modal('hide');
        });
    });

    // Copy preview to clipboard
    $('.copy-preview').on('click', function() {
        const type = $(this).data('type');
        const text = $('#preview-' + type).text();
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({ icon: 'success', text: '{{ trans("clients.copied") ?? "تم النسخ" }}', timer: 1500, showConfirmButton: false });
        });
    });

    // Auto-preview on textarea change (with debounce)
    $('.template-body').on('input', debounce(function() {
        const type = $(this).data('type');
        updatePreview(type);
    }, 500));

    function updatePreview(type) {
        const body = $('#body-' + type).val();
        const sampleData = {
            '{name}': '{{ trans("clients.whatsapp_sample_name") ?? "محمد أحمد" }}',
            '{total_amount}': '50.00',
            '{amount}': '15.00',
            '{month}': '07',
            '{year}': '2026',
            '{collector}': '{{ trans("clients.whatsapp_sample_collector") ?? "أحمد" }}',
            '{datetime}': new Date().toLocaleString('ar-SA'),
            '{balance_status}': '{{ trans("clients.whatsapp_sample_balance") ?? "الرصيد الحالي: $0.00" }}',
            '{due_date}': new Date(Date.now() + 3*86400000).toLocaleDateString('ar-SA'),
            '{support_phone}': '96170781562',
            '{invoice_details_list}': "❌ 07 / 2026      $20.00\n❌ 06 / 2026      $20.00",
            '{message_body}': '{{ trans("clients.whatsapp_sample_custom") ?? "نص الرسالة المخصصة" }}',
        };
        let preview = body;
        for (const [key, val] of Object.entries(sampleData)) {
            preview = preview.replaceAll(key, val);
        }
        $('#preview-' + type).html(preview.replace(/\n/g, '<br>'));
    }

    function saveTemplate(type, body) {
        $.post('{{ route("admin.whatsapp.templates.save") }}', {
            _token: '{{ csrf_token() }}',
            type: type,
            body: body
        }).done(function(res) {
            Swal.fire({ icon: 'success', text: res.message, timer: 2000, showConfirmButton: false });
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
});
</script>
@endsection
