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
                setTimeout(function() { location.reload(); }, 2000);
            } else {
                toastr.error(res.message);
            }
        }
    });
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

            $container.html(html);
            $('#btn_send_reminders').prop('disabled', false);
        },
        error: function() {
            $container.html('<div class="alert alert-danger">{{ trans("clients.whatsapp_preview_error") }}</div>');
        }
    });
}

function sendReminders() {
    if (!confirm('{{ trans("clients.whatsapp_confirm_send") }}')) return;

    $('#btn_send_reminders').prop('disabled', true);
    $('#reminders_progress').show();
    $('#reminders_result').hide().empty();

    $.ajax({
        url: '{{ route('admin.settings.whatsapp.send_reminders') }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            return xhr;
        },
        success: function(res) {
            if (res.error) {
                $('#reminders_result').html('<div class="alert alert-danger">' + res.error + '</div>').show();
                $('#btn_send_reminders').prop('disabled', false);
                return;
            }

            $('#reminders_progress_bar').css('width', '100%').text('100%');
            $('#reminders_progress_text').text('');

            var isArabic = '{{ app()->getLocale() }}' === 'ar';
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

            $('#reminders_result').html(summaryHtml + detailsHtml).show();
            $('#btn_send_reminders').prop('disabled', false);
            toastr.success(isArabic ? 'تم الانتهاء' : 'Done');
        },
        error: function() {
            $('#reminders_result').html('<div class="alert alert-danger">{{ trans("clients.whatsapp_send_error") }}</div>').show();
            $('#btn_send_reminders').prop('disabled', false);
        }
    });
}

$(document).ready(function() {
    previewTemplate();
});
</script>
@endsection
