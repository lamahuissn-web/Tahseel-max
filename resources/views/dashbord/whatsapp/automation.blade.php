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
                    <a class="nav-link active" data-bs-toggle="tab" href="#rules-tab" role="tab">
                        📋 {{ trans('clients.whatsapp_automation_rules') ?? 'قواعد التشغيل الآلي' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#calendar-tab" role="tab">
                        📅 {{ trans('clients.whatsapp_monthly_calendar') ?? 'التقويم الشهري' }}
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- ════════════════════════════════════════════════ --}}
                {{--  TAB 1: RULES TABLE                           --}}
                {{-- ════════════════════════════════════════════════ --}}
                <div class="tab-pane active" id="rules-tab" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-align-middle">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800">
                                    <th>{{ trans('clients.whatsapp_rule') ?? 'القاعدة' }}</th>
                                    <th>{{ trans('clients.whatsapp_command') ?? 'الأمر' }}</th>
                                    <th>{{ trans('clients.status') ?? 'الحالة' }}</th>
                                    <th>{{ trans('clients.whatsapp_actions') ?? 'إجراءات' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ app()->getLocale() == 'ar' ? $rule['label'] : $rule['label_en'] }}</span>
                                        @if(!empty($rule['filter_summary']) && $rule['filter_summary'] !== 'الكل')
                                            <br><small class="text-muted">{{ $rule['filter_summary'] }}</small>
                                        @endif
                                    </td>
                                    <td><code>{{ $rule['command'] }}</code></td>
                                    <td>
                                        <span class="badge {{ $rule['enabled'] ? 'badge-success' : 'badge-secondary' }}" id="status-{{ $rule['id'] }}">
                                            {{ $rule['enabled'] ? '🟢 ' . (trans('clients.active') ?? 'مفعل') : '⚪ ' . (trans('clients.inactive') ?? 'معطل') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm {{ $rule['enabled'] ? 'btn-warning' : 'btn-success' }} toggle-rule"
                                                data-id="{{ $rule['id'] }}">
                                            {{ $rule['enabled'] ? (trans('clients.whatsapp_disable') ?? 'تعطيل') : (trans('clients.whatsapp_enable') ?? 'تفعيل') }}
                                        </button>
                                        <button class="btn btn-sm btn-primary run-rule" data-id="{{ $rule['command'] }}">
                                            <i class="bi bi-play-fill"></i> {{ trans('clients.whatsapp_run_now') ?? 'تشغيل الآن' }}
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-6">
                                        {{ trans('clients.whatsapp_no_rules') ?? 'لا توجد قواعد تشغيل آلي' }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════ --}}
{{--  MODAL: Day Customer Details                   --}}
{{-- ════════════════════════════════════════════════ --}}
<div class="modal fade" id="dayDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dayModalTitle">📅 زبائن</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4 d-none" id="dayModalLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="dayModalContent">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-align-middle" id="dayClientsTable">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800">
                                    <th width="40">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllDayClients">
                                        </div>
                                    </th>
                                    <th>{{ trans('clients.name') ?? 'الاسم' }}</th>
                                    <th>{{ trans('clients.phone') ?? 'الهاتف' }}</th>
                                    <th>{{ trans('clients.whatsapp_unpaid_bills_short') ?? 'عدد الفواتير' }}</th>
                                    <th>{{ trans('clients.total_amount') ?? 'المبلغ' }}</th>
                                    <th>{{ trans('clients.whatsapp_actions') ?? 'إجراءات' }}</th>
                                </tr>
                            </thead>
                            <tbody id="dayClientsBody">
                                {{-- Filled by JS --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center text-muted py-4 d-none" id="dayNoClients">
                        <i class="bi bi-emoji-frown fs-1"></i>
                        <p class="mt-2">{{ trans('clients.whatsapp_no_clients') ?? 'لا يوجد زبائن غير مدفوعين في هذا التاريخ' }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ trans('clients.close') ?? 'إغلاق' }}
                </button>
                <button type="button" class="btn btn-primary" id="sendDayReminders" disabled>
                    <i class="bi bi-send"></i> {{ trans('clients.whatsapp_send_reminder') ?? 'إرسال تذكير للمحددين' }}
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
    //  TAB 1: RULES — Toggle & Run
    // ═══════════════════════════════════════════════════════════════
    $('.toggle-rule').on('click', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        $.post('{{ url("admin/whatsapp/automation") }}/' + id + '/toggle', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            if (res.enabled) {
                $('#status-' + id).removeClass('badge-secondary').addClass('badge-success')
                    .text('🟢 {{ trans("clients.active") ?? "مفعل" }}');
                btn.removeClass('btn-success').addClass('btn-warning')
                    .text('{{ trans("clients.whatsapp_disable") ?? "تعطيل" }}');
            } else {
                $('#status-' + id).removeClass('badge-success').addClass('badge-secondary')
                    .text('⚪ {{ trans("clients.inactive") ?? "معطل" }}');
                btn.removeClass('btn-warning').addClass('btn-success')
                    .text('{{ trans("clients.whatsapp_enable") ?? "تفعيل" }}');
            }
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false);
        });
    });

    $('.run-rule').on('click', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        Swal.fire({ icon: 'info', text: '{{ trans("clients.whatsapp_running") ?? "جارٍ التشغيل..." }}', showConfirmButton: false });
        $.post('{{ url("admin/whatsapp/automation") }}/' + id + '/run', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            Swal.fire({ icon: res.success ? 'success' : 'error', text: res.output || res.error });
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i> {{ trans("clients.whatsapp_run_now") ?? "تشغيل الآن" }}');
        });
    });

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
        $('#dayClientsBody .client-checkbox').prop('checked', $(this).is(':checked'));
        updateSendButton();
    });

    $(document).on('change', '.client-checkbox', function() {
        updateSendButton();
    });

    // --- Send Reminders ---
    $('#sendDayReminders').on('click', function() {
        const selectedIds = [];
        $('#dayClientsBody .client-checkbox:checked').each(function() {
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

        // Update title
        $('#calTitle').text(monthName + ' ' + year);

        // Build grid
        let html = '<table class="table table-bordered text-center calendar-grid mb-0"><thead><tr>';
        dayNames.forEach(function(d) {
            html += '<th class="text-muted fw-bold py-2">' + d + '</th>';
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
                        ' style="cursor:' + (hasBills ? 'pointer' : 'default') + '; height:75px; vertical-align:top; padding:6px;">';

                    html += '<div class="fw-bold day-number" style="font-size:1rem;">' + dayCount + '</div>';
                    if (hasBills) {
                        html += '<div class="small badge badge-success mt-1">' + calMap[dayCount] + ' ' +
                            "{{ trans('clients.clients') ?? 'زبون' }}" + '</div>';
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
        $('#dayModalTitle').text('📅 ' + date);
        $('#dayClientsBody').empty();
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
                $('#dayClientsTable').addClass('d-none');
            } else {
                $('#dayNoClients').addClass('d-none');
                $('#dayClientsTable').removeClass('d-none');

                let html = '';
                res.clients.forEach(function(c) {
                    const monthNames = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                    let invoiceMonths = '';
                    if (c.invoices.length > 0) {
                        const months = c.invoices.map(function(inv) {
                            const parts = inv.due_date.split('-');
                            return monthNames[parseInt(parts[1])] + ' ' + parts[0];
                        });
                        invoiceMonths = months.join(', ');
                    }
                    const invoiceList = c.invoices.map(function(inv) {
                        const parts = inv.due_date.split('-');
                        return inv.type + ' ' + monthNames[parseInt(parts[1])] + ': ' + inv.remaining_amount.toFixed(2) + '$';
                    }).join('<br>');
                    const typeIcon = c.client_type === 'satellite' ? '🛰️' : '🌐';
                    html += '<tr>' +
                        '<td><div class="form-check"><input class="form-check-input client-checkbox" type="checkbox" value="' + c.id + '"></div></td>' +
                        '<td class="fw-bold">' + typeIcon + ' ' + c.name + '</td>' +
                        '<td dir="ltr"><small>' + c.phone + '</small></td>' +
                        '<td><span class="badge badge-warning">' + c.invoice_count + '</span></td>' +
                        '<td class="fw-bold">' + c.total_amount.toFixed(2) + ' $</td>' +
                        '<td><button class="btn btn-sm btn-light" title="' + invoiceList.replace(/<br>/g, '\n') + '" onclick="Swal.fire({icon:\'info\',title:\'تفاصيل الفواتير\',html:\'' + invoiceList.replace(/'/g, "\\'") + '\'})"><i class="bi bi-eye"></i></button></td>' +
                        '</tr>' +
                        (c.invoices.length > 1 ? '<tr class="no-border"><td></td><td colspan="5" class="text-muted small py-0">📄 ' + invoiceMonths + '</td></tr>' : '');
                });
                $('#dayClientsBody').html(html);
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
        const checked = $('#dayClientsBody .client-checkbox:checked').length;
        $('#sendDayReminders').prop('disabled', checked === 0);
        if (checked > 0) {
            $('#sendDayReminders').text('{{ trans("clients.whatsapp_send_reminder") ?? "إرسال تذكير" }} (' + checked + ')');
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
            let msg = '✅ تم الإرسال: ' + res.sent + '<br>';
            if (res.failed > 0) {
                msg += '❌ فشل: ' + res.failed + '<br>';
                if (res.errors && res.errors.length > 0) {
                    msg += '<br>الأخطاء:<br>';
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
});
</script>

<style>
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
</style>
@endsection
