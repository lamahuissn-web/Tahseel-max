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

$(document).ready(function() {
    previewTemplate();
});
</script>
@endsection
