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
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#clients-tab" role="tab">
                        👥 {{ trans('clients.whatsapp_clients') ?? 'الزبائن' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#calendar-tab" role="tab">
                        📅 {{ trans('clients.whatsapp_monthly_calendar') ?? 'التقويم الشهري' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tasks-tab" role="tab">
                        ⏰ {{ trans('clients.whatsapp_scheduled_tasks') ?? 'المهام المجدولة' }}
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- ════════════════════════════════════════════════ --}}
                {{--  TAB 1: CLIENTS                           --}}
                {{-- ════════════════════════════════════════════════ --}}
                <div class="tab-pane active" id="clients-tab" role="tabpanel">
                    {{-- Filter Bar --}}
                    <div class="row mb-4 g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.client_type') ?? 'نوع العميل' }}</label>
                            <select class="form-select form-select-sm" id="filterClientType">
                                <option value="all">{{ trans('clients.all') ?? 'الكل' }}</option>
                                <option value="internet">{{ trans('clients.internet') ?? 'إنترنت' }}</option>
                                <option value="satellite">{{ trans('clients.satellite') ?? 'ساتلايت' }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.status') ?? 'الحالة' }}</label>
                            <select class="form-select form-select-sm" id="filterStatus">
                                <option value="all">{{ trans('clients.all') ?? 'الكل' }}</option>
                                <option value="active">{{ trans('clients.active') ?? 'نشط' }}</option>
                                <option value="inactive">{{ trans('clients.inactive') ?? 'غير نشط' }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_min_unpaid') ?? 'حد أدنى' }}</label>
                            <input type="number" class="form-control form-control-sm" id="filterMinUnpaid" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-7">{{ trans('clients.search') ?? 'بحث' }}</label>
                            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="{{ trans('clients.search_name_phone') ?? 'اسم أو هاتف...' }}">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <label class="form-label fw-bold fs-7">&nbsp;</label>
                            <button class="btn btn-sm btn-primary w-100" id="filterClientsBtn">
                                <i class="bi bi-search"></i> {{ trans('clients.search') ?? 'بحث' }}
                            </button>
                        </div>
                    </div>

                    {{-- Loading --}}
                    <div class="text-center py-6 d-none" id="clientsLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    {{-- Clients Table --}}
                    <div class="table-responsive d-none" id="clientsTableWrapper">
                        <table class="table table-row-bordered table-align-middle" id="clientsTable">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800">
                                    <th width="40"><div class="form-check"><input class="form-check-input" type="checkbox" id="selectAllClients"></div></th>
                                    <th>{{ trans('clients.name') ?? 'الاسم' }}</th>
                                    <th>{{ trans('clients.phone') ?? 'الهاتف' }}</th>
                                    <th>{{ trans('clients.whatsapp_unpaid_bills_short') ?? 'فواتير' }}</th>
                                    <th>{{ trans('clients.total_amount') ?? 'المبلغ' }}</th>
                                    <th>{{ trans('clients.last_due') ?? 'آخر استحقاق' }}</th>
                                </tr>
                            </thead>
                            <tbody id="clientsBody">
                            </tbody>
                        </table>
                    </div>

                    {{-- Empty State --}}
                    <div class="text-center text-muted py-8 d-none" id="clientsEmpty">
                        <i class="bi bi-emoji-frown fs-3x text-gray-400"></i>
                        <p class="mt-3">{{ trans('clients.whatsapp_no_clients') ?? 'لا يوجد زبائن بالفلاتر المحددة' }}</p>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-between align-items-center mt-3 d-none" id="clientsPagination">
                        <small class="text-muted" id="clientsCount">0 {{ trans('clients.clients') ?? 'زبون' }}</small>
                        <div class="d-flex gap-2" id="clientsPages"></div>
                    </div>

                    {{-- Action Bar --}}
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top d-none" id="clientsActions">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="selectAllClientsBottom">
                            <label class="form-check-label text-muted" for="selectAllClientsBottom">{{ trans('clients.select_all') ?? 'تحديد الكل' }}</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" id="clientsSendNow" disabled>
                                <i class="bi bi-send"></i> {{ trans('clients.whatsapp_send_reminder') ?? 'إرسال تذكير' }}
                            </button>
                            <button class="btn btn-outline-primary btn-sm" id="clientsScheduleTask" disabled>
                                <i class="bi bi-clock"></i> {{ trans('clients.whatsapp_schedule') ?? 'جدولة مهمة' }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════════ --}}
                {{--  TAB 2: MONTHLY CALENDAR                       --}}
                {{-- ════════════════════════════════════════════════ --}}
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
                {{-- ════════════════════════════════════════════════ --}}
                {{--  TAB 3: SCHEDULED TASKS                      --}}
                {{-- ════════════════════════════════════════════════ --}}
                <div class="tab-pane" id="tasks-tab" role="tabpanel">
                    <div class="text-center py-6" id="tasksLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="tasksContainer" class="d-none">
                        {{-- Task cards rendered by JS --}}
                    </div>
                    <div class="text-center text-muted py-8 d-none" id="tasksEmpty">
                        <i class="bi bi-inbox fs-3x text-gray-400"></i>
                        <p class="mt-3">{{ trans('clients.whatsapp_no_tasks') ?? 'لا توجد مهام مجدولة' }}</p>
                        <p class="small">{{ trans('clients.whatsapp_no_tasks_hint') ?? 'اذهب إلى تبويب الزبائن، اختر زبائنك، وجدول مهمة جديدة' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════ --}}
{{--  MODAL: Day Customer Details                   --}}
{{-- ════════════════════════════════════════════════ --}} (Keen Design)     --}}
{{-- ════════════════════════════════════════════════ --}}
<div class="modal fade" id="dayDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold" id="dayModalTitle">
                    <i class="bi bi-calendar-event text-primary ms-1"></i> 
                    {{ trans('clients.whatsapp_monthly_calendar') ?? 'التقويم الشهري' }}
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
                    {{-- Client Cards Container --}}
                    <div id="dayClientsContainer"></div>
                    
                    {{-- Empty State --}}
                    <div class="text-center text-muted py-8 d-none" id="dayNoClients">
                        <i class="bi bi-emoji-frown fs-3x text-gray-400"></i>
                        <p class="mt-3 fs-6">{{ trans('clients.whatsapp_no_clients') ?? 'لا يوجد زبائن غير مدفوعين في هذا التاريخ' }}</p>
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

{{-- Send Reminder Modal --}}
<div class="modal fade" id="sendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold fs-6">
                    <i class="bi bi-send text-primary ms-1"></i>
                    {{ trans('clients.whatsapp_send_reminder') ?? 'إرسال تذكير' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sendModalClientIds">
                <div class="text-center mb-4">
                    <span class="badge badge-light-primary fs-6 px-4 py-2">
                        <i class="bi bi-people"></i>
                        <span id="sendModalClientCount">0</span> {{ trans('clients.clients') ?? 'زبون' }}
                    </span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</label>
                    <select class="form-select" id="sendModalTemplate">
                        @foreach($templates as $key => $tpl)
                            <option value="{{ $key }}">{{ $tpl['label'] ?? $key }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer py-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('clients.cancel') ?? 'إلغاء' }}</button>
                <button type="button" class="btn btn-primary" id="confirmSendBtn">
                    <i class="bi bi-send"></i> {{ trans('clients.whatsapp_confirm_send') ?? 'تأكيد الإرسال' }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Schedule Task Modal --}}
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold fs-6">
                    <i class="bi bi-clock text-primary ms-1"></i>
                    {{ trans('clients.whatsapp_schedule_task') ?? 'جدولة مهمة جديدة' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="scheduleClientIds">
                <div class="text-center mb-3">
                    <span class="badge badge-light-primary fs-6 px-4 py-2">
                        <i class="bi bi-people"></i>
                        <span id="scheduleClientCount">0</span> {{ trans('clients.clients') ?? 'زبون' }}
                    </span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7">{{ trans('clients.name') ?? 'اسم المهمة' }} *</label>
                    <input type="text" class="form-control" id="scheduleName" placeholder="{{ trans('clients.whatsapp_task_name_placeholder') ?? 'مثلاً: تذكير زبائن الإنترنت' }}">
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_time') ?? 'الوقت' }} *</label>
                        <input type="time" class="form-control" id="scheduleTime" value="09:00">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_template') ?? 'القالب' }}</label>
                        <select class="form-select" id="scheduleTemplate">
                            @foreach($templates as $key => $tpl)
                                <option value="{{ $key }}">{{ $tpl['label'] ?? $key }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7">{{ trans('clients.whatsapp_days') ?? 'أيام التشغيل' }} *</label>
                    <div class="d-flex gap-2 flex-wrap" id="scheduleDays">
                        @php $dayNames = ['سبت', 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة']; @endphp
                        @foreach($dayNames as $i => $day)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" value="{{ $i }}" id="day{{ $i }}" {{ $i < 5 ? 'checked' : '' }}>
                            <label class="form-check-label" for="day{{ $i }}">{{ $day }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer py-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('clients.cancel') ?? 'إلغاء' }}</button>
                <button type="button" class="btn btn-primary" id="confirmScheduleBtn">
                    <i class="bi bi-clock"></i> {{ trans('clients.whatsapp_schedule') ?? 'جدولة' }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function() {
    // ═══════════════════════════════════════════════════════════════
    //  
    // ═══════════════════════════════════════════════════════════════


    // ═══════════════════════════════════════════════════════════════
    //  TAB 2: MONTHLY CALENDAR
    // ═══════════════════════════════════════════════════════════════

    let currentMonth = {{ now()->month }};
    let currentYear = {{ now()->year }};

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
        const dir = $(this).data('dir');
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
        const date = $(this).data('date');
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

    // --- Send Reminders ---
    $('#sendDayReminders').on('click', function() {
        const selectedIds = [];
        $('#dayClientsContainer .client-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        if (selectedIds.length === 0) return;
        sendDayReminders(selectedIds);
    });

    // ═══════════════════════════════════════════════════════════════
    //  HELPER FUNCTIONS
    // ═══════════════════════════════════════════════════════════════

    function loadCalendar(month, year) {
        $('#calGrid').addClass('d-none');
        $('#calLoading').removeClass('d-none');
        $('#calTitle').text('...');

        let calClientType = $('#calClientType').val();
        $.get('{{ route("admin.whatsapp.automation.calendar_data") }}', {
            month: month,
            year: year,
            client_type: calClientType
        }).done(function(data) {
            // Build calendar map from response
            const calMap = {};
            data.forEach(function(item) {
                const day = parseInt(item.due_day.split('-')[2]);
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
        const firstDay = new Date(year, month - 1, 1);
        const daysInMonth = new Date(year, month, 0).getDate();
        const startDay = firstDay.getDay(); // 0=Sun
        const monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        const monthName = monthNames[month - 1];
        const dayNames = ['سبت', 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة'];
        const dayNamesShort = ['سب', 'أح', 'اث', 'ثل', 'أر', 'خم', 'جم'];
        const isMobile = window.innerWidth < 768;
        const useShortNames = isMobile;
        const names = useShortNames ? dayNamesShort : dayNames;

        // Update title
        $('#calTitle').text(monthName + ' ' + year);

        // Build grid
        let html = '<table class="table table-bordered text-center calendar-grid mb-0"><thead><tr>';
        dayNames.forEach(function(d, i) {
            html += '<th class="text-muted fw-bold py-1 py-md-2 fs-8 fs-md-7">' + names[i] + '</th>';
        });
        html += '</tr></thead><tbody>';

        const today = new Date();
        let dayCount = 1;
        const totalCells = startDay + daysInMonth;
        const rows = Math.ceil(totalCells / 7);

        for (let row = 0; row < rows; row++) {
            html += '<tr>';
            for (let col = 0; col < 7; col++) {
                const cellIndex = row * 7 + col;
                if (cellIndex < startDay || dayCount > daysInMonth) {
                    html += '<td class="text-muted" style="opacity:0.2;">&nbsp;</td>';
                } else {
                    const hasBills = calMap[dayCount] > 0;
                    const isToday = (dayCount === today.getDate() && month === (today.getMonth() + 1) && year === today.getFullYear());
                    const dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(dayCount).padStart(2, '0');

                    html += '<td class="calendar-day' +
                        (hasBills ? ' has-bills' : '') +
                        (isToday ? ' today' : '') +
                        '" data-date="' + dateStr + '"' +
                        (hasBills ? ' data-has-bills="1"' : ' data-has-bills="0"') +
                        ' style="cursor:' + (hasBills ? 'pointer' : 'default') + '; height:' + (isMobile ? '55px' : '75px') + '; vertical-align:top; padding:' + (isMobile ? '3px' : '6px') + ';">';

                    html += '<div class="fw-bold day-number" style="font-size:' + (isMobile ? '0.85rem' : '1rem') + ';">' + dayCount + '</div>';
                    if (hasBills) {
                        const countLabel = isMobile ? calMap[dayCount] : calMap[dayCount] + ' ' +
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

        let calClientType = $('#calClientType').val();
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

                const monthNames = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                const monthNamesShort = ['', 'ينا', 'فبر', 'مار', 'أبر', 'ماي', 'يون', 'يول', 'أغس', 'سبت', 'أكت', 'نوف', 'ديس'];
                let html = '';

                res.clients.forEach(function(c) {
                    const typeIcon = c.client_type === 'satellite' ? '🛰️' : '🌐';
                    const typeLabel = c.client_type === 'satellite' ? 'ساتلايت' : 'إنترنت';
                    const typeColor = c.client_type === 'satellite' ? 'badge-light-info' : 'badge-light-success';

                    // Build invoice rows
                    let invRows = '';
                    c.invoices.forEach(function(inv) {
                        const parts = inv.due_date.split('-');
                        const monthNum = parseInt(parts[1]);
                        const monthLabel = monthNames[monthNum];
                        const invTypeBadge = inv.type === 'اشتراك'
                            ? '<span class="badge badge-light-primary fs-8">📡 اشتراك</span>'
                            : '<span class="badge badge-light-warning fs-8">🔧 خدمة</span>';
                        const amountColor = parseFloat(inv.remaining_amount) > 0 ? 'text-danger' : 'text-success';
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

                    // Client card
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
                                        '<label for="client_' + c.id + '" class="fw-bold text-gray-800 mb-0 cursor-pointer" style="cursor:pointer;">' + c.name + '</label>' +
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
                                '</span></div>' +
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
        const checked = $('#dayClientsContainer .client-checkbox:checked').length;
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
            let msg = '✅ {{ trans("clients.whatsapp_sent_ok") ?? "تم الإرسال" }}: ' + res.sent + '<br>';
            if (res.failed > 0) {
                msg += '❌ {{ trans("clients.whatsapp_failed") ?? "فشل" }}: ' + res.failed + '<br>';
                if (res.errors && res.errors.length > 0) {
                    msg += '<br>{{ trans("clients.errors") ?? "الأخطاء" }}:<br>';
                    res.errors.forEach(function(e) { msg += '• ' + e + '<br>'; });
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

    // ═══════════════════════════════════════════════════════════════
    //  TAB 1: CLIENTS — Filter, Load, Send, Schedule
    // ═══════════════════════════════════════════════════════════════
    let currentPage = 1;
    let selectedClientIds = [];

    // --- Load clients on filter ---
    $('#filterClientsBtn').on('click', function() { loadClients(1); });
    $('#filterClientType, #filterStatus, #filterMinUnpaid').on('change', function() { loadClients(1); });
    $('#filterSearch').on('keypress', function(e) { if (e.which === 13) loadClients(1); });

    // --- Initial load on tab shown ---
    $('#clients-tab').on('shown.bs.tab', function() {
        if ($('#clientsBody').is(':empty')) {
            loadClients(1);
        }
    });
    if ($('#clients-tab').hasClass('active')) {
        loadClients(1);
    }

    // --- Select All ---
    $('#selectAllClients, #selectAllClientsBottom').on('change', function() {
        const checked = $(this).is(':checked');
        $('#clientsBody .client-checkbox').prop('checked', checked);
        $('#selectAllClients, #selectAllClientsBottom').prop('checked', checked);
        updateClientActions();
    });
    $(document).on('change', '.client-checkbox', function() {
        updateClientActions();
    });

    // --- Send Now ---
    $('#clientsSendNow').on('click', function() {
        const ids = getSelectedClientIds();
        if (ids.length === 0) return;
        // Show template selection modal
        $('#sendModalClientIds').val(JSON.stringify(ids));
        $('#sendModalClientCount').text(ids.length);
        $('#sendModal').modal('show');
    });

    // --- Schedule Task ---
    $('#clientsScheduleTask').on('click', function() {
        const ids = getSelectedClientIds();
        if (ids.length === 0) return;
        $('#scheduleClientIds').val(JSON.stringify(ids));
        $('#scheduleClientCount').text(ids.length);
        $('#scheduleModal').modal('show');
    });

    // --- Confirm Send ---
    $('#confirmSendBtn').on('click', function() {
        const ids = JSON.parse($('#sendModalClientIds').val());
        const template = $('#sendModalTemplate').val();
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i> {{ trans("clients.whatsapp_sending") ?? "جارٍ الإرسال..." }}');

        $.post('{{ route("admin.whatsapp.automation.send_now") }}', {
            _token: '{{ csrf_token() }}',
            client_ids: ids,
            template_type: template
        }).done(function(res) {
            let html = '✅ {{ trans("clients.whatsapp_sent_ok") ?? "تم الإرسال" }}: ' + res.sent + '<br>';
            if (res.failed > 0) {
                html += '❌ {{ trans("clients.whatsapp_failed") ?? "فشل" }}: ' + res.failed + '<br>';
                if (res.errors && res.errors.length > 0) {
                    html += '<br>{{ trans("clients.errors") ?? "الأخطاء" }}:<br>';
                    res.errors.forEach(function(e) { html += '• ' + e + '<br>'; });
                }
            }
            Swal.fire({ icon: (res.failed > 0 && res.sent === 0) ? 'error' : 'success', html: html });
            $('#sendModal').modal('hide');
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ في الإرسال" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-send"></i> {{ trans("clients.whatsapp_confirm_send") ?? "تأكيد الإرسال" }}');
        });
    });

    // --- Confirm Schedule ---
    $('#confirmScheduleBtn').on('click', function() {
        const ids = JSON.parse($('#scheduleClientIds').val());
        const name = $('#scheduleName').val();
        const time = $('#scheduleTime').val();
        const template = $('#scheduleTemplate').val();
        const days = [];
        $('#scheduleDays input:checked').each(function() { days.push(parseInt($(this).val())); });
        const btn = $(this);

        if (!name || !time || days.length === 0) {
            Swal.fire({ icon: 'warning', text: '{{ trans("clients.whatsapp_fill_required") ?? "يرجى تعبئة الحقول المطلوبة" }}' });
            return;
        }

        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i> {{ trans("clients.whatsapp_saving") ?? "جارٍ الحفظ..." }}');

        $.post('{{ route("admin.whatsapp.automation.schedule_task") }}', {
            _token: '{{ csrf_token() }}',
            name: name,
            client_ids: ids,
            time: time,
            days: days,
            template_type: template
        }).done(function(res) {
            Swal.fire({ icon: 'success', text: res.message });
            $('#scheduleModal').modal('hide');
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-clock"></i> {{ trans("clients.whatsapp_schedule") ?? "جدولة" }}');
        });
    });

    // --- Helper: Get selected client IDs ---
    function getSelectedClientIds() {
        const ids = [];
        $('#clientsBody .client-checkbox:checked').each(function() {
            ids.push(parseInt($(this).val()));
        });
        return ids;
    }

    function updateClientActions() {
        const checked = $('#clientsBody .client-checkbox:checked').length;
        $('#clientsSendNow').prop('disabled', checked === 0);
        $('#clientsScheduleTask').prop('disabled', checked === 0);
        const label = checked > 0 ? ' (' + checked + ')' : '';
        $('#clientsSendNow').html('<i class="bi bi-send"></i> {{ trans("clients.whatsapp_send_reminder") ?? "إرسال تذكير" }}' + label);
        $('#clientsScheduleTask').html('<i class="bi bi-clock"></i> {{ trans("clients.whatsapp_schedule") ?? "جدولة مهمة" }}' + label);
    }

    function loadClients(page) {
        currentPage = page;
        $('#clientsTableWrapper').addClass('d-none');
        $('#clientsEmpty').addClass('d-none');
        $('#clientsPagination').addClass('d-none');
        $('#clientsActions').addClass('d-none');
        $('#clientsLoading').removeClass('d-none');

        const params = {
            page: page,
            client_type: $('#filterClientType').val(),
            status: $('#filterStatus').val(),
            min_unpaid: $('#filterMinUnpaid').val(),
            search: $('#filterSearch').val(),
        };

        $.get('{{ route("admin.whatsapp.automation.clients") }}', params)
        .done(function(res) {
            $('#clientsLoading').addClass('d-none');

            if (res.data.length === 0) {
                $('#clientsEmpty').removeClass('d-none');
                return;
            }

            $('#clientsTableWrapper').removeClass('d-none');
            $('#clientsPagination').removeClass('d-none');
            $('#clientsActions').removeClass('d-none');

            let html = '';
            res.data.forEach(function(c) {
                const icon = c.client_type === 'satellite' ? '🛰️' : '🌐';
                const statusBadge = c.is_active === '1'
                    ? '<span class="badge badge-light-success fs-8">{{ trans("clients.active") ?? "نشط" }}</span>'
                    : '<span class="badge badge-light-secondary fs-8">{{ trans("clients.inactive") ?? "غير نشط" }}</span>';
                html += '<tr>' +
                    '<td><div class="form-check"><input class="form-check-input client-checkbox" type="checkbox" value="' + c.id + '"></div></td>' +
                    '<td><span class="fw-bold">' + icon + ' ' + c.name + '</span><br>' + statusBadge + '</td>' +
                    '<td dir="ltr"><small>' + c.phone + '</small></td>' +
                    '<td><span class="badge badge-warning">' + c.invoice_count + '</span></td>' +
                    '<td class="fw-bold text-danger">$' + c.total_unpaid.toFixed(2) + '</td>' +
                    '<td><small class="text-muted">' + (c.last_due_date || '-') + '</small></td>' +
                    '</tr>';
            });
            $('#clientsBody').html(html);
            $('#clientsCount').text(res.total + ' {{ trans("clients.clients") ?? "زبون" }}');

            // Pagination
            let pagesHtml = '';
            const lastPage = res.last_page;
            for (let i = 1; i <= lastPage; i++) {
                pagesHtml += '<button class="btn btn-sm ' + (i === res.current_page ? 'btn-primary' : 'btn-light') + ' page-btn" data-page="' + i + '">' + i + '</button>';
            }
            $('#clientsPages').html(pagesHtml);
            $('#selectAllClients, #selectAllClientsBottom').prop('checked', false);
            updateClientActions();
        }).fail(function() {
            $('#clientsLoading').addClass('d-none');
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ في تحميل البيانات" }}' });
        });
    }

    $(document).on('click', '.page-btn', function() {
        loadClients(parseInt($(this).data('page')));
    });

    // ═══════════════════════════════════════════════════════════════
    //  TAB 3: TASKS — Load, Toggle, Edit, Delete
    // ═══════════════════════════════════════════════════════════════
    function loadTasks() {
        $('#tasksLoading').removeClass('d-none');
        $('#tasksContainer').addClass('d-none');
        $('#tasksEmpty').addClass('d-none');

        $.get('{{ route("admin.whatsapp.automation.tasks") }}')
        .done(function(res) {
            $('#tasksLoading').addClass('d-none');

            if (res.tasks.length === 0) {
                $('#tasksEmpty').removeClass('d-none');
                return;
            }

            const dayNames = ['سبت', 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة'];
            let html = '';

            res.tasks.forEach(function(task) {
                const daysLabels = task.days.map(function(d) { return dayNames[d]; });
                const daysStr = daysLabels.length === 7 ? '{{ trans("clients.every_day") ?? "كل يوم" }}' : daysLabels.join('، ');
                const filterStr = task.filter_client_type !== 'all'
                    ? (task.filter_client_type === 'internet' ? '🌐 إنترنت' : '🛰️ ساتلايت')
                    : '{{ trans("clients.all") ?? "الكل" }}';

                const templateLabel = task.template;
                let infoHtml = '<span class="text-muted"><i class="bi bi-clock"></i> ' + task.time + '</span>';
                infoHtml += ' | <span class="text-muted"><i class="bi bi-calendar"></i> ' + daysStr + '</span>';
                infoHtml += ' | <span class="text-muted"><i class="bi bi-funnel"></i> ' + filterStr + '</span>';
                infoHtml += ' | <span class="text-muted"><i class="bi bi-file-text"></i> ' + templateLabel + '</span>';

                let statsHtml = '';
                if (task.last_run) {
                    statsHtml += '<span class="text-muted small"><i class="bi bi-clock-history"></i> {{ trans("clients.last_run") ?? "آخر تشغيل" }}: ' + task.last_run + '</span>';
                } else {
                    statsHtml += '<span class="text-muted small">{{ trans("clients.whatsapp_never_run") ?? "لم يتم تشغيلها بعد" }}</span>';
                }
                statsHtml += ' | <span class="small text-success">✅ ' + task.total_sent + '</span>';
                if (task.total_failed > 0) {
                    statsHtml += ' <span class="small text-danger">❌ ' + task.total_failed + '</span>';
                }

                const isRemindBefore = task.id === 'whatsapp_remind_before';

                html += '<div class="card card-flush shadow-sm mb-3 task-card" data-task-id="' + task.id + '">' +
                    '<div class="card-header py-3">' +
                    '<div class="d-flex align-items-center gap-3 w-100">' +
                    '<div class="flex-grow-1">' +
                    '<div class="d-flex align-items-center gap-2">' +
                    '<span class="fw-bold text-gray-800">' + task.label + '</span>' +
                    '<span class="badge ' + (task.enabled ? 'badge-success' : 'badge-secondary') + ' task-status-badge">' +
                    (task.enabled ? '🟢 {{ trans("clients.active") ?? "مفعل" }}' : '⚪ {{ trans("clients.inactive") ?? "معطل" }}') +
                    '</span>' +
                    '</div>' +
                    '</div>' +
                    '<div class="d-flex gap-1">' +
                    '<button class="btn btn-sm btn-light toggle-task" data-id="' + task.id + '">' +
                    (task.enabled ? '<i class="bi bi-pause"></i>' : '<i class="bi bi-play"></i>') +
                    '</button>' +
                    '<button class="btn btn-sm btn-light-primary run-task" data-id="' + task.id + '" data-command="' + (task.id === 'whatsapp_remind_before' ? 'whatsapp:reminders' : 'whatsapp:reminders') + '">' +
                    '<i class="bi bi-play-fill"></i>' +
                    '</button>' +
                    (!isRemindBefore ? '<button class="btn btn-sm btn-light-danger delete-task" data-id="' + task.id + '"><i class="bi bi-trash"></i></button>' : '') +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="card-body py-3">' +
                    '<div class="d-flex justify-content-between align-items-center">' +
                    '<div>' + infoHtml + '</div>' +
                    '<div class="text-end">' + statsHtml + '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });

            $('#tasksContainer').html(html).removeClass('d-none');
        }).fail(function() {
            $('#tasksLoading').addClass('d-none');
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ في تحميل المهام" }}' });
        });
    }

    // --- Load tasks on tab shown ---
    $('#tasks-tab').on('shown.bs.tab', function() {
        loadTasks();
    });

    // --- Toggle Task ---
    $(document).on('click', '.toggle-task', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');

        $.post('{{ url("admin/whatsapp/automation") }}/' + id + '/toggle', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            loadTasks();
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
            btn.prop('disabled', false);
        });
    });

    // --- Run Task ---
    $(document).on('click', '.run-task', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');

        $.post('{{ url("admin/whatsapp/automation") }}/' + id + '/run', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            Swal.fire({ icon: res.success ? 'success' : 'error', text: res.output || res.error });
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i>');
        });
    });

    // --- Delete Task ---
    $(document).on('click', '.delete-task', function() {
        const id = $(this).data('id');
        const card = $(this).closest('.task-card');

        Swal.fire({
            title: '{{ trans("clients.whatsapp_confirm_delete") ?? "تأكيد الحذف" }}',
            text: '{{ trans("clients.whatsapp_delete_task_confirm") ?? "هل أنت متأكد من حذف هذه المهمة؟" }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ trans("clients.yes") ?? "نعم" }}',
            cancelButtonText: '{{ trans("clients.cancel") ?? "إلغاء" }}',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '{{ url("admin/whatsapp/automation/tasks") }}/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' }
            }).done(function() {
                card.fadeOut(300, function() { $(this).remove(); });
                Swal.fire({ icon: 'success', text: '{{ trans("clients.whatsapp_task_deleted") ?? "تم حذف المهمة" }}' });
            }).fail(function() {
                Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
            });
        });
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

/* ════════════════════════════════════════════════════
   📱 RESPONSIVE — Calendar & Modal
   ════════════════════════════════════════════════════ */

/* --- Calendar Grid --- */
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

/* Small screens (<768px) */
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

    /* Month navigation */
    .monthly-calendar h3 {
        font-size: 1rem !important;
    }
    .monthly-calendar .btn-sm {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    /* Filter bar */
    #monthlyCalendar .row.mb-3 > div {
        margin-bottom: 0.5rem;
    }
    #monthlyCalendar .row.mb-3 > div:last-child {
        margin-bottom: 0;
    }
    #monthlyCalendar .d-flex.justify-content-end small {
        font-size: 0.7rem;
    }

    /* Legend */
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

    /* Invoice rows in modal */
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

    /* Column headers in card body */
    .client-card .card-body .d-flex.align-items-center.py-1 {
        font-size: 0.65rem !important;
    }

    /* Modal footer */
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

    /* Modal header */
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

    /* Modal body */
    #dayDetailModal .modal-body {
        padding: 0.75rem !important;
    }
}

/* Medium screens (768-991px) */
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

/* Print */
@media print {
    .calendar-grid td.has-bills {
        background: #f0f0f0 !important;
    }
}

</style>
@endsection
