{{-- Internet Tab — RADIUS Live Data View --}}
@php
    function formatBytesHelper($bytes) {
        $bytes = max($bytes, 0);
        if ($bytes > 1073741824) return round($bytes/1073741824, 2) . ' GB';
        if ($bytes > 1048576) return round($bytes/1048576, 2) . ' MB';
        return $bytes . ' B';
    }

    $dlToday = $todayTraffic['download_bytes'] ?? 0;
    $ulToday = $todayTraffic['upload_bytes'] ?? 0;
    $totalToday = $dlToday + $ulToday;

    $dlMonth = $monthlyTraffic['download_bytes'] ?? 0;
    $ulMonth = $monthlyTraffic['upload_bytes'] ?? 0;
    $totalMonth = $dlMonth + $ulMonth;

    $liveAddress = $liveData['address'] ?? ($clientInfo['last_session']['framedipaddress'] ?? '—');
    $liveMac = $liveData['caller-id'] ?? ($liveData['mac-address'] ?? ($clientInfo['last_session']['callingstationid'] ?? '—'));
    $liveUptime = $liveData['uptime'] ?? null;

    // Format uptime from MikroTik (e.g. "02:30:15") or from session_time (seconds)
    if ($liveUptime) {
        $uptimeDisplay = $liveUptime; // MikroTik returns formatted string
    } else {
        $sec = $clientInfo['last_session']['acctsessiontime'] ?? 0;
        $uptimeDisplay = $sec > 0 ? gmdate('H\h i\m', $sec) : '—';
    }

    // Speed from subscription
    $planName = $client->subscription->name ?? '—';
@endphp

<div class="internet-tab-content">

    {{-- Status Card --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-3">
                                <span class="online-dot {{ $isOnline ? 'online' : 'offline' }}"></span>
                                {{ $isOnline ? '🟢 ' . trans('clients.connected') : '🔴 ' . trans('clients.disconnected') }}
                                @if($liveData)
                                    <small class="text-primary ms-1">(Live)</small>
                                @endif
                            </h6>
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">{{ trans('clients.ip_address') }}</small>
                                    <strong>{{ $liveAddress }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">MAC</small>
                                    <strong class="text-muted small">{{ $liveMac }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">{{ trans('clients.session_time') }}</small>
                                    <strong>{{ $uptimeDisplay }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">{{ trans('clients.plan') }}</small>
                                    <strong>{{ $planName }}</strong>
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($isOnline)
                                <button class="btn btn-sm btn-outline-danger" onclick="radiusDisconnect({{ $client->id }})">
                                    <i class="bi bi-plug"></i> {{ trans('clients.disconnect_user') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Traffic Stats Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="traffic-stat-card card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <small class="stat-label d-block">{{ trans('clients.traffic_today') }}</small>
                    <div class="stat-value text-primary">{{ formatBytesHelper($totalToday) }}</div>
                    <small class="stat-sub">📥 {{ formatBytesHelper($dlToday) }} / 📤 {{ formatBytesHelper($ulToday) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="traffic-stat-card card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <small class="stat-label d-block">{{ trans('clients.traffic_month') }}</small>
                    <div class="stat-value text-success">{{ formatBytesHelper($totalMonth) }}</div>
                    <small class="stat-sub">📥 {{ formatBytesHelper($dlMonth) }} / 📤 {{ formatBytesHelper($ulMonth) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="traffic-stat-card card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <small class="stat-label d-block">{{ trans('clients.sessions_count') }}</small>
                    <div class="stat-value text-info">{{ count($activeSessions) }}</div>
                    <small class="stat-sub">{{ trans('clients.active') }} / {{ $monthlyTraffic['sessions'] ?? 0 }} هذا الشهر</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Sessions --}}
    @if(count($activeSessions) > 0)
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold mb-0"><i class="bi bi-wifi me-1"></i> الجلسات النشطة</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ trans('clients.start_time') }}</th>
                            <th>{{ trans('clients.ip_address') }}</th>
                            <th>📥 {{ trans('clients.download') }}</th>
                            <th>📤 {{ trans('clients.upload') }}</th>
                            <th>{{ trans('clients.duration') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeSessions as $session)
                        <tr>
                            <td>{{ $session->radacctid }}</td>
                            <td>{{ $session->acctstarttime }}</td>
                            <td>{{ $session->framedipaddress }}</td>
                            <td>{{ formatBytesHelper($session->acctoutputoctets) }}</td>
                            <td>{{ formatBytesHelper($session->acctinputoctets) }}</td>
                            <td>
                                @php $h = floor($session->acctsessiontime / 3600); $m = floor(($session->acctsessiontime % 3600) / 60); @endphp
                                {{ $h }}h {{ $m }}m
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" onclick="radiusDisconnect({{ $client->id }})">
                                    🔌
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- RADIUS Actions --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold mb-0"><i class="bi bi-tools me-1"></i> {{ trans('clients.radius_actions') }}</h6>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                @if($isOnline)
                <button class="btn btn-outline-danger" onclick="radiusDisconnect({{ $client->id }})">
                    <i class="bi bi-plug"></i> {{ trans('clients.disconnect_user') }}
                </button>
                @endif
                <button class="btn btn-outline-warning" onclick="radiusToggle({{ $client->id }})">
                    <i class="bi bi-toggle-off"></i> {{ $client->is_active ? trans('clients.disable') : trans('clients.enable') }}
                </button>
                <button class="btn btn-outline-info" onclick="radiusChangeSpeed({{ $client->id }})">
                    <i class="bi bi-speedometer2"></i> {{ trans('clients.change_speed') }}
                </button>
                <button class="btn btn-outline-secondary" onclick="radiusScheduleStop({{ $client->id }})">
                    <i class="bi bi-calendar-stop"></i> {{ trans('clients.schedule_stop') }}
                </button>
            </div>
        </div>
    </div>

</div>
