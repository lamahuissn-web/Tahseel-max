<div class="card card-xl-stretch">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-activity me-2 text-primary"></i>
            Connection Monitor
        </h3>
        <div class="card-toolbar d-flex flex-wrap gap-2">
            <span class="badge {{ $monitor['api_reachable'] ? 'badge-light-success' : 'badge-light-danger' }}">
                {{ $monitor['api_reachable'] ? 'OpenWA Reachable' : 'OpenWA Unreachable' }}
            </span>
            <span class="badge badge-light-{{ $monitor['overall_alert_level'] }}">{{ $monitor['overall_alert_label'] }}</span>
            <span class="text-muted fs-8" id="monitor-last-checked">
                Last checked: {{ optional($monitor['checked_at'])->diffForHumans() ?? 'just now' }}
            </span>
            <button type="button" class="btn btn-sm btn-light-primary" id="monitor-refresh-btn" onclick="refreshConnectionMonitor(false)">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-light-warning" id="monitor-qr-btn" onclick="fetchMonitorQR()">
                <i class="bi bi-qr-code-scan me-1"></i> Show QR
            </button>
            <button type="button" class="btn btn-sm btn-light-primary" onclick="refreshQRCode()">
                <i class="bi bi-arrow-clockwise me-1"></i> New QR
            </button>
            <button type="button" class="btn btn-sm btn-light-success" id="monitor-restart-btn" onclick="restartWhatsAppSession()">
                <i class="bi bi-arrow-repeat me-1"></i> Restart Session
            </button>
            <a href="{{ route('admin.whatsapp.queue') }}" class="btn btn-sm btn-light-info">
                <i class="bi bi-list-check me-1"></i> Open Queue
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-light-{{ $monitor['overall_alert_level'] }} d-flex align-items-start p-5 mb-6">
            <i class="bi {{ $monitor['overall_alert_level'] === 'success' ? 'bi-shield-check' : ($monitor['overall_alert_level'] === 'danger' ? 'bi-exclamation-octagon' : 'bi-exclamation-triangle') }} fs-2 me-4 text-{{ $monitor['overall_alert_level'] }}"></i>
            <div class="d-flex flex-column flex-grow-1">
                <h4 class="mb-1 text-gray-900">{{ $monitor['overall_alert_label'] }} / حالة المراقبة</h4>
                <span class="text-gray-700">{{ $monitor['overall_alert_text'] }}</span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-6">
            @foreach(($monitor['status_badges'] ?? []) as $badge)
                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
            @endforeach
        </div>

        <div class="row g-6">
            <div class="col-xl-6">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle gs-0 gy-3 mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted fw-semibold w-200px">OpenWA API</th>
                                <td><span class="badge {{ $monitor['api_reachable'] ? 'badge-light-success' : 'badge-light-danger' }}">{{ $monitor['api_reachable'] ? 'Reachable' : 'Unreachable' }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Session</th>
                                <td><span class="badge {{ $monitor['session_connected'] ? 'badge-light-success' : 'badge-light-warning' }}">{{ $monitor['session_status_label'] }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">QR Required</th>
                                <td><span class="badge {{ $monitor['qr_needed'] ? 'badge-light-warning' : 'badge-light-secondary' }}">{{ $monitor['qr_needed'] ? 'Yes' : 'No' }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Connected Phone</th>
                                <td>{{ $monitor['connected_phone'] ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">OpenWA Note</th>
                                <td class="text-gray-700">{{ $monitor['session_message'] ?: '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle gs-0 gy-3 mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted fw-semibold w-200px">Pending Queue</th>
                                <td><span class="badge badge-light-primary">{{ $monitor['pending_queue_count'] }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Sending Now</th>
                                <td><span class="badge badge-light-info">{{ $monitor['sending_queue_count'] }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Oldest Pending</th>
                                <td>{{ $monitor['oldest_pending_at'] ? $monitor['oldest_pending_at']->diffForHumans() : '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Last Success</th>
                                <td>{{ $monitor['last_success_at'] ? $monitor['last_success_at']->diffForHumans() : '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Last Failure</th>
                                <td>
                                    {{ $monitor['last_failure_at'] ? $monitor['last_failure_at']->diffForHumans() : '—' }}
                                    @if($monitor['last_failure_error'])
                                        <div class="text-muted fs-8 mt-1">{{ \Illuminate\Support\Str::limit($monitor['last_failure_error'], 90) }}</div>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="separator separator-dashed my-6"></div>

        <div class="alert alert-light-primary d-flex align-items-start p-5 mb-6">
            <i class="bi bi-info-circle-fill fs-2 me-4 text-primary"></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-gray-900">Recommended admin action</h4>
                <span class="text-gray-700">{{ $monitor['recommended_action'] }}</span>
            </div>
        </div>

        <div class="alert alert-light-danger border border-danger d-flex align-items-start p-5 mb-0">
            <i class="bi bi-shield-exclamation fs-2 me-4 text-danger"></i>
            <div class="d-flex flex-column flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1 text-danger">Danger Zone: Change WhatsApp Phone</h4>
                        <div class="text-gray-700">
                            Revoke logs out the current WhatsApp session immediately. Sending stops until a new phone scans the QR code.
                            Use this only when changing the connected phone or repairing a broken session.
                        </div>
                        <div class="text-muted fs-8 mt-2">
                            Safety: blocked when queue has pending/sending messages. Requires typing <code>REVOKE</code>.
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" id="monitor-revoke-btn" onclick="revokeWhatsAppSession()">
                        <i class="bi bi-door-open me-1"></i> Revoke Session / Change Phone
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>