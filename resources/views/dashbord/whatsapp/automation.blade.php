@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_automation') ?? 'التشغيل الآلي' }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_automation') ?? 'التشغيل الآلي';
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
        ['label' => trans('clients.whatsapp_automation') ?? 'التشغيل الآلي', 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="d-flex flex-column gap-6">
        @php
        $dayNames = ['سبت', 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة'];
        $dayNamesShort = ['س', 'ح', 'ن', 'ث', 'ر', 'خ', 'ج'];
        $templateLabels = [];
        foreach ($templates as $type => $tmpl) {
            $templateLabels[$type] = app()->getLocale() == 'ar' ? ($tmpl['label'] ?? $type) : ($tmpl['label_en'] ?? $type);
        }
        @endphp

        @forelse($rules as $rule)
        @php
        $ruleId = $rule['id'];
        $isEnabled = $rule['enabled'] ?? false;
        $color = $rule['color'] ?? 'primary';
        $icon = $rule['icon'] ?? 'bi bi-gear';
        $days = $rule['days'] ?? [];
        $daysCount = count($days);
        $daysSummary = '';
        if ($daysCount === 7) {
            $daysSummary = 'كل الأيام';
        } elseif ($daysCount === 0) {
            $daysSummary = '-';
        } else {
            $parts = [];
            foreach ($days as $d) {
                $parts[] = $dayNamesShort[$d] ?? $d;
            }
            $daysSummary = implode('، ', $parts);
        }
        $templateName = $templateLabels[$rule['template']] ?? $rule['template'] ?? '-';
        $offset = (int) ($rule['days_offset'] ?? 0);
        if ($offset < 0) {
            $offsetLabel = ($rule['days_offset_label'] ?? 'قبل') . ' ' . abs($offset) . ' ' . ($rule['days_offset_unit'] ?? 'أيام');
        } elseif ($offset > 0) {
            $offsetLabel = ($rule['days_offset_label'] ?? 'بعد') . ' ' . $offset . ' ' . ($rule['days_offset_unit'] ?? 'أيام');
        } else {
            $offsetLabel = 'فوري';
        }

        // Build filter summary
        $filterParts = [];
        if ($ruleId === 'whatsapp_remind_before') {
            $fcType = $rule['filter_client_type'] ?? 'all';
            $fcStatus = $rule['filter_client_status'] ?? 'all';
            $fmUnpaid = (int) ($rule['filter_min_unpaid'] ?? 0);

            if ($fcType !== 'all') {
                $filterParts[] = $fcType === 'internet' ? 'إنترنت' : 'ساتلايت';
            }
            if ($fcStatus !== 'all') {
                $filterParts[] = $fcStatus === 'active' ? 'نشط' : 'غير نشط';
            }
            if ($fmUnpaid > 0) {
                $filterParts[] = '≥ ' . $fmUnpaid . ' unpaid';
            }
        }
        $filtersSummary = !empty($filterParts) ? implode('، ', $filterParts) : 'الكل';
        @endphp

        <div class="card" id="rule-card-{{ $ruleId }}">
            <div class="card-header d-flex flex-wrap align-items-center gap-3" style="min-height: 60px;">
                <div class="d-flex align-items-center gap-3">
                    <i class="{{ $icon }} fs-2x text-{{ $color }}"></i>
                    <div>
                        <span class="card-title fw-bold fs-6 mb-0">
                            {{ app()->getLocale() == 'ar' ? $rule['label'] : $rule['label_en'] }}
                        </span>
                        <span class="badge badge-light-{{ $isEnabled ? 'success' : 'secondary' }} fs-8 ms-2" id="status-badge-{{ $ruleId }}">
                            {{ $isEnabled ? '🟢 ' . (trans('clients.active') ?? 'مفعل') : '⚪ ' . (trans('clients.inactive') ?? 'معطل') }}
                        </span>
                    </div>
                </div>
                <div class="ms-auto d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-light-{{ $color }} toggle-rule-btn" data-id="{{ $ruleId }}">
                        <i class="bi {{ $isEnabled ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                        {{ $isEnabled ? (trans('clients.whatsapp_disable') ?? 'تعطيل') : (trans('clients.whatsapp_enable') ?? 'تفعيل') }}
                    </button>
                    <button class="btn btn-sm btn-primary edit-rule-btn" data-id="{{ $ruleId }}">
                        <i class="bi bi-pencil"></i> {{ trans('clients.whatsapp_edit') ?? 'تعديل' }}
                    </button>
                    <button class="btn btn-sm btn-success run-rule-btn" data-id="{{ $ruleId }}">
                        <i class="bi bi-play-fill"></i> {{ trans('clients.whatsapp_run_now') ?? 'تشغيل الآن' }}
                    </button>
                </div>
            </div>

            {{-- Summary View --}}
            <div class="card-body" id="summary-{{ $ruleId }}">
                <div class="row g-4">
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock text-muted me-2 fs-5"></i>
                            <div>
                                <div class="text-muted fs-8">{{ trans('clients.whatsapp_time') ?? 'وقت التشغيل' }}</div>
                                <div class="fw-semibold" id="summary-time-{{ $ruleId }}">{{ $rule['time'] ?? '09:00' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-week text-muted me-2 fs-5"></i>
                            <div>
                                <div class="text-muted fs-8">{{ trans('clients.whatsapp_days') ?? 'الأيام' }}</div>
                                <div class="fw-semibold" id="summary-days-{{ $ruleId }}">{{ $daysSummary }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-text text-muted me-2 fs-5"></i>
                            <div>
                                <div class="text-muted fs-8">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</div>
                                <div class="fw-semibold" id="summary-template-{{ $ruleId }}">{{ $templateName }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hourglass-split text-muted me-2 fs-5"></i>
                            <div>
                                <div class="text-muted fs-8">{{ trans('clients.whatsapp_offset') ?? 'التوقيت' }}</div>
                                <div class="fw-semibold" id="summary-offset-{{ $ruleId }}">{{ $offsetLabel }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($ruleId === 'whatsapp_remind_before')
                <div class="row g-4 mt-2">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-funnel text-muted me-2 fs-5"></i>
                            <div>
                                <div class="text-muted fs-8">{{ trans('clients.whatsapp_filters') ?? 'فلترة الزبائن' }}</div>
                                <div class="fw-semibold" id="summary-filters-{{ $ruleId }}">{{ $filtersSummary }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Inline Edit Form (hidden by default) --}}
            <div class="card-body d-none border-top" id="edit-form-{{ $ruleId }}">
                <form class="rule-edit-form" data-id="{{ $ruleId }}">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_time') ?? 'وقت التشغيل' }}</label>
                            <input type="time" class="form-control" name="time" value="{{ $rule['time'] ?? '09:00' }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_days') ?? 'أيام التشغيل' }}</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($dayNames as $i => $dname)
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input day-cb" type="checkbox" name="days[]" value="{{ $i }}"
                                        {{ in_array($i, $days) ? 'checked' : '' }}>
                                    <label class="form-check-label fs-7">{{ $dname }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</label>
                            <select class="form-select form-select-sm" name="template">
                                @foreach($templates as $type => $tmpl)
                                <option value="{{ $type }}" {{ $rule['template'] == $type ? 'selected' : '' }}>
                                    {{ $templateLabels[$type] ?? $type }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold fs-7">{{ $rule['days_offset_label'] ?? trans('clients.whatsapp_offset') ?? 'الأيام' }}</label>
                            <input type="number" class="form-control" name="days_offset" value="{{ $offset }}"
                                   min="-30" max="30"
                                   title="{{ trans('clients.whatsapp_offset_help') ?? 'قبل (-) أو بعد (+) الأيام' }}">
                        </div>
                    </div>

                    @if($ruleId === 'whatsapp_remind_before')
                    {{-- ── Advanced Filters for Reminder Rule ── --}}
                    <div class="separator separator-dashed my-4"></div>
                    <div class="fw-bold fs-7 mb-3">
                        <i class="bi bi-funnel me-1"></i>
                        {{ trans('clients.whatsapp_client_filters') ?? 'فلترة الزبائن المراد تذكيرهم' }}
                    </div>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.client_type') ?? 'نوع العميل' }}</label>
                            <select class="form-select form-select-sm" name="filter_client_type">
                                <option value="all" {{ ($rule['filter_client_type'] ?? 'all') == 'all' ? 'selected' : '' }}>{{ trans('clients.all') ?? 'الكل' }}</option>
                                <option value="internet" {{ ($rule['filter_client_type'] ?? 'all') == 'internet' ? 'selected' : '' }}>{{ trans('clients.whatsapp_internet') ?? 'إنترنت' }}</option>
                                <option value="satellite" {{ ($rule['filter_client_type'] ?? 'all') == 'satellite' ? 'selected' : '' }}>{{ trans('clients.whatsapp_satellite') ?? 'ساتلايت' }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_subscription') ?? 'الاشتراك' }}</label>
                            <select class="form-select form-select-sm" name="filter_subscription_id">
                                <option value="">{{ trans('clients.all') ?? 'الكل' }}</option>
                                @foreach($subscriptions as $sub)
                                <option value="{{ $sub->id }}" {{ ($rule['filter_subscription_id'] ?? '') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_min_unpaid') ?? 'حد أدنى unpaid' }}</label>
                            <input type="number" class="form-control form-control-sm" name="filter_min_unpaid"
                                   value="{{ $rule['filter_min_unpaid'] ?? 0 }}" min="0" max="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.status') ?? 'الحالة' }}</label>
                            <select class="form-select form-select-sm" name="filter_client_status">
                                <option value="all" {{ ($rule['filter_client_status'] ?? 'all') == 'all' ? 'selected' : '' }}>{{ trans('clients.all') ?? 'الكل' }}</option>
                                <option value="active" {{ ($rule['filter_client_status'] ?? 'all') == 'active' ? 'selected' : '' }}>{{ trans('clients.active') ?? 'نشط' }}</option>
                                <option value="inactive" {{ ($rule['filter_client_status'] ?? 'all') == 'inactive' ? 'selected' : '' }}>{{ trans('clients.inactive') ?? 'غير نشط' }}</option>
                            </select>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light cancel-edit-btn" data-id="{{ $ruleId }}">
                            <i class="bi bi-x-lg"></i> {{ trans('clients.whatsapp_cancel') ?? 'إلغاء' }}
                        </button>
                        <button type="submit" class="btn btn-primary save-rule-btn" data-id="{{ $ruleId }}">
                            <i class="bi bi-check-lg"></i> {{ trans('clients.whatsapp_save') ?? 'حفظ' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="card">
            <div class="card-body text-center py-10">
                <i class="bi bi-gear fs-3x text-muted d-block mb-3"></i>
                <p class="text-muted">{{ trans('clients.whatsapp_no_rules') ?? 'لا توجد قواعد تشغيل آلي' }}</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    let currentEditId = null;

    // ── Toggle rule on/off ──
    $(document).on('click', '.toggle-rule-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');

        $.post('{{ route("admin.whatsapp.automation.toggle", ["id" => "RULE_ID"]) }}'.replace("RULE_ID", id), {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            const badge = $('#status-badge-' + id);
            if (res.enabled) {
                badge.removeClass('badge-light-secondary').addClass('badge-light-success')
                    .text('🟢 {{ trans("clients.active") ?? "مفعل" }}');
                btn.removeClass('btn-light-secondary').addClass('btn-light-success')
                    .html('<i class="bi bi-pause-circle"></i> {{ trans("clients.whatsapp_disable") ?? "تعطيل" }}');
            } else {
                badge.removeClass('badge-light-success').addClass('badge-light-secondary')
                    .text('⚪ {{ trans("clients.inactive") ?? "معطل" }}');
                btn.removeClass('btn-light-success').addClass('btn-light-secondary')
                    .html('<i class="bi bi-play-circle"></i> {{ trans("clients.whatsapp_enable") ?? "تفعيل" }}');
            }
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false);
        });
    });

    // ── Edit / Cancel toggle ──
    $(document).on('click', '.edit-rule-btn', function() {
        const id = $(this).data('id');
        toggleEdit(id);
    });

    $(document).on('click', '.cancel-edit-btn', function() {
        const id = $(this).closest('.rule-edit-form').data('id');
        toggleEdit(id);
    });

    function toggleEdit(id) {
        const editForm = $('#edit-form-' + id);
        const summary = $('#summary-' + id);
        const editBtn = $('.edit-rule-btn[data-id="' + id + '"]');

        if (currentEditId === id) {
            editForm.addClass('d-none');
            summary.removeClass('d-none');
            editBtn.html('<i class="bi bi-pencil"></i> {{ trans("clients.whatsapp_edit") ?? "تعديل" }}');
            currentEditId = null;
            return;
        }

        if (currentEditId !== null) {
            const prev = $('#edit-form-' + currentEditId);
            const prevSummary = $('#summary-' + currentEditId);
            const prevBtn = $('.edit-rule-btn[data-id="' + currentEditId + '"]');
            prev.addClass('d-none');
            prevSummary.removeClass('d-none');
            prevBtn.html('<i class="bi bi-pencil"></i> {{ trans("clients.whatsapp_edit") ?? "تعديل" }}');
        }

        editForm.removeClass('d-none');
        summary.addClass('d-none');
        editBtn.html('<i class="bi bi-x-lg"></i> {{ trans("clients.whatsapp_cancel_edit") ?? "إلغاء" }}');
        currentEditId = id;
    }

    // ── Save rule settings ──
    $(document).on('submit', '.rule-edit-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const id = form.data('id');
        const submitBtn = form.find('.save-rule-btn');
        submitBtn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');

        const formData = form.serializeArray();
        const days = [];
        form.find('.day-cb:checked').each(function() {
            days.push($(this).val());
        });

        const postData = {};
        formData.forEach(function(item) {
            if (item.name !== 'days[]') {
                postData[item.name] = item.value;
            }
        });
        postData.days = days;
        postData._token = '{{ csrf_token() }}';

        $.ajax({
            url: '{{ route("admin.whatsapp.automation.save", ["id" => "RULE_ID"]) }}'.replace("RULE_ID", id),
            method: 'POST',
            data: postData,
            traditional: true
        }).done(function(res) {
            if (res.success) {
                $('#summary-time-' + id).text(res.rule.time);
                $('#summary-days-' + id).text(res.days_summary);
                $('#summary-template-' + id).text(
                    form.find('select[name="template"] option:selected').text()
                );
                const offsetVal = parseInt(form.find('input[name="days_offset"]').val()) || 0;
                let offsetText = '';
                if (offsetVal < 0) {
                    offsetText = '{{ trans("clients.whatsapp_before") ?? "قبل" }} ' + Math.abs(offsetVal) + ' {{ trans("clients.whatsapp_days_unit") ?? "أيام" }}';
                } else if (offsetVal > 0) {
                    offsetText = '{{ trans("clients.whatsapp_after") ?? "بعد" }} ' + offsetVal + ' {{ trans("clients.whatsapp_days_unit") ?? "أيام" }}';
                } else {
                    offsetText = 'فوري';
                }
                $('#summary-offset-' + id).text(offsetText);

                // Update filter summary if present
                if ($('#summary-filters-' + id).length) {
                    $('#summary-filters-' + id).text(res.filters_summary || '{{ trans("clients.all") ?? "الكل" }}');
                }

                toggleEdit(id);

                Swal.fire({
                    icon: 'success',
                    text: '{{ trans("clients.whatsapp_settings_saved") ?? "تم الحفظ" }}',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }).fail(function(xhr) {
            let msg = '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errs = Object.values(xhr.responseJSON.errors).flat();
                msg = errs.join('<br>');
            }
            Swal.fire({ icon: 'error', html: msg });
        }).always(function() {
            submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> {{ trans("clients.whatsapp_save") ?? "حفظ" }}');
        });
    });

    // ── Run rule now ──
    $(document).on('click', '.run-rule-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');

        $.post('{{ route("admin.whatsapp.automation.run", ["id" => "RULE_ID"]) }}'.replace("RULE_ID", id), {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    text: '{{ trans("clients.whatsapp_rule_executed") ?? "تم تشغيل القاعدة بنجاح" }}',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({ icon: 'error', text: res.error || '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
            }
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i> {{ trans("clients.whatsapp_run_now") ?? "تشغيل الآن" }}');
        });
    });
});
</script>

<style>
.spinner {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.card {
    transition: box-shadow 0.15s ease;
}
.card:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.08);
}
.form-check-input.day-cb {
    width: 1.1em;
    height: 1.1em;
    cursor: pointer;
}
.form-check-label {
    cursor: pointer;
    user-select: none;
}
</style>
@endsection
