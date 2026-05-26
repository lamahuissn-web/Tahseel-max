@extends('dashbord.layouts.master')

@section('css')
<style>
.wa-card { border-radius: 10px; overflow: hidden; border: 1px solid #e8e8e8; }
.wa-card-header { padding: 12px 16px; font-weight: 600; font-size: 14px; border-bottom: 1px solid #e8e8e8; }
.wa-card-body { padding: 16px; }
.wa-status-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-left: 6px; }
.wa-status-dot.connected { background: #198754; }
.wa-status-dot.disconnected { background: #dc3545; }
.wa-qr-container { text-align: center; padding: 20px; }
.wa-qr-container img { max-width: 250px; border-radius: 8px; border: 1px solid #e8e8e8; }
.wa-template-preview { background: #f8f9fa; border: 1px solid #e8e8e8; border-radius: 8px; padding: 14px; margin-top: 10px; white-space: pre-wrap; font-size: 14px; direction: rtl; }
.wa-log-table { font-size: 13px; }
.wa-log-table td { padding: 8px 12px; vertical-align: middle; }
.wa-log-table tr:not(:last-child) { border-bottom: 1px solid #f5f5f5; }
.wa-log-status.sent { color: #198754; }
.wa-log-status.failed { color: #dc3545; }
.wa-test-result { margin-top: 10px; padding: 10px; border-radius: 8px; display: none; }
.wa-test-result.success { background: #d1e7dd; color: #0f5132; }
.wa-test-result.error { background: #f8d7da; color: #842029; }
.wa-preview-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.wa-preview-table th { background: #f8f9fa; padding: 10px 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; }
.wa-preview-table td { padding: 10px 12px; border-bottom: 1px solid #e8e8e8; vertical-align: top; }
.wa-preview-table tr:hover { background: #f8f9fa; }
.wa-preview-table .invoice-line { display: block; margin-bottom: 4px; font-size: 12px; color: #555; }
.wa-preview-summary { margin-top: 12px; padding: 10px; background: #e7f5ff; border-radius: 8px; font-weight: 600; text-align: center; }
.wa-result-item { padding: 8px 12px; border-radius: 6px; margin-bottom: 6px; font-size: 13px; }
.wa-result-item.sent { background: #d1e7dd; color: #0f5132; }
.wa-result-item.failed { background: #f8d7da; color: #842029; }
.wa-month-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.wa-month-btn { padding: 14px 10px; border: 2px solid #e8e8e8; border-radius: 10px; background: #fff; cursor: pointer; text-align: center; transition: all 0.2s; user-select: none; }
.wa-month-btn:hover { border-color: #0d6efd; background: #f0f7ff; transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.wa-month-btn.active { border-color: #0d6efd; background: #e7f1ff; font-weight: 600; }
.wa-month-btn.has-invoices { border-color: #ffc107; background: #fff8e1; }
.wa-month-btn.has-invoices:hover { border-color: #ff9800; background: #fff3cd; }
.wa-month-btn .month-name { font-weight: 600; font-size: 14px; direction: rtl; }
.wa-month-btn .month-count { font-size: 11px; color: #666; margin-top: 4px; direction: rtl; }
.wa-month-btn.has-invoices .month-count { color: #e65100; font-weight: 500; }
.wa-year-selector { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 14px; padding: 8px; background: #f8f9fa; border-radius: 8px; }
.wa-year-selector select { padding: 6px 14px; border-radius: 6px; border: 1px solid #dee2e6; font-size: 14px; font-weight: 600; cursor: pointer; }
.wa-year-selector i { color: #6c757d; cursor: pointer; font-size: 16px; }
.wa-year-selector i:hover { color: #0d6efd; }
.wa-day-calendar { width: 100%; border-collapse: separate; border-spacing: 3px; margin: 10px 0; }
.wa-day-calendar th { padding: 6px 4px; text-align: center; font-size: 11px; color: #888; font-weight: 600; text-transform: uppercase; }
.wa-day-calendar td { padding: 6px 4px; text-align: center; cursor: pointer; position: relative; min-width: 36px; height: 36px; border-radius: 6px; font-size: 13px; font-weight: 500; transition: all 0.15s; }
.wa-day-calendar td:hover:not(.empty) { background: #e7f1ff; }
.wa-day-calendar td.today { box-shadow: inset 0 0 0 2px #0d6efd; }
.wa-day-calendar td.selected { background: #0d6efd; color: #fff; }
.wa-day-calendar td.selected:hover { background: #0b5ed7; }
.wa-day-calendar td.has-invoices { background: #fff3cd; font-weight: 700; }
.wa-day-calendar td.has-invoices:hover { background: #ffe69c; }
.wa-day-calendar td.selected.has-invoices { background: #0d6efd; color: #fff; }
.wa-day-calendar td.empty { background: transparent; cursor: default; }
.wa-day-calendar td.empty:hover { background: transparent; }
.wa-day-badge { position: absolute; top: 1px; right: 1px; background: #e65100; color: #fff; border-radius: 50%; min-width: 16px; height: 16px; font-size: 9px; display: flex; align-items: center; justify-content: center; padding: 0 3px; line-height: 1; }
.wa-day-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; padding: 6px 0; }
.wa-day-nav button { border: none; background: none; cursor: pointer; font-size: 18px; color: #6c757d; padding: 4px 10px; border-radius: 6px; }
.wa-day-nav button:hover { background: #f0f0f0; color: #0d6efd; }
.wa-day-nav .month-title { font-weight: 700; font-size: 15px; text-align: center; }
.wa-back-btn { display: inline-flex; align-items: center; gap: 4px; cursor: pointer; color: #0d6efd; font-size: 13px; margin-bottom: 10px; border: none; background: none; padding: 4px 8px; border-radius: 4px; }
.wa-back-btn:hover { background: #e7f1ff; text-decoration: none; }
.wa-day-hint { text-align: center; color: #888; font-size: 13px; margin-top: 8px; }
.wa-client-check { width: 16px; height: 16px; cursor: pointer; vertical-align: middle; }
.wa-phone-warning { color: #dc3545; font-size: 14px; margin-left: 4px; cursor: help; }
.wa-selection-controls { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 12px; padding: 10px; background: #f8f9fa; border-radius: 8px; }
.wa-selection-controls #wa_selected_count { font-weight: 600; font-size: 13px; color: #333; }
.wa-preview-table th:first-child, .wa-preview-table td:first-child { width: 40px; text-align: center; }
</style>
@endsection

@section('content')
<div class="app-container container-xxl">
    <div class="row g-4">

        {{-- Connection Status --}}
        <div class="col-12">
            <div class="wa-card shadow-sm">
                <div class="wa-card-header bg-light">
                    <i class="bi bi-link-45deg text-primary me-2"></i> {{ trans('clients.whatsapp_connection') }}
                </div>
                <div class="wa-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="wa-status-dot {{ $status['connected'] ? 'connected' : 'disconnected' }}"></span>
                                <span class="fw-semibold">
                                    {{ $status['connected'] ? trans('clients.whatsapp_connected') : trans('clients.whatsapp_disconnected') }}
                                </span>
                            </div>
                            @if($status['connected'] && $status['phone'])
                                <div class="text-muted" style="font-size: 13px;">
                                    <i class="bi bi-phone me-1"></i> {{ $status['phone'] }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 text-md-end mt-2 mt-md-0">
                            <button class="btn btn-outline-primary btn-sm" onclick="restartWhatsApp()">
                                <i class="bi bi-arrow-clockwise me-1"></i> {{ trans('clients.whatsapp_restart') }}
                            </button>
                        </div>
                    </div>

                    @if(!$status['connected'])
                        <div class="wa-qr-container mt-3">
                            @if($qr['qr'])
                                <img src="{{ $qr['qr'] }}" alt="QR Code">
                                <div class="text-muted mt-2" style="font-size: 13px;">
                                    <i class="bi bi-qr-code me-1"></i> {{ trans('clients.whatsapp_qr_scan') }}
                                </div>
                            @else
                                <div class="text-muted py-4">
                                    <i class="bi bi-hourglass-split fs-1"></i>
                                    <p class="mt-2">{{ trans('clients.whatsapp_connecting') }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Reminder Settings --}}
        <div class="col-12">
            <div class="wa-card shadow-sm">
                <div class="wa-card-header bg-light">
                    <i class="bi bi-gear text-warning me-2"></i> {{ trans('clients.whatsapp_reminder_settings') }}
                </div>
                <div class="wa-card-body">
                    <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled"
                                        {{ $settings['whatsapp_enabled'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="whatsapp_enabled">
                                        {{ trans('clients.whatsapp_enabled_label') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ trans('clients.whatsapp_remind_before') }}</label>
                                <input type="number" class="form-control" name="whatsapp_remind_before" value="{{ $settings['whatsapp_remind_before'] }}" min="0" max="30">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="whatsapp_remind_on_due" id="whatsapp_remind_on_due"
                                        {{ $settings['whatsapp_remind_on_due'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="whatsapp_remind_on_due">
                                        {{ trans('clients.whatsapp_remind_on_due') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ trans('clients.whatsapp_remind_after') }}</label>
                                <input type="text" class="form-control" name="whatsapp_remind_after" value="{{ $settings['whatsapp_remind_after'] }}" placeholder="1,3,7">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-check-circle me-1"></i> {{ trans('clients.save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reminders Preview --}}
        <div class="col-12">
            <div class="wa-card shadow-sm">
                <div class="wa-card-header bg-light d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-eye text-info me-2"></i> {{ trans('clients.whatsapp_reminders_preview') }}</span>
                    <div>
                        <button class="btn btn-outline-info btn-sm me-2" onclick="loadRemindersPreview()">
                            <i class="bi bi-arrow-clockwise me-1"></i> {{ trans('clients.whatsapp_refresh_preview') }}
                        </button>
                        <button class="btn btn-success btn-sm" id="btn_send_reminders" onclick="sendReminders()" disabled>
                            <i class="bi bi-send me-1"></i> {{ trans('clients.whatsapp_send_reminders') }}
                        </button>
                    </div>
                </div>
                <div class="wa-card-body">
                    <div id="reminders_preview_container">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-info-circle fs-1"></i>
                            <p class="mt-2">{{ trans('clients.whatsapp_click_refresh') }}</p>
                        </div>
                    </div>
                    <div id="reminders_progress" class="mt-3" style="display:none;">
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="reminders_progress_bar" role="progressbar" style="width: 0%;">0%</div>
                        </div>
                        <div id="reminders_progress_text" class="text-center text-muted" style="font-size: 13px;"></div>
                    </div>
                    <div id="reminders_result" class="mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>

        {{-- Monthly Calendar --}}
        <div class="col-12">
            <div class="wa-card shadow-sm">
                <div class="wa-card-header bg-light">
                    <i class="bi bi-calendar-month text-primary me-2"></i> {{ trans('clients.whatsapp_monthly_reminders') }}
                </div>
                <div class="wa-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ trans('clients.whatsapp_select_month') }}</label>
                            <div id="wa_month_grid"></div>
                            <div id="wa_day_calendar_wrapper" style="display:none;">
                                <button class="wa-back-btn" onclick="backToMonthGrid()"><i class="bi bi-arrow-left"></i> {{ trans('clients.whatsapp_back_to_months') }}</button>
                                <div id="wa_day_calendar_container"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="wa_month_preview_container">
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-event fs-1"></i>
                                    <p class="mt-2">{{ trans('clients.whatsapp_select_day_hint') }}</p>
                                </div>
                            </div>
                            <div id="wa_month_progress" class="mt-3" style="display:none;">
                                <div class="progress mb-2" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="wa_month_progress_bar" role="progressbar" style="width: 0%;">0%</div>
                                </div>
                                <div id="wa_month_progress_text" class="text-center text-muted" style="font-size: 13px;"></div>
                            </div>
                            <div id="wa_month_result" class="mt-3" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Message Template --}}
        <div class="col-12">
            <div class="wa-card shadow-sm">
                <div class="wa-card-header bg-light">
                    <i class="bi bi-file-earmark-text text-success me-2"></i> {{ trans('clients.whatsapp_message_template') }}
                </div>
                <div class="wa-card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">{{ trans('clients.whatsapp_template_label') }}</label>
                            <textarea class="form-control" id="wa_template" rows="8" dir="rtl" style="font-size: 14px; line-height: 1.8;">{{ $settings['whatsapp_message_template'] }}</textarea>
                            <div class="text-muted mt-2" style="font-size: 12px;">
                                {{ trans('clients.whatsapp_variables') }}: <code>{name}</code> <code>{total_amount}</code> <code>{invoice_details_list}</code>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">{{ trans('clients.whatsapp_preview') }}</label>
                            <div class="wa-template-preview" id="wa_preview"></div>
                            <button class="btn btn-outline-success btn-sm mt-2" onclick="previewTemplate()">
                                <i class="bi bi-eye me-1"></i> {{ trans('clients.whatsapp_preview_btn') }}
                            </button>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success btn-sm" onclick="saveTemplate()">
                                <i class="bi bi-check-circle me-1"></i> {{ trans('clients.whatsapp_save_template') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Test Send --}}
        <div class="col-12">
            <div class="wa-card shadow-sm">
                <div class="wa-card-header bg-light">
                    <i class="bi bi-send text-info me-2"></i> {{ trans('clients.whatsapp_test_send') }}
                </div>
                <div class="wa-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ trans('clients.whatsapp_test_phone') }}</label>
                            <input type="text" class="form-control" id="wa_test_phone" placeholder="+201234567890" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ trans('clients.whatsapp_test_message') }}</label>
                            <input type="text" class="form-control" id="wa_test_message" placeholder="رسالة تجريبية">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-info btn-sm w-100 text-white" onclick="testSend()">
                                <i class="bi bi-send me-1"></i> {{ trans('clients.whatsapp_send_test') }}
                            </button>
                        </div>
                    </div>
                    <div class="wa-test-result" id="wa_test_result"></div>
                </div>
            </div>
        </div>

        {{-- Message Logs --}}
        <div class="col-12">
            <div class="wa-card shadow-sm">
                <div class="wa-card-header bg-light">
                    <i class="bi bi-clock-history text-secondary me-2"></i> {{ trans('clients.whatsapp_message_logs') }}
                </div>
                <div class="wa-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 wa-log-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>{{ trans('clients.whatsapp_log_date') }}</th>
                                    <th>{{ trans('clients.whatsapp_log_phone') }}</th>
                                    <th>{{ trans('clients.whatsapp_log_message') }}</th>
                                    <th>{{ trans('clients.whatsapp_log_status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log['timestamp'])->format('Y-m-d H:i') }}</td>
                                    <td dir="ltr">{{ $log['phone'] }}</td>
                                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $log['message'] }}</td>
                                    <td>
                                        <span class="wa-log-status {{ $log['status'] }}">
                                            @if($log['status'] == 'sent')
                                                <i class="bi bi-check-circle-fill"></i> {{ trans('clients.whatsapp_sent') }}
                                            @else
                                                <i class="bi bi-x-circle-fill"></i> {{ trans('clients.whatsapp_failed') }}
                                                @if(!empty($log['error']))
                                                    <br><small class="text-muted">{{ $log['error'] }}</small>
                                                @endif
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mt-2">{{ trans('clients.whatsapp_no_logs') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function previewTemplate() {
    var template = $('#wa_template').val();
    $.ajax({
        url: '{{ route('admin.settings.whatsapp.preview') }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', template: template },
        success: function(res) {
            $('#wa_preview').html(res.preview);
        }
    });
}

function saveTemplate() {
    var template = $('#wa_template').val();
    $.ajax({
        url: '{{ route('admin.settings.whatsapp.update') }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', whatsapp_message_template: template },
        success: function() {
            toastr.success('{{ trans('clients.whatsapp_template_saved') }}');
        }
    });
}

function testSend() {
    var phone = $('#wa_test_phone').val();
    var message = $('#wa_test_message').val();
    var $result = $('#wa_test_result');

    if (!phone || !message) {
        $result.removeClass('success error').addClass('error').show().text('{{ trans('clients.whatsapp_test_required') }}');
        return;
    }

    $result.hide();
    $.ajax({
        url: '{{ route('admin.settings.whatsapp.test') }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', test_phone: phone, test_message: message },
        success: function(res) {
            $result.removeClass('success error').addClass(res.success ? 'success' : 'error')
                .show().text(res.message);
        },
        error: function() {
            $result.removeClass('success error').addClass('error')
                .show().text('{{ trans('clients.whatsapp_test_error') }}');
        }
    });
}

function restartWhatsApp() {
    $.ajax({
        url: '{{ route('admin.settings.whatsapp.restart') }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                setTimeout(function() { refreshConnectionStatus(); }, 3000);
            } else {
                toastr.error(res.message);
            }
        }
    });
}

var waStatusInterval = null;

function refreshConnectionStatus() {
    $.ajax({
        url: '{{ route('admin.settings.whatsapp.api_status') }}',
        type: 'GET',
        success: function(res) {
            if (res.connected) {
                updateConnectionUI(res);
            } else {
                $.ajax({
                    url: '{{ route('admin.settings.whatsapp.api_qr') }}',
                    type: 'GET',
                    success: function(qrRes) {
                        res.qr = qrRes.qr || null;
                        updateConnectionUI(res);
                    },
                    error: function() {
                        updateConnectionUI(res);
                    }
                });
            }
        },
        error: function() {
            $.ajax({
                url: '{{ route('admin.settings.whatsapp.api_qr') }}',
                type: 'GET',
                success: function(res) {
                    updateConnectionUI({ connected: false, qr: res.qr || null });
                },
                error: function() {
                    updateConnectionUI({ connected: false, qr: null });
                }
            });
        }
    });
}

function updateConnectionUI(res) {
    var $dot = $('.wa-status-dot');
    var $statusText = $dot.next('.fw-semibold');
    var $phoneInfo = $dot.closest('.col-md-6').find('.text-muted');
    var $qrContainer = $('.wa-qr-container');

    if (res.connected) {
        $dot.removeClass('disconnected').addClass('connected');
        $statusText.text('{{ trans('clients.whatsapp_connected') }}');
        if (res.phone) {
            if ($phoneInfo.length === 0) {
                $dot.closest('.col-md-6').append('<div class="text-muted" style="font-size: 13px;"><i class="bi bi-phone me-1"></i> ' + res.phone + '</div>');
            } else {
                $phoneInfo.html('<i class="bi bi-phone me-1"></i> ' + res.phone);
            }
        }
        if ($qrContainer.length) $qrContainer.hide();
        if (waStatusInterval) { clearInterval(waStatusInterval); waStatusInterval = null; }
    } else {
        $dot.removeClass('connected').addClass('disconnected');
        $statusText.text('{{ trans('clients.whatsapp_disconnected') }}');
        $phoneInfo.remove();

        if (res.qr) {
            if ($qrContainer.length === 0) {
                $('.wa-card-body').first().append('<div class="wa-qr-container mt-3"><img src="' + res.qr + '" alt="QR Code"><div class="text-muted mt-2" style="font-size: 13px;"><i class="bi bi-qr-code me-1"></i> {{ trans('clients.whatsapp_qr_scan') }}</div></div>');
            } else {
                $qrContainer.show().find('img').attr('src', res.qr);
                $qrContainer.find('.text-muted').show();
            }
        } else {
            if ($qrContainer.length === 0) {
                $('.wa-card-body').first().append('<div class="wa-qr-container mt-3"><div class="text-muted py-4"><i class="bi bi-hourglass-split fs-1"></i><p class="mt-2">{{ trans('clients.whatsapp_connecting') }}</p></div></div>');
            } else {
                $qrContainer.show().html('<div class="text-muted py-4"><i class="bi bi-hourglass-split fs-1"></i><p class="mt-2">{{ trans('clients.whatsapp_connecting') }}</p></div>');
            }
        }

        if (!waStatusInterval) {
            waStatusInterval = setInterval(refreshConnectionStatus, 15000);
        }
    }
}

var remindersData = [];

function loadRemindersPreview() {
    var $container = $('#reminders_preview_container');
    $container.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">{{ trans("clients.whatsapp_loading") }}</p></div>');
    $('#btn_send_reminders').prop('disabled', true);
    $('#reminders_result').hide();
    $('#reminders_progress').hide();

    $.ajax({
        url: '{{ route('admin.settings.whatsapp.reminders_preview') }}',
        type: 'GET',
        success: function(res) {
            if (res.error) {
                $container.html('<div class="alert alert-danger">' + res.error + '</div>');
                return;
            }

            if (res.clients.length === 0) {
                $container.html('<div class="alert alert-info text-center"><i class="bi bi-check-circle me-2"></i>{{ trans("clients.whatsapp_no_reminders") }}</div>');
                return;
            }

            remindersData = res.clients;
            var isArabic = '{{ app()->getLocale() }}' === 'ar';
            var headers = isArabic
                ? ['العميل', 'الهاتف', 'الإجمالي', 'الفواتير']
                : ['Client', 'Phone', 'Total', 'Invoices'];

            var html = '<table class="wa-preview-table"><thead><tr>';
            html += '<th><input type="checkbox" id="wa_select_all_rem" checked onchange="toggleSelectAll()"></th>';
            headers.forEach(function(h) { html += '<th>' + h + '</th>'; });
            html += '</tr></thead><tbody>';

            res.clients.forEach(function(c) {
                var phoneHtml = c.phone;
                if (c.suspicious_phone) {
                    phoneHtml += ' <span class="wa-phone-warning" title="{{ trans("clients.whatsapp_phone_warning") }}">⚠</span>';
                }
                html += '<tr data-client-id="' + c.client_id + '" data-phone="' + c.phone + '">';
                html += '<td><input type="checkbox" class="wa-client-check" checked onchange="updateSelectedCount()"></td>';
                html += '<td>' + c.client_name + '</td>';
                html += '<td dir="ltr">' + phoneHtml + '</td>';
                html += '<td>$' + c.total_amount + '</td>';
                html += '<td>';
                c.invoice_lines.forEach(function(line) {
                    html += '<span class="invoice-line">' + line + '</span>';
                });
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            html += '<div class="wa-preview-summary">' +
                (isArabic ? 'العملاء: ' + res.total + ' | الإجمالي: $' + res.grandTotal : 'Clients: ' + res.total + ' | Total: $' + res.grandTotal) +
                '</div>';
            html += '<div class="wa-selection-controls">';
            html += '<span id="wa_selected_count">' + res.total + ' ' + (isArabic ? 'عملاء محددين' : 'clients selected') + '</span>';
            html += '<button class="btn btn-success btn-sm" id="btn_send_selected" onclick="sendSelectedClients(\'reminders\')">';
            html += '<i class="bi bi-send me-1"></i> ' + (isArabic ? 'إرسال المحدد' : 'Send Selected') + ' (' + res.total + ')</button>';
            html += '<button class="btn btn-outline-secondary btn-sm" onclick="toggleSelectAll()">';
            html += '<i class="bi bi-check-square me-1"></i> ' + (isArabic ? 'تحديد/إلغاء الكل' : 'Select/Deselect All') + '</button>';
            html += '</div>';

            $container.html(html);
        },
        error: function() {
            $container.html('<div class="alert alert-danger">{{ trans("clients.whatsapp_preview_error") }}</div>');
        }
    });
}

function sendReminders() {
    sendSelectedClients('reminders');
}

window.addEventListener('load', function() {
    console.log('Window loaded, calling previewTemplate and initMonthGrid');
    previewTemplate();
    initMonthGrid();
    refreshConnectionStatus();
    waStatusInterval = setInterval(refreshConnectionStatus, 15000);
});

var selectedMonth = null;
var selectedYear = null;
var selectedDay = null;
var monthlyData = [];
var currentCalMonth = null;
var currentCalYear = null;
var daysWithInvoices = {};

function initMonthGrid() {
    console.log('initMonthGrid called');
    var currentYear = new Date().getFullYear();
    var html = '<div class="wa-year-selector">';
    html += '<i class="bi bi-chevron-left" onclick="navigateYear(-1)"></i>';
    html += '<select id="wa_year_select" onchange="changeYear(this.value)">';
    for (var y = currentYear - 2; y <= currentYear + 1; y++) {
        html += '<option value="' + y + '"' + (y === currentYear ? ' selected' : '') + '>' + y + '</option>';
    }
    html += '</select>';
    html += '<i class="bi bi-chevron-right" onclick="navigateYear(1)"></i>';
    html += '</div>';
    html += '<div class="wa-month-grid" id="wa_months_container"></div>';
    $('#wa_month_grid').html(html);
    console.log('Month grid HTML injected');
    renderMonths(currentYear);
}

function navigateYear(direction) {
    var $select = $('#wa_year_select');
    var currentIndex = $select.prop('selectedIndex');
    var newIndex = currentIndex + direction;
    if (newIndex >= 0 && newIndex < $select.find('option').length) {
        $select.prop('selectedIndex', newIndex).trigger('change');
    }
}

function changeYear(year) {
    renderMonths(parseInt(year));
}

function renderMonths(year) {
    console.log('renderMonths called for year:', year);
    var isArabic = '{{ app()->getLocale() }}' === 'ar';
    var monthNames = isArabic
        ? ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
        : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var currentMonth = new Date().getMonth() + 1;
    var currentYear = new Date().getFullYear();

    var html = '';
    for (var m = 1; m <= 12; m++) {
        var isCurrentMonth = (m === currentMonth && year === currentYear);
        var classes = 'wa-month-btn' + (isCurrentMonth ? ' active' : '');
        html += '<div class="' + classes + '" id="wa_month_' + m + '" onclick="selectMonth(' + m + ', ' + year + ')">';
        html += '<div class="month-name">' + monthNames[m - 1] + '</div>';
        html += '<div class="month-count" id="wa_month_count_' + m + '">...</div>';
        html += '</div>';
    }
    $('#wa_months_container').html(html);

    loadMonthInvoiceCounts(year);
}

function loadMonthInvoiceCounts(year) {
    for (var m = 1; m <= 12; m++) {
        (function(month) {
            $.ajax({
                url: '{{ route('admin.settings.whatsapp.monthly_preview') }}',
                type: 'GET',
                data: { month: month, year: year },
                success: function(res) {
                    if (res.error) return;
                    var $count = $('#wa_month_count_' + month);
                    if (res.total > 0) {
                        $count.text(res.total + ' عميل | $' + res.grandTotal);
                        $('#wa_month_' + month).addClass('has-invoices');
                    } else {
                        $count.text('لا يوجد');
                    }
                }
            });
        })(m);
    }
}

function selectMonth(month, year) {
    selectedMonth = month;
    selectedYear = year;

    $('.wa-month-btn').removeClass('active');
    $('#wa_month_' + month).addClass('active');

    loadMonthForDayCalendar(month, year);
}

function loadMonthForDayCalendar(month, year) {
    $.ajax({
        url: '{{ route('admin.settings.whatsapp.monthly_preview') }}',
        type: 'GET',
        data: { month: month, year: year },
        success: function(res) {
            if (res.error) return;
            daysWithInvoices = res.days_with_invoices || {};
            currentCalMonth = month;
            currentCalYear = year;
            renderDayCalendar(month, year);
        }
    });
}

function renderDayCalendar(month, year) {
    $('#wa_month_grid').hide();
    $('#wa_day_calendar_wrapper').show();

    var isArabic = '{{ app()->getLocale() }}' === 'ar';
    var monthNames = isArabic
        ? ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
        : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    var firstDay = new Date(year, month - 1, 1).getDay();
    var daysInMonth = new Date(year, month, 0).getDate();
    var today = new Date();
    var isCurrentMonth = (month === today.getMonth() + 1 && year === today.getFullYear());
    var todayDate = today.getDate();

    var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    var html = '<div class="wa-day-nav">';
    html += '<button onclick="navigateDayCalendar(-1)"><i class="bi bi-chevron-left"></i></button>';
    html += '<span class="month-title">' + monthNames[month - 1] + ' ' + year + '</span>';
    html += '<button onclick="navigateDayCalendar(1)"><i class="bi bi-chevron-right"></i></button>';
    html += '</div>';

    html += '<table class="wa-day-calendar"><thead><tr>';
    dayNames.forEach(function(d) { html += '<th>' + d + '</th>'; });
    html += '</tr></thead><tbody><tr>';

    for (var i = 0; i < firstDay; i++) {
        html += '<td class="empty"></td>';
    }

    var currentDayOfWeek = firstDay;
    for (var d = 1; d <= daysInMonth; d++) {
        var classes = [];
        if (isCurrentMonth && d === todayDate) classes.push('today');
        if (daysWithInvoices[d]) classes.push('has-invoices');

        html += '<td class="' + classes.join(' ') + '" onclick="selectDay(' + year + ', ' + month + ', ' + d + ')">';
        html += d;
        if (daysWithInvoices[d]) {
            html += '<span class="wa-day-badge">' + daysWithInvoices[d].count + '</span>';
        }
        html += '</td>';

        currentDayOfWeek++;
        if (currentDayOfWeek === 7 && d < daysInMonth) {
            html += '</tr><tr>';
            currentDayOfWeek = 0;
        }
    }

    var remaining = 7 - currentDayOfWeek;
    if (remaining < 7) {
        for (var r = 0; r < remaining; r++) {
            html += '<td class="empty"></td>';
        }
    }

    html += '</tr></tbody></table>';
    html += '<div class="wa-day-hint">{{ trans("clients.whatsapp_select_day_hint") }}</div>';

    $('#wa_day_calendar_container').html(html);

    var $container = $('#wa_month_preview_container');
    $container.html('<div class="text-center text-muted py-4"><i class="bi bi-calendar-day fs-1"></i><p class="mt-2">{{ trans("clients.whatsapp_select_day_hint") }}</p></div>');
}

function navigateDayCalendar(direction) {
    var newMonth = currentCalMonth + direction;
    var newYear = currentCalYear;
    if (newMonth > 12) { newMonth = 1; newYear++; }
    if (newMonth < 1) { newMonth = 12; newYear--; }
    loadMonthForDayCalendar(newMonth, newYear);
}

function backToMonthGrid() {
    $('#wa_day_calendar_wrapper').hide();
    $('#wa_month_grid').show();
    $('#wa_month_preview_container').html('<div class="text-center text-muted py-4"><i class="bi bi-calendar-event fs-1"></i><p class="mt-2">{{ trans("clients.whatsapp_select_day_hint") }}</p></div>');
}

function selectDay(year, month, day) {
    selectedDay = day;
    $('.wa-day-calendar td').removeClass('selected');
    event.target.closest('td').classList.add('selected');
    loadDailyPreview(year, month, day);
}

function loadDailyPreview(year, month, day) {
    var $container = $('#wa_month_preview_container');
    $container.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">{{ trans("clients.whatsapp_loading") }}</p></div>');
    $('#wa_month_result').hide();
    $('#wa_month_progress').hide();

    $.ajax({
        url: '{{ route('admin.settings.whatsapp.daily_preview') }}',
        type: 'GET',
        data: { month: month, year: year, day: day },
        success: function(res) {
            if (res.error) {
                $container.html('<div class="alert alert-danger">' + res.error + '</div>');
                return;
            }

            if (res.clients.length === 0) {
                var isArabic = '{{ app()->getLocale() }}' === 'ar';
                $container.html('<div class="alert alert-info text-center"><i class="bi bi-check-circle me-2"></i>' + (isArabic ? 'لا توجد فواتير مستحقة في ' : 'No invoices due on ') + res.month_name + ' ' + res.day + ', ' + res.year + '</div>');
                return;
            }

            monthlyData = res.clients;
            var isArabic = '{{ app()->getLocale() }}' === 'ar';

            var html = '<h6 class="fw-bold mb-3"><i class="bi bi-calendar-day me-2"></i>' + res.month_name + ' ' + res.day + ', ' + res.year + '</h6>';
            html += '<table class="wa-preview-table"><thead><tr>';
            html += '<th><input type="checkbox" id="wa_select_all" checked onchange="toggleSelectAll()"></th>';
            var headers = isArabic ? ['العميل', 'الهاتف', 'الإجمالي', 'الفواتير'] : ['Client', 'Phone', 'Total', 'Invoices'];
            headers.forEach(function(h) { html += '<th>' + h + '</th>'; });
            html += '</tr></thead><tbody>';

            res.clients.forEach(function(c) {
                var phoneHtml = c.phone;
                if (c.suspicious_phone) {
                    phoneHtml += ' <span class="wa-phone-warning" title="{{ trans("clients.whatsapp_phone_warning") }}">⚠</span>';
                }
                html += '<tr data-client-id="' + c.client_id + '" data-phone="' + c.phone + '">';
                html += '<td><input type="checkbox" class="wa-client-check" checked onchange="updateSelectedCount()"></td>';
                html += '<td>' + c.client_name + '</td>';
                html += '<td dir="ltr">' + phoneHtml + '</td>';
                html += '<td>$' + c.total_amount + '</td>';
                html += '<td>';
                c.invoice_lines.forEach(function(line) {
                    html += '<span class="invoice-line">' + line + '</span>';
                });
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            html += '<div class="wa-preview-summary">' +
                (isArabic ? 'العملاء: ' + res.total + ' | الإجمالي: $' + res.grandTotal : 'Clients: ' + res.total + ' | Total: $' + res.grandTotal) +
                '</div>';
            html += '<div class="wa-selection-controls">';
            html += '<span id="wa_selected_count">' + res.total + ' ' + (isArabic ? 'عملاء محددين' : 'clients selected') + '</span>';
            html += '<button class="btn btn-success btn-sm" id="btn_send_selected" onclick="sendSelectedClients(\'daily\')">';
            html += '<i class="bi bi-send me-1"></i> ' + (isArabic ? 'إرسال المحدد' : 'Send Selected') + ' (' + res.total + ')</button>';
            html += '<button class="btn btn-outline-secondary btn-sm" onclick="toggleSelectAll()">';
            html += '<i class="bi bi-check-square me-1"></i> ' + (isArabic ? 'تحديد/إلغاء الكل' : 'Select/Deselect All') + '</button>';
            html += '</div>';

            $container.html(html);
        },
        error: function() {
            $container.html('<div class="alert alert-danger">{{ trans("clients.whatsapp_preview_error") }}</div>');
        }
    });
}

function sendSelectedClients(type) {
    var selected = [];
    $('.wa-client-check:checked').each(function() {
        var $row = $(this).closest('tr');
        selected.push(parseInt($row.data('client-id')));
    });

    if (selected.length === 0) {
        var isArabic = '{{ app()->getLocale() }}' === 'ar';
        alert(isArabic ? 'لم يتم تحديد أي عملاء' : 'No clients selected');
        return;
    }

    var isArabic = '{{ app()->getLocale() }}' === 'ar';
    if (!confirm(isArabic ? 'هل تريد إرسال رسائل واتساب للعملاء المحددين؟' : 'Send WhatsApp messages to selected clients?')) return;

    $('#wa_month_progress').show();
    $('#wa_month_result').hide().empty();
    $('#btn_send_selected').prop('disabled', true);

    var data = { _token: '{{ csrf_token() }}', clients: selected, type: type, month: currentCalMonth, year: currentCalYear };
    if (type === 'daily') { data.day = selectedDay; }

    $.ajax({
        url: '{{ route('admin.settings.whatsapp.send_selected') }}',
        type: 'POST',
        data: data,
        success: function(res) {
            if (res.error) {
                $('#wa_month_result').html('<div class="alert alert-danger">' + res.error + '</div>').show();
                $('#btn_send_selected').prop('disabled', false);
                return;
            }

            $('#wa_month_progress_bar').css('width', '100%').text('100%');
            $('#wa_month_progress_text').text('');

            var summaryHtml = '<div class="alert alert-success text-center fw-bold">';
            summaryHtml += isArabic ? 'تم الإرسال: ' + res.sent + ' | فشل: ' + res.failed : 'Sent: ' + res.sent + ' | Failed: ' + res.failed;
            summaryHtml += '</div>';

            var detailsHtml = '';
            res.results.forEach(function(r) {
                var cls = r.status === 'sent' ? 'sent' : 'failed';
                var icon = r.status === 'sent' ? '✓' : '✗';
                detailsHtml += '<div class="wa-result-item ' + cls + '">' + icon + ' ' + r.client + ' (' + r.phone + ')';
                if (r.error) detailsHtml += ' - ' + r.error;
                detailsHtml += '</div>';
            });

            $('#wa_month_result').html(summaryHtml + detailsHtml).show();
            $('#btn_send_selected').prop('disabled', false);
            toastr.success(isArabic ? 'تم الانتهاء' : 'Done');

            if (type === 'daily') {
                loadMonthForDayCalendar(currentCalMonth, currentCalYear);
            }
        },
        error: function() {
            $('#wa_month_result').html('<div class="alert alert-danger">{{ trans("clients.whatsapp_send_error") }}</div>').show();
            $('#btn_send_selected').prop('disabled', false);
        }
    });
}

function toggleSelectAll() {
    var $checks = $('.wa-client-check');
    var $selectAll = $('#wa_select_all');
    if ($selectAll.length) {
        var checked = $selectAll.prop('checked');
        $checks.prop('checked', checked);
    } else {
        var allChecked = $checks.length === $checks.filter(':checked').length;
        $checks.prop('checked', !allChecked);
    }
    updateSelectedCount();
}

function updateSelectedCount() {
    var count = $('.wa-client-check:checked').length;
    var total = $('.wa-client-check').length;
    var isArabic = '{{ app()->getLocale() }}' === 'ar';
    $('#wa_selected_count').text(count + ' ' + (isArabic ? 'عملاء محددين' : 'clients selected'));
    $('#btn_send_selected').html('<i class="bi bi-send me-1"></i> ' + (isArabic ? 'إرسال المحدد' : 'Send Selected') + ' (' + count + ')');
    $('#btn_send_selected').prop('disabled', count === 0);

    var $selectAll = $('#wa_select_all');
    if ($selectAll.length) {
        $selectAll.prop('checked', count === total);
        $selectAll.prop('indeterminate', count > 0 && count < total);
    }
}

function loadMonthlyPreview(month, year) {
    var $container = $('#wa_month_preview_container');
    $container.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">{{ trans("clients.whatsapp_loading") }}</p></div>');
    $('#wa_month_result').hide();
    $('#wa_month_progress').hide();

    $.ajax({
        url: '{{ route('admin.settings.whatsapp.monthly_preview') }}',
        type: 'GET',
        data: { month: month, year: year },
        success: function(res) {
            if (res.error) {
                $container.html('<div class="alert alert-danger">' + res.error + '</div>');
                return;
            }

            if (res.clients.length === 0) {
                $container.html('<div class="alert alert-info text-center"><i class="bi bi-check-circle me-2"></i>{{ trans("clients.whatsapp_no_invoices_month") }} ' + res.month_name + ' ' + res.year + '</div>');
                return;
            }

            monthlyData = res.clients;
            var isArabic = '{{ app()->getLocale() }}' === 'ar';

            var html = '<h6 class="fw-bold mb-3"><i class="bi bi-calendar-event me-2"></i>' + res.month_name + ' ' + res.year + '</h6>';
            html += '<table class="wa-preview-table"><thead><tr>';
            var headers = isArabic ? ['العميل', 'الهاتف', 'الإجمالي', 'الفواتير'] : ['Client', 'Phone', 'Total', 'Invoices'];
            headers.forEach(function(h) { html += '<th>' + h + '</th>'; });
            html += '</tr></thead><tbody>';

            res.clients.forEach(function(c) {
                html += '<tr>';
                html += '<td>' + c.client_name + '</td>';
                html += '<td dir="ltr">' + c.phone + '</td>';
                html += '<td>$' + c.total_amount + '</td>';
                html += '<td>';
                c.invoice_lines.forEach(function(line) {
                    html += '<span class="invoice-line">' + line + '</span>';
                });
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            html += '<div class="wa-preview-summary">' +
                (isArabic ? 'العملاء: ' + res.total + ' | الإجمالي: $' + res.grandTotal : 'Clients: ' + res.total + ' | Total: $' + res.grandTotal) +
                '</div>';
            html += '<div class="mt-3 text-center">';
            html += '<button class="btn btn-success btn-sm" onclick="sendMonthlyReminders()">';
            html += '<i class="bi bi-send me-1"></i> ' + (isArabic ? 'إرسال رسائل واتساب' : 'Send WhatsApp Messages') + '</button>';
            html += '</div>';

            $container.html(html);
        },
        error: function() {
            $container.html('<div class="alert alert-danger">{{ trans("clients.whatsapp_preview_error") }}</div>');
        }
    });
}

function sendMonthlyReminders() {
    if (!selectedMonth || !selectedYear) return;
    var isArabic = '{{ app()->getLocale() }}' === 'ar';
    if (!confirm(isArabic ? 'هل أنت متأكد من إرسال رسائل واتساب لجميع عملاء هذا الشهر؟' : 'Are you sure you want to send WhatsApp messages to all clients for this month?')) return;

    $('#wa_month_progress').show();
    $('#wa_month_result').hide().empty();
    $('.wa-month-btn').off('click');

    $.ajax({
        url: '{{ route('admin.settings.whatsapp.send_monthly') }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', month: selectedMonth, year: selectedYear },
        success: function(res) {
            if (res.error) {
                $('#wa_month_result').html('<div class="alert alert-danger">' + res.error + '</div>').show();
                initMonthGrid();
                return;
            }

            $('#wa_month_progress_bar').css('width', '100%').text('100%');
            $('#wa_month_progress_text').text('');

            var summaryHtml = '<div class="alert alert-success text-center fw-bold">';
            summaryHtml += isArabic ? 'تم الإرسال: ' + res.sent + ' | فشل: ' + res.failed : 'Sent: ' + res.sent + ' | Failed: ' + res.failed;
            summaryHtml += '</div>';

            var detailsHtml = '';
            res.results.forEach(function(r) {
                var cls = r.status === 'sent' ? 'sent' : 'failed';
                var icon = r.status === 'sent' ? '✓' : '✗';
                detailsHtml += '<div class="wa-result-item ' + cls + '">' + icon + ' ' + r.client + ' (' + r.phone + ')';
                if (r.error) detailsHtml += ' - ' + r.error;
                detailsHtml += '</div>';
            });

            $('#wa_month_result').html(summaryHtml + detailsHtml).show();
            initMonthGrid();
            toastr.success(isArabic ? 'تم الانتهاء' : 'Done');
        },
        error: function() {
            $('#wa_month_result').html('<div class="alert alert-danger">{{ trans("clients.whatsapp_send_error") }}</div>').show();
            initMonthGrid();
        }
    });
}
</script>
@endsection
