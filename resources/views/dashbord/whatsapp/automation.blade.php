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
@include('dashbord.whatsapp._partials.tab-nav')

<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#rules-tab" role="tab">
                        {{ trans('clients.whatsapp_automation_rules') ?? 'قواعد التشغيل الآلي' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#calendar-tab" role="tab">
                        {{ trans('clients.whatsapp_calendar_monthly') ?? 'التقويم الشهري' }}
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- == RULES TAB == --}}
                <div class="tab-pane active" id="rules-tab" role="tabpanel">
                    <div class="row g-4">

                        {{-- == RULE 1: Reminder Before Disconnection == --}}
                        @php $r1 = $rules['whatsapp_remind_before'] ?? null; @endphp
                        @if($r1)
                        <div class="col-lg-6">
                            <div class="card card-flush shadow-sm h-100">
                                <div class="card-header py-3">
                                    <div class="card-title d-flex align-items-center gap-2">
                                        <span class="badge badge-light-primary fs-1 p-2">
                                            <i class="bi bi-bell"></i>
                                        </span>
                                        <div>
                                            <h5 class="mb-0 fw-bold">{{ app()->getLocale() == 'ar' ? ($r1['label'] ?? 'تذكير قبل التعطيل') : ($r1['label_en'] ?? 'Reminder Before Disconnection') }}</h5>
                                            <small class="text-muted">{{ $r1['description'] ?? 'إرسال تذكير للزبائن قبل موعد تعطيل الخدمة بعدد أيام محددة' }}</small>
                                        </div>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="form-check form-switch form-switch-custom form-switch-primary">
                                            <input class="form-check-input rule-toggle" type="checkbox"
                                                   id="toggle_whatsapp_remind_before"
                                                   data-id="whatsapp_remind_before"
                                                   {{ ($r1['enabled'] ?? false) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Time --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_rule_time') ?? 'الوقت' }}</label>
                                            <input type="time" class="form-control form-control-sm"
                                                   id="time_whatsapp_remind_before"
                                                   value="{{ $r1['time'] ?? '09:00' }}">
                                        </div>
                                        {{-- Template --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</label>
                                            <select class="form-select form-select-sm"
                                                    id="template_whatsapp_remind_before">
                                                @foreach($templates as $tplId => $tpl)
                                                    <option value="{{ $tplId }}"
                                                        {{ ($r1['template'] ?? '') == $tplId ? 'selected' : '' }}>
                                                        {{ $tpl['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Days Offset --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_rule_days_before') ?? 'عدد الأيام قبل التعطيل' }}</label>
                                            <input type="number" class="form-control form-control-sm"
                                                   id="days_whatsapp_remind_before"
                                                   min="1" max="30"
                                                   value="{{ $r1['days_before'] ?? 3 }}">
                                        </div>
                                        {{-- Days of Week --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_rule_days_of_week') ?? 'أيام الأسبوع' }}</label>
                                            <select class="form-select form-select-sm"
                                                    id="dow_whatsapp_remind_before" multiple
                                                    style="min-height:80px;">
                                                @php
                                                $dowLabels = [
                                                    0 => 'الأحد', 1 => 'الاثنين', 2 => 'الثلاثاء',
                                                    3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'
                                                ];
                                                $selectedDow = $r1['days_of_week'] ?? [0,1,2,3,4,5,6];
                                                if (!is_array($selectedDow)) $selectedDow = [0,1,2,3,4,5,6];
                                                @endphp
                                                @foreach($dowLabels as $dayNum => $dayName)
                                                    <option value="{{ $dayNum }}"
                                                        {{ in_array($dayNum, $selectedDow) ? 'selected' : '' }}>
                                                        {{ $dayName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- == Filters Section == --}}
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="fw-bold fs-7 text-gray-600 mb-3">
                                            <i class="bi bi-funnel me-1"></i> {{ trans('clients.whatsapp_rule_filters') ?? 'التصفية' }}
                                        </h6>
                                        <div class="row g-3">
                                            {{-- Client Type --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold fs-8">{{ trans('clients.client_type') ?? 'نوع العميل' }}</label>
                                                <select class="form-select form-select-sm filter-select"
                                                        id="filter_type_whatsapp_remind_before"
                                                        data-rule="whatsapp_remind_before">
                                                    <option value="all" {{ ($r1['filter_client_type'] ?? 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                                                    <option value="internet" {{ ($r1['filter_client_type'] ?? '') == 'internet' ? 'selected' : '' }}>إنترنت</option>
                                                    <option value="satellite" {{ ($r1['filter_client_type'] ?? '') == 'satellite' ? 'selected' : '' }}>ساتلايت</option>
                                                </select>
                                            </div>
                                            {{-- Subscription --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold fs-8">{{ trans('clients.subscription') ?? 'الاشتراك' }}</label>
                                                <select class="form-select form-select-sm filter-select"
                                                        id="filter_sub_whatsapp_remind_before"
                                                        data-rule="whatsapp_remind_before">
                                                    <option value="all">الكل</option>
                                                    @foreach($subscriptions as $sub)
                                                        <option value="{{ $sub->id }}"
                                                            {{ ($r1['filter_subscription'] ?? '') == $sub->id ? 'selected' : '' }}>
                                                            {{ $sub->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            {{-- Min Unpaid --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold fs-8">{{ trans('clients.min_unpaid') ?? 'الحد الأدنى للمبلغ غير المدفوع' }}</label>
                                                <input type="number" class="form-control form-control-sm filter-input"
                                                       id="filter_min_whatsapp_remind_before"
                                                       data-rule="whatsapp_remind_before"
                                                       min="0" step="0.01"
                                                       value="{{ $r1['filter_min_unpaid'] ?? 0 }}">
                                            </div>
                                            {{-- Client Status --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold fs-8">{{ trans('clients.status') ?? 'حالة العميل' }}</label>
                                                <select class="form-select form-select-sm filter-select"
                                                        id="filter_status_whatsapp_remind_before"
                                                        data-rule="whatsapp_remind_before">
                                                    <option value="all">الكل</option>
                                                    <option value="active" {{ ($r1['filter_status'] ?? '') == 'active' ? 'selected' : '' }}>نشط</option>
                                                    <option value="inactive" {{ ($r1['filter_status'] ?? '') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- == Actions == --}}
                                    <div class="mt-4 pt-3 border-top d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-light-primary preview-rule"
                                                data-id="whatsapp_remind_before">
                                            <i class="bi bi-eye"></i> {{ trans('clients.whatsapp_preview') ?? 'معاينة' }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary save-rule"
                                                data-id="whatsapp_remind_before">
                                            <i class="bi bi-check-lg"></i> {{ trans('clients.whatsapp_save') ?? 'حفظ' }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success run-rule"
                                                data-id="whatsapp_remind_before">
                                            <i class="bi bi-play-fill"></i> {{ trans('clients.whatsapp_run_now') ?? 'تشغيل الآن' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- == RULE 2: Overdue Reminder == --}}
                        @php $r2 = $rules['whatsapp_custom'] ?? null; @endphp
                        @if($r2)
                        <div class="col-lg-6">
                            <div class="card card-flush shadow-sm h-100">
                                <div class="card-header py-3">
                                    <div class="card-title d-flex align-items-center gap-2">
                                        <span class="badge badge-light-warning fs-1 p-2">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <div>
                                            <h5 class="mb-0 fw-bold">{{ app()->getLocale() == 'ar' ? ($r2['label'] ?? 'تذكير متأخر') : ($r2['label_en'] ?? 'Overdue Reminder') }}</h5>
                                            <small class="text-muted">{{ $r2['description'] ?? 'إرسال تذكير للزبائن الذين تأخر سداد فواتيرهم' }}</small>
                                        </div>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="form-check form-switch form-switch-custom form-switch-warning">
                                            <input class="form-check-input rule-toggle" type="checkbox"
                                                   id="toggle_whatsapp_custom"
                                                   data-id="whatsapp_custom"
                                                   {{ ($r2['enabled'] ?? false) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Time --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_rule_time') ?? 'الوقت' }}</label>
                                            <input type="time" class="form-control form-control-sm"
                                                   id="time_whatsapp_custom"
                                                   value="{{ $r2['time'] ?? '09:00' }}">
                                        </div>
                                        {{-- Template --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</label>
                                            <select class="form-select form-select-sm"
                                                    id="template_whatsapp_custom">
                                                @foreach($templates as $tplId => $tpl)
                                                    <option value="{{ $tplId }}"
                                                        {{ ($r2['template'] ?? '') == $tplId ? 'selected' : '' }}>
                                                        {{ $tpl['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Days of Week --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_rule_days_of_week') ?? 'أيام الأسبوع' }}</label>
                                            <select class="form-select form-select-sm"
                                                    id="dow_whatsapp_custom" multiple
                                                    style="min-height:80px;">
                                                @php
                                                $dowLabels2 = [
                                                    0 => 'الأحد', 1 => 'الاثنين', 2 => 'الثلاثاء',
                                                    3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'
                                                ];
                                                $selectedDow2 = $r2['days_of_week'] ?? [0,1,2,3,4,5,6];
                                                if (!is_array($selectedDow2)) $selectedDow2 = [0,1,2,3,4,5,6];
                                                @endphp
                                                @foreach($dowLabels2 as $dayNum => $dayName)
                                                    <option value="{{ $dayNum }}"
                                                        {{ in_array($dayNum, $selectedDow2) ? 'selected' : '' }}>
                                                        {{ $dayName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- == Filters Section == --}}
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="fw-bold fs-7 text-gray-600 mb-3">
                                            <i class="bi bi-funnel me-1"></i> {{ trans('clients.whatsapp_rule_filters') ?? 'التصفية' }}
                                        </h6>
                                        <div class="row g-3">
                                            {{-- Client Type --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold fs-8">{{ trans('clients.client_type') ?? 'نوع العميل' }}</label>
                                                <select class="form-select form-select-sm filter-select"
                                                        id="filter_type_whatsapp_custom"
                                                        data-rule="whatsapp_custom">
                                                    <option value="all" {{ ($r2['filter_client_type'] ?? 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                                                    <option value="internet" {{ ($r2['filter_client_type'] ?? '') == 'internet' ? 'selected' : '' }}>إنترنت</option>
                                                    <option value="satellite" {{ ($r2['filter_client_type'] ?? '') == 'satellite' ? 'selected' : '' }}>ساتلايت</option>
                                                </select>
                                            </div>
                                            {{-- Subscription --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold fs-8">{{ trans('clients.subscription') ?? 'الاشتراك' }}</label>
                                                <select class="form-select form-select-sm filter-select"
                                                        id="filter_sub_whatsapp_custom"
                                                        data-rule="whatsapp_custom">
                                                    <option value="all">الكل</option>
                                                    @foreach($subscriptions as $sub)
                                                        <option value="{{ $sub->id }}"
                                                            {{ ($r2['filter_subscription'] ?? '') == $sub->id ? 'selected' : '' }}>
                                                            {{ $sub->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            {{-- Min Unpaid --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold fs-8">{{ trans('clients.min_unpaid') ?? 'الحد الأدنى للمبلغ غير المدفوع' }}</label>
                                                <input type="number" class="form-control form-control-sm filter-input"
                                                       id="filter_min_whatsapp_custom"
                                                       data-rule="whatsapp_custom"
                                                       min="0" step="0.01"
                                                       value="{{ $r2['filter_min_unpaid'] ?? 0 }}">
                                            </div>
                                            {{-- Client Status --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold fs-8">{{ trans('clients.status') ?? 'حالة العميل' }}</label>
                                                <select class="form-select form-select-sm filter-select"
                                                        id="filter_status_whatsapp_custom"
                                                        data-rule="whatsapp_custom">
                                                    <option value="all">الكل</option>
                                                    <option value="active" {{ ($r2['filter_status'] ?? '') == 'active' ? 'selected' : '' }}>نشط</option>
                                                    <option value="inactive" {{ ($r2['filter_status'] ?? '') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- == Actions == --}}
                                    <div class="mt-4 pt-3 border-top d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-light-primary preview-rule"
                                                data-id="whatsapp_custom">
                                            <i class="bi bi-eye"></i> {{ trans('clients.whatsapp_preview') ?? 'معاينة' }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary save-rule"
                                                data-id="whatsapp_custom">
                                            <i class="bi bi-check-lg"></i> {{ trans('clients.whatsapp_save') ?? 'حفظ' }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success run-rule"
                                                data-id="whatsapp_custom">
                                            <i class="bi bi-play-fill"></i> {{ trans('clients.whatsapp_run_now') ?? 'تشغيل الآن' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- == CALENDAR TAB == --}}
                <div class="tab-pane" id="calendar-tab" role="tabpanel">
                    <div class="monthly-calendar" id="monthlyCalendar">
                        {{-- Month Navigation --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <button class="btn btn-sm btn-light nav-calendar" data-dir="prev" id="calPrev">
                                <i class="bi bi-chevron-right"></i> السابق
                            </button>
                            <h3 class="mb-0 fw-bold" id="calTitle">{{ now()->locale('ar')->isoFormat('MMMM YYYY') }}</h3>
                            <button class="btn btn-sm btn-light nav-calendar" data-dir="next" id="calNext">
                                التالي <i class="bi bi-chevron-left"></i>
                            </button>
                        </div>

                        {{-- Filter Bar --}}
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold fs-7">{{ trans('clients.client_type') ?? 'نوع العميل' }}</label>
                                <select class="form-select form-select-sm" id="calClientType">
                                    <option value="all">{{ trans('clients.all') ?? 'الكل' }}</option>
                                    <option value="internet">{{ trans('clients.internet') ?? 'إنترنت' }}</option>
                                    <option value="satellite">{{ trans('clients.satellite') ?? 'ساتلايت' }}</option>
                                </select>
                            </div>
                            <div class="col-md-9 d-flex align-items-end justify-content-end">
                                <small class="text-muted" id="calFilterInfo">
                                    <i class="bi bi-info-circle"></i> عرض كل الزبائن غير المدفوعين
                                </small>
                            </div>
                        </div>

                        {{-- Loading Spinner --}}
                        <div class="text-center py-4 d-none" id="calLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>

                        {{-- Calendar Grid (rendered by JS on load) --}}
                        <div id="calGrid"></div>

                        {{-- Legend --}}
                        <div class="d-flex justify-content-center gap-4 mt-3 text-muted small">
                            <span><span class="badge badge-success" style="width:10px;height:10px;display:inline-block;border-radius:50%;">&nbsp;</span> في زبائن غير مدفوعين</span>
                            <span><span class="badge badge-light" style="width:10px;height:10px;display:inline-block;border-radius:50%;border:1px solid #ddd;">&nbsp;</span> اليوم</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- == DAY DETAIL MODAL == --}}
<div class="modal fade" id="dayDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold" id="dayModalTitle">
                    <i class="bi bi-calendar-event text-primary ms-1"></i>
                    {{ trans('clients.whatsapp_calendar_monthly') ?? 'التقويم الشهري' }}
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-light-primary fs-7 px-3 py-2 d-none" id="dayModalStats">
                        <i class="bi bi-people"></i> <span id="dayModalTotal">0</span>
                        {{ trans('clients.clients') ?? 'زبون' }}
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body py-4">
                <div class="text-center py-8 d-none" id="dayModalLoading">
                    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-3">{{ trans('clients.whatsapp_loading') ?? 'جاري تحميل البيانات...' }}</p>
                </div>
                <div id="dayModalContent" class="d-none">
                    <div id="dayClientsContainer"></div>
                    <div class="text-center text-muted py-8 d-none" id="dayNoClients">
                        <i class="bi bi-emoji-frown fs-3x text-gray-400"></i>
                        <p class="mt-3 fs-6">{{ trans('clients.whatsapp_calendar_no_clients') ?? 'لا يوجد زبائن غير مدفوعين في هذا التاريخ' }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-3">
                <div class="d-flex align-items-center gap-2 w-100">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllDayClients">
                        <label class="form-check-label text-muted" for="selectAllDayClients">
                            {{ trans('clients.select_all') ?? 'تحديد الكل' }}
                        </label>
                    </div>
                    <div class="flex-grow-1"></div>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ trans('clients.close') ?? 'إغلاق' }}
                    </button>
                    <button type="button" class="btn btn-primary" id="sendDayReminders" disabled>
                        <i class="bi bi-send"></i> {{ trans('clients.whatsapp_send_reminder') ?? 'إرسال تذكير للمحددين' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- == PREVIEW MODAL == --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold" id="previewModalTitle">
                    <i class="bi bi-eye text-primary ms-1"></i> {{ trans('clients.whatsapp_preview') ?? 'معاينة' }}
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-light-info fs-7 px-3 py-2" id="previewCount">
                        <i class="bi bi-people"></i> <span id="previewCountNum">0</span>
                        {{ trans('clients.clients') ?? 'زبون' }}
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body py-4">
                <div class="text-center py-8 d-none" id="previewLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-3">{{ trans('clients.whatsapp_loading') ?? 'جاري تحميل البيانات...' }}</p>
                </div>
                <div id="previewContent" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-hover align-middle mb-0">
                            <thead>
                                <tr class="fw-bold fs-7 text-gray-600">
                                    <th>{{ trans('clients.name') ?? 'الاسم' }}</th>
                                    <th>{{ trans('clients.phone') ?? 'الهاتف' }}</th>
                                    <th>{{ trans('clients.due_date') ?? 'تاريخ الاستحقاق' }}</th>
                                    <th class="text-end">{{ trans('clients.total_amount') ?? 'المبلغ' }}</th>
                                    <th class="text-center">{{ trans('clients.count') ?? 'الفواتير' }}</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                            </tbody>
                            <tfoot id="previewTableFoot" class="d-none">
                                <tr class="fw-bold">
                                    <td colspan="3">{{ trans('clients.total') ?? 'الإجمالي' }}</td>
                                    <td class="text-end text-danger" id="previewTotalAmount">$0.00</td>
                                    <td class="text-center" id="previewTotalInvoices">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="text-center text-muted py-6 d-none" id="previewEmpty">
                        <i class="bi bi-emoji-frown fs-3x text-gray-400"></i>
                        <p class="mt-3 fs-6">{{ trans('clients.whatsapp_calendar_no_clients') ?? 'لا يوجد زبائن يطابقون المعايير' }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    {{ trans('clients.close') ?? 'إغلاق' }}
                </button>
                <button type="button" class="btn btn-success" id="previewSendBtn" disabled>
                    <i class="bi bi-send"></i> {{ trans('clients.whatsapp_send_reminder') ?? 'إرسال التذكيرات' }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    // =================================================================
    //  RULE CARDS: Toggle, Save, Run, Preview
    // =================================================================

    var currentPreviewId = null;

    // -- Toggle Switch --
    $('.rule-toggle').on('change', function() {
        var id = $(this).data('id');
        var checkbox = $(this);
        $.post('{{ route("admin.whatsapp.automation.toggle", "__ID__") }}'.replace('__ID__', id), {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            Swal.fire({ icon: 'success', text: res.enabled ? 'تم التفعيل' : 'تم التعطيل', timer: 1500, showConfirmButton: false });
        }).fail(function() {
            checkbox.prop('checked', !checkbox.is(':checked'));
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        });
    });

    // -- Save Rule --
    $('.save-rule').on('click', function() {
        var id = $(this).data('id');
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');

        var daysOfWeek = [];
        $('#dow_' + id + ' option:selected').each(function() {
            daysOfWeek.push(parseInt($(this).val()));
        });

        var payload = {
            _token: '{{ csrf_token() }}',
            time: $('#time_' + id).val(),
            template: $('#template_' + id).val(),
            days: daysOfWeek,
            days_offset: parseInt($('#days_' + id).val() || 0),
            filter_client_type: $('#filter_type_' + id).val(),
            filter_subscription_id: $('#filter_sub_' + id).val(),
            filter_min_unpaid: parseInt($('#filter_min_' + id).val() || 0),
            filter_client_status: $('#filter_status_' + id).val()
        };

        // Days offset only for remind_before rule
        if (id === 'whatsapp_remind_before') {
            payload.days_offset = parseInt($('#days_' + id).val() || 3);
        }

        $.post('{{ route("admin.whatsapp.automation.save", "__ID__") }}'.replace('__ID__', id), payload)
            .done(function(res) {
                Swal.fire({ icon: 'success', text: res.message || 'تم الحفظ بنجاح', timer: 1500, showConfirmButton: false });
            }).fail(function() {
                Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ أثناء الحفظ" }}' });
            }).always(function() {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> {{ trans("clients.whatsapp_save") ?? "حفظ" }}');
            });
    });

    // -- Run Rule --
    $('.run-rule').on('click', function() {
        var id = $(this).data('id');
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        Swal.fire({ icon: 'info', text: '{{ trans("clients.whatsapp_running") ?? "جارٍ التشغيل..." }}', showConfirmButton: false });
        $.post('{{ route("admin.whatsapp.automation.run", "__ID__") }}'.replace('__ID__', id), {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            Swal.fire({ icon: res.success ? 'success' : 'error', text: res.output || res.error });
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i> {{ trans("clients.whatsapp_run_now") ?? "تشغيل الآن" }}');
        });
    });

    // -- Preview Rule --
    $('.preview-rule').on('click', function() {
        var id = $(this).data('id');
        currentPreviewId = id;

        $('#previewLoading').removeClass('d-none');
        $('#previewContent').addClass('d-none');
        $('#previewTableBody').empty();
        $('#previewTableFoot').addClass('d-none');
        $('#previewEmpty').addClass('d-none');
        $('#previewSendBtn').prop('disabled', true);
        $('#previewCountNum').text('0');
        $('#previewModalTitle').html('<i class="bi bi-eye text-primary ms-1"></i> ' +
            ($(this).closest('.card').find('h5').text() || 'معاينة'));

        var previewUrl = '{{ route("admin.whatsapp.automation.preview", "__ID__") }}'.replace('__ID__', id);

        $.get(previewUrl)
            .done(function(res) {
                $('#previewLoading').addClass('d-none');
                $('#previewContent').removeClass('d-none');

                if (!res.clients || res.clients.length === 0) {
                    $('#previewEmpty').removeClass('d-none');
                    return;
                }

                var totalAmount = 0;
                var totalInvoices = 0;
                var rows = '';

                res.clients.forEach(function(c) {
                    totalAmount += parseFloat(c.total_amount || 0);
                    var invCount = c.invoices ? c.invoices.length : (c.invoice_count || 0);
                    totalInvoices += parseInt(invCount);

                    // Build due dates column (one per line)
                    var dueDates = '';
                    if (c.invoices && c.invoices.length > 0) {
                        dueDates = c.invoices.map(function(inv) {
                            return inv.due_date;
                        }).join('<br>');
                    }

                    rows += '<tr>' +
                        '<td class="fw-semibold">' + c.name + '</td>' +
                        '<td class="text-muted">' + c.phone + '</td>' +
                        '<td class="text-muted small">' + dueDates + '</td>' +
                        '<td class="text-end fw-bold text-danger">$' + parseFloat(c.total_amount).toFixed(2) + '</td>' +
                        '<td class="text-center"><span class="badge badge-light-warning rounded-pill">' + invCount + '</span></td>' +
                        '</tr>';
                });

                $('#previewTableBody').html(rows);
                $('#previewTableFoot').removeClass('d-none');
                $('#previewTotalAmount').text('$' + totalAmount.toFixed(2));
                $('#previewTotalInvoices').text(totalInvoices);
                $('#previewCountNum').text(res.clients.length);
                $('#previewSendBtn').prop('disabled', false);
            })
            .fail(function() {
                $('#previewLoading').addClass('d-none');
                Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ في تحميل المعاينة" }}' });
            });

        $('#previewModal').modal('show');
    });

    // -- Send from Preview --
    $('#previewSendBtn').on('click', function() {
        if (!currentPreviewId) return;
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i> جاري الإرسال...');

        var sendUrl = '{{ route("admin.whatsapp.automation.send_from_preview", "__ID__") }}'.replace('__ID__', currentPreviewId);

        $.post(sendUrl, { _token: '{{ csrf_token() }}' })
            .done(function(res) {
                var msg = 'تم الإرسال: ' + res.sent;
                if (res.failed > 0) {
                    msg += '<br>فشل: ' + res.failed;
                    if (res.errors && res.errors.length > 0) {
                        msg += '<br><br>الأخطاء:<br>';
                        res.errors.forEach(function(e) { msg += '- ' + e + '<br>'; });
                    }
                }
                Swal.fire({ icon: (res.failed > 0 && res.sent === 0) ? 'error' : 'success', html: msg });
                $('#previewModal').modal('hide');
            })
            .fail(function() {
                Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ في الإرسال" }}' });
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="bi bi-send"></i> {{ trans("clients.whatsapp_send_reminder") ?? "إرسال التذكيرات" }}');
            });
    });

    // =================================================================
    //  TAB 2: MONTHLY CALENDAR
    // =================================================================

    var currentMonth = {{ now()->month }};
    var currentYear = {{ now()->year }};

    // Load initial calendar on tab shown
    $('#calendar-tab').on('shown.bs.tab', function() {
        if ($('#calGrid').is(':empty')) {
            loadCalendar(currentMonth, currentYear);
        }
    });

    // If calendar tab is already active (direct load), render immediately
    if ($('#calendar-tab').hasClass('active') || !$('#rules-tab').hasClass('active')) {
        loadCalendar(currentMonth, currentYear);
    }

    // --- Calendar Navigation ---
    $(document).on('click', '.nav-calendar', function() {
        var dir = $(this).data('dir');
        if (dir === 'prev') {
            currentMonth--;
            if (currentMonth < 1) { currentMonth = 12; currentYear--; }
        } else {
            currentMonth++;
            if (currentMonth > 12) { currentMonth = 1; currentYear++; }
        }
        loadCalendar(currentMonth, currentYear);
    });

    // --- Filter Change ---
    $(document).on('change', '#calClientType', function() {
        var val = $(this).val();
        var label = val === 'all' ? 'الكل' : (val === 'internet' ? 'إنترنت' : 'ساتلايت');
        $('#calFilterInfo').html('<i class="bi bi-funnel"></i> تصفية: ' + label);
        $('#calGrid').empty();
        loadCalendar(currentMonth, currentYear);
    });

    // --- Click on Day ---
    $(document).on('click', '.calendar-day.has-bills', function() {
        var date = $(this).data('date');
        loadDayDetails(date);
    });

    // --- Select All Toggle ---
    $('#selectAllDayClients').on('change', function() {
        $('#dayClientsContainer .client-checkbox').prop('checked', $(this).is(':checked'));
        updateSendButton();
    });

    $(document).on('change', '.client-checkbox', function() {
        updateSendButton();
    });

    // --- Send Reminders from Calendar ---
    $('#sendDayReminders').on('click', function() {
        var selectedIds = [];
        $('#dayClientsContainer .client-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        if (selectedIds.length === 0) return;
        sendDayReminders(selectedIds);
    });

    // =================================================================
    //  HELPER FUNCTIONS
    // =================================================================

    function loadCalendar(month, year) {
        $('#calGrid').addClass('d-none');
        $('#calLoading').removeClass('d-none');
        $('#calTitle').text('...');

        var calClientType = $('#calClientType').val();
        $.get('{{ route("admin.whatsapp.automation.calendar_data") }}', {
            month: month,
            year: year,
            client_type: calClientType
        }).done(function(data) {
            var calMap = {};
            data.forEach(function(item) {
                var day = parseInt(item.due_day.split('-')[2]);
                calMap[day] = parseInt(item.client_count);
            });
            renderCalendar(month, year, calMap);
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
            $('#calLoading').addClass('d-none');
            $('#calGrid').removeClass('d-none');
        });
    }

    function renderCalendar(month, year, calMap) {
        var firstDay = new Date(year, month - 1, 1);
        var daysInMonth = new Date(year, month, 0).getDate();
        var startDay = firstDay.getDay();
        var monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        var monthName = monthNames[month - 1];
        var dayNames = ['سبت', 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة'];
        var dayNamesShort = ['سب', 'أح', 'اث', 'ثل', 'أر', 'خم', 'جم'];
        var isMobile = window.innerWidth < 768;
        var useShortNames = isMobile;
        var names = useShortNames ? dayNamesShort : dayNames;

        $('#calTitle').text(monthName + ' ' + year);

        var html = '<table class="table table-bordered text-center calendar-grid mb-0"><thead><tr>';
        dayNames.forEach(function(d, i) {
            html += '<th class="text-muted fw-bold py-1 py-md-2 fs-8 fs-md-7">' + names[i] + '</th>';
        });
        html += '</tr></thead><tbody>';

        var today = new Date();
        var dayCount = 1;
        var totalCells = startDay + daysInMonth;
        var rows = Math.ceil(totalCells / 7);

        for (var row = 0; row < rows; row++) {
            html += '<tr>';
            for (var col = 0; col < 7; col++) {
                var cellIndex = row * 7 + col;
                if (cellIndex < startDay || dayCount > daysInMonth) {
                    html += '<td class="text-muted" style="opacity:0.2;">&nbsp;</td>';
                } else {
                    var hasBills = calMap[dayCount] > 0;
                    var isToday = (dayCount === today.getDate() && month === (today.getMonth() + 1) && year === today.getFullYear());
                    var dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(dayCount).padStart(2, '0');

                    html += '<td class="calendar-day' +
                        (hasBills ? ' has-bills' : '') +
                        (isToday ? ' today' : '') +
                        '" data-date="' + dateStr + '"' +
                        (hasBills ? ' data-has-bills="1"' : ' data-has-bills="0"') +
                        ' style="cursor:' + (hasBills ? 'pointer' : 'default') + '; height:' + (isMobile ? '55px' : '75px') + '; vertical-align:top; padding:' + (isMobile ? '3px' : '6px') + ';">';

                    html += '<div class="fw-bold day-number" style="font-size:' + (isMobile ? '0.85rem' : '1rem') + ';">' + dayCount + '</div>';
                    if (hasBills) {
                        var countLabel = isMobile ? calMap[dayCount] : calMap[dayCount] + ' ' +
                            "{{ trans('clients.clients') ?? 'زبون' }}";
                        html += '<div class="small badge badge-success mt-1" style="font-size:' + (isMobile ? '0.6rem' : '0.7rem') + ';padding:' + (isMobile ? '2px 4px' : '') + ';">' + countLabel + '</div>';
                    } else if (isToday) {
                        html += '<div class="small text-muted" style="font-size:0.7rem;">اليوم</div>';
                    }
                    html += '</td>';
                    dayCount++;
                }
            }
            html += '</tr>';
        }

        html += '</tbody></table>';

        $('#calGrid').html(html);
        $('#calLoading').addClass('d-none');
        $('#calGrid').removeClass('d-none');
    }

    function loadDayDetails(date) {
        $('#dayModalContent').addClass('d-none');
        $('#dayModalLoading').removeClass('d-none');
        $('#dayModalTitle').html('<i class="bi bi-calendar-event text-primary ms-1"></i> ' + date);
        $('#dayModalStats').addClass('d-none');
        $('#dayClientsContainer').empty();
        $('#selectAllDayClients').prop('checked', false);
        $('#sendDayReminders').prop('disabled', true);

        var calClientType = $('#calClientType').val();
        $.get('{{ route("admin.whatsapp.automation.calendar_day") }}', {
            date: date,
            client_type: calClientType
        }).done(function(res) {
            $('#dayModalLoading').addClass('d-none');
            $('#dayModalContent').removeClass('d-none');

            if (res.clients.length === 0) {
                $('#dayNoClients').removeClass('d-none');
                $('#dayModalStats').addClass('d-none');
            } else {
                $('#dayNoClients').addClass('d-none');
                $('#dayModalStats').removeClass('d-none');
                $('#dayModalTotal').text(res.clients.length);

                var monthNames = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                var html = '';

                res.clients.forEach(function(c) {
                    var typeIcon = c.client_type === 'satellite' ? '🛰️' : '🌐';
                    var typeLabel = c.client_type === 'satellite' ? 'ساتلايت' : 'إنترنت';
                    var typeColor = c.client_type === 'satellite' ? 'badge-light-info' : 'badge-light-success';

                    var invRows = '';
                    c.invoices.forEach(function(inv) {
                        var parts = inv.due_date.split('-');
                        var monthNum = parseInt(parts[1]);
                        var monthLabel = monthNames[monthNum];
                        var invTypeBadge = inv.type === 'اشتراك'
                            ? '<span class="badge badge-light-primary fs-8">📡 اشتراك</span>'
                            : '<span class="badge badge-light-warning fs-8">🔧 خدمة</span>';
                        var amountColor = parseFloat(inv.remaining_amount) > 0 ? 'text-danger' : 'text-success';
                        invRows += '<div class="d-flex align-items-center py-2 border-bottom border-gray-200">' +
                            '<div class="col-4 col-md-3">' +
                                '<span class="fw-semibold text-gray-800 fs-7">' + monthLabel + ' ' + parts[0] + '</span>' +
                            '</div>' +
                            '<div class="col-4 col-md-3">' +
                                invTypeBadge +
                            '</div>' +
                            '<div class="col-4 col-md-3 text-end">' +
                                '<span class="fw-bold ' + amountColor + ' fs-6">$' + parseFloat(inv.remaining_amount).toFixed(2) + '</span>' +
                            '</div>' +
                            '<div class="d-none d-md-block col-md-3 text-end text-muted small">' +
                                (inv.notes ? inv.notes : '') +
                            '</div>' +
                        '</div>';
                    });

                    html +=
                    '<div class="card card-flush shadow-sm mb-3 client-card" data-client-id="' + c.id + '">' +
                        '<div class="card-header bg-light py-3">' +
                            '<div class="d-flex align-items-center gap-3 w-100">' +
                                '<div class="form-check mb-0">' +
                                    '<input class="form-check-input client-checkbox" type="checkbox" value="' + c.id + '" id="client_' + c.id + '">' +
                                '</div>' +
                                '<div class="flex-grow-1">' +
                                    '<div class="d-flex align-items-center gap-2">' +
                                        '<span class="fs-5">' + typeIcon + '</span>' +
                                        '<label for="client_' + c.id + '" class="fw-bold text-gray-800 mb-0" style="cursor:pointer;">' + c.name + '</label>' +
                                        '<span class="badge ' + typeColor + ' fs-8">' + typeLabel + '</span>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="text-end">' +
                                    '<div class="d-flex align-items-center gap-3">' +
                                        '<span class="text-muted small d-none d-md-inline"><i class="bi bi-telephone"></i> ' + c.phone + '</span>' +
                                        '<span class="badge badge-light-warning rounded-pill fs-7 px-3 py-2">' +
                                            '<i class="bi bi-receipt"></i> ' + c.invoice_count +
                                        '</span>' +
                                        '<span class="fw-bold text-danger fs-6">$' + c.total_amount.toFixed(2) + '</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="card-body py-2 px-4">' +
                            '<div class="d-flex align-items-center py-1 border-bottom border-gray-300 mb-1">' +
                                '<div class="col-4 col-md-3"><span class="text-muted fs-8 fw-semibold">' +
                                    "{{ trans('clients.invoice_month') ?? 'الشهر' }}" +
                                '</span></div>' +
                                '<div class="col-4 col-md-3"><span class="text-muted fs-8 fw-semibold">' +
                                    "{{ trans('clients.invoice_type') ?? 'النوع' }}" +
                                '</span></div>' +
                                '<div class="col-4 col-md-3 text-end"><span class="text-muted fs-8 fw-semibold">' +
                                    "{{ trans('clients.total_amount') ?? 'المبلغ' }}" +
                                '</span></div>' +
                                '<div class="d-none d-md-block col-md-3 text-end text-muted fs-8 fw-semibold">' +
                                    "{{ trans('clients.notes') ?? 'ملاحظات' }}" +
                                '</div>' +
                            '</div>' +
                            invRows +
                        '</div>' +
                    '</div>';
                });

                $('#dayClientsContainer').html(html);
                updateSendButton();
            }

            $('#dayDetailModal').modal('show');
        }).fail(function() {
            $('#dayModalLoading').addClass('d-none');
            $('#dayModalContent').removeClass('d-none');
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ في تحميل البيانات" }}' });
        });
    }

    function updateSendButton() {
        var checked = $('#dayClientsContainer .client-checkbox:checked').length;
        $('#sendDayReminders').prop('disabled', checked === 0);
        if (checked > 0) {
            $('#sendDayReminders').html('<i class="bi bi-send"></i> {{ trans("clients.whatsapp_send_reminder") ?? "إرسال تذكير" }} <span class="badge badge-light ms-1">' + checked + '</span>');
        } else {
            $('#sendDayReminders').html('<i class="bi bi-send"></i> {{ trans("clients.whatsapp_send_reminder") ?? "إرسال تذكير للمحددين" }}');
        }
    }

    function sendDayReminders(clientIds) {
        $('#sendDayReminders').prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i> جاري الإرسال...');

        $.post('{{ route("admin.whatsapp.automation.calendar_send") }}', {
            _token: '{{ csrf_token() }}',
            client_ids: clientIds,
            template_type: 'reminder'
        }).done(function(res) {
            var msg = 'تم الإرسال: ' + res.sent + '<br>';
            if (res.failed > 0) {
                msg += 'فشل: ' + res.failed + '<br>';
                if (res.errors && res.errors.length > 0) {
                    msg += '<br>الأخطاء:<br>';
                    res.errors.forEach(function(e) { msg += '- ' + e + '<br>'; });
                }
            }
            Swal.fire({ icon: (res.failed > 0 && res.sent === 0) ? 'error' : 'success', html: msg });
            $('#dayDetailModal').modal('hide');
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ في الإرسال" }}' });
        }).always(function() {
            $('#sendDayReminders').prop('disabled', false)
                .html('<i class="bi bi-send"></i> {{ trans("clients.whatsapp_send_reminder") ?? "إرسال تذكير للمحددين" }}');
        });
    }
});
</script>

<style>
/* Font size helpers */
.fs-8 { font-size: 0.75rem !important; }
@media (min-width: 768px) {
    .fs-md-7 { font-size: 0.85rem !important; }
}

.calendar-grid th {
    background: #f8f9fa;
    font-size: 0.85rem;
}
.calendar-grid td {
    transition: all 0.15s ease;
    position: relative;
}
.calendar-grid td.has-bills:hover {
    background: #e8f5e9;
    transform: scale(1.05);
    z-index: 2;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.calendar-grid td.today {
    background: #fff8e1;
}
.calendar-grid td.today .day-number {
    color: #f57c00;
}
.calendar-grid .day-badge {
    font-size: 0.7rem;
}
.calendar-grid tr.no-border td {
    border-top: none !important;
    border-bottom: none !important;
    padding-top: 0 !important;
    background: transparent;
}
.spinner {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* --- Rule Cards --- */
.rule-card .card-header {
    border-bottom: 1px solid #f1f1f4;
}
.rule-card .form-switch {
    min-width: 44px;
}

/* --- Responsive Calendar --- */
.calendar-grid {
    table-layout: fixed;
    width: 100%;
}
.calendar-grid th,
.calendar-grid td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Small screens */
@media (max-width: 767.98px) {
    .calendar-grid th {
        font-size: 0.7rem !important;
        padding: 6px 2px !important;
    }
    .calendar-grid td {
        padding: 2px !important;
        height: auto !important;
        min-height: 45px;
    }
    .calendar-grid td .day-number {
        font-size: 0.75rem !important;
    }
    .calendar-grid td .badge {
        font-size: 0.55rem !important;
        padding: 1px 3px !important;
        line-height: 1.2;
    }
    .calendar-grid td.has-bills:hover {
        transform: none !important;
    }

    .monthly-calendar h3 {
        font-size: 1rem !important;
    }
    .monthly-calendar .btn-sm {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    #monthlyCalendar .row.mb-3 > div {
        margin-bottom: 0.5rem;
    }
    #monthlyCalendar .row.mb-3 > div:last-child {
        margin-bottom: 0;
    }
    #monthlyCalendar .d-flex.justify-content-end small {
        font-size: 0.7rem;
    }

    .monthly-calendar .d-flex.gap-4 {
        gap: 0.75rem !important;
        flex-wrap: wrap;
    }
    .monthly-calendar .d-flex.gap-4 span {
        font-size: 0.65rem;
    }
}

/* --- Modal Client Cards --- */
.client-card .card-header {
    min-height: auto;
}
.client-card .card-header .d-flex {
    flex-wrap: wrap;
    gap: 0.5rem !important;
}

@media (max-width: 767.98px) {
    .client-card .card-header .d-flex.align-items-center.gap-3 {
        flex-direction: column;
        align-items: flex-start !important;
    }
    .client-card .card-header .text-end {
        text-align: left !important;
        width: 100%;
    }
    .client-card .card-header .text-end .d-flex {
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    .client-card .card-header .text-end .d-flex span {
        font-size: 0.75rem;
    }
    .client-card .card-header .text-end .d-flex .badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
    }

    .client-card .card-body .d-flex.align-items-center {
        flex-wrap: wrap;
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }
    .client-card .card-body .d-flex.align-items-center > div {
        margin-bottom: 0.15rem;
    }
    .client-card .card-body .d-flex.align-items-center .fs-6 {
        font-size: 0.75rem !important;
    }
    .client-card .card-body .d-flex.align-items-center .badge {
        font-size: 0.6rem !important;
    }

    .client-card .card-body .d-flex.align-items-center.py-1 {
        font-size: 0.65rem !important;
    }

    #dayDetailModal .modal-footer .d-flex {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    #dayDetailModal .modal-footer .d-flex .form-check {
        margin-bottom: 0;
    }
    #dayDetailModal .modal-footer .d-flex button {
        width: 100%;
    }

    #dayDetailModal .modal-header {
        padding: 0.75rem !important;
    }
    #dayDetailModal .modal-header .modal-title {
        font-size: 0.9rem !important;
    }
    #dayDetailModal .modal-header .badge {
        font-size: 0.65rem !important;
        padding: 0.2rem 0.5rem !important;
    }

    #dayDetailModal .modal-body {
        padding: 0.75rem !important;
    }
}

@media (min-width: 768px) and (max-width: 991.98px) {
    .calendar-grid td {
        height: 60px !important;
        padding: 4px !important;
    }
    .calendar-grid td .day-number {
        font-size: 0.85rem !important;
    }
    .monthly-calendar h3 {
        font-size: 1.2rem !important;
    }
}

@media print {
    .calendar-grid td.has-bills {
        background: #f0f0f0 !important;
    }
}
</style>
@endsection
