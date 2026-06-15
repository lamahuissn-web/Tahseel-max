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

    $liveAddress = (isset($liveData) ? ($liveData['address'] ?? null) : null) ?? ($clientInfo['last_session']['framedipaddress'] ?? '—');
    $liveMac = (isset($liveData) ? ($liveData['caller-id'] ?? ($liveData['mac-address'] ?? null)) : null) ?? ($clientInfo['last_session']['callingstationid'] ?? '—');
    $liveUptime = isset($liveData) ? ($liveData['uptime'] ?? null) : null;

    if ($liveUptime) {
        $uptimeDisplay = $liveUptime;
    } else {
        $sec = $clientInfo['last_session']['acctsessiontime'] ?? 0;
        $uptimeDisplay = $sec > 0 ? gmdate('H\h i\m', $sec) : '—';
    }

    $planName = $client->subscription->name ?? '—';
    $nasIp = $clientInfo['last_session']['nasipaddress'] ?? $clientInfo['last_session']['nas'] ?? '—';
    $sessionStart = $clientInfo['last_session']['acctstarttime'] ?? ($activeSessions[0]->acctstarttime ?? null);

    // Get daily traffic for last 3 days
    $dailyTraffic = \DB::connection('radius')->select("
        SELECT DATE(acctstarttime) as day, COUNT(*) as sessions,
               COALESCE(SUM(acctinputoctets),0) as upload,
               COALESCE(SUM(acctoutputoctets),0) as download,
               COALESCE(SUM(acctsessiontime),0) as total_seconds
        FROM radacct WHERE username = ? AND acctstoptime IS NOT NULL
        GROUP BY DATE(acctstarttime) ORDER BY day DESC LIMIT 3
    ", [$client->sas_username]);

    // Get current speed
    $currentSpeed = \DB::connection('radius')->table('radreply')
        ->where('username', $client->sas_username)
        ->where('attribute', 'Mikrotik-Rate-Limit')
        ->value('value') ?? ($client->subscription->name ?? '—');
@endphp

{{-- Overview Card --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-info-circle text-primary"></i> {{ trans('clients.client_information') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <span class="badge fs-6 px-3 py-2 {{ $isOnline ? 'bg-success' : 'bg-secondary' }}">
                    <span class="online-dot {{ $isOnline ? 'online' : 'offline' }}"></span>
                    {{ $isOnline ? ' متصل' : ' غير متصل' }}
                    @if($liveData) <small class="ms-2 opacity-75">(من CHR مباشرة)</small> @endif
                </span>
            </div>
            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                <span class="text-muted"><i class="bi bi-person"></i> {{ $client->sas_username }}</span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <small class="text-muted d-block"><i class="bi bi-globe"></i> IP</small>
                <strong>{{ $liveAddress }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block"><i class="bi bi-router"></i> الراوتر</small>
                <strong>{{ $nasIp }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block"><i class="bi bi-clock"></i> بدأ الجلسة</small>
                <strong>{{ $sessionStart ? date('Y-m-d H:i', strtotime($sessionStart)) : '—' }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block"><i class="bi bi-speedometer2"></i> {{ trans('clients.plan') }}</small>
                <strong>{{ $planName }}</strong>
            </div>
            @if($liveMac != '—')
            <div class="col-12">
                <small class="text-muted d-block"><i class="bi bi-motherboard"></i> كرت الشبكة</small>
                <strong class="text-muted small">{{ $liveMac }}</strong>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-4 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-calendar-day fs-1 text-primary"></i>
                <h5 class="mt-2 mb-0 fw-bold">{{ formatBytesHelper($totalToday) }}</h5>
                <small class="text-muted">استهلاك اليوم</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-calendar-range fs-1 text-success"></i>
                <h5 class="mt-2 mb-0 fw-bold">{{ formatBytesHelper($totalMonth) }}</h5>
                <small class="text-muted">استهلاك الشهر</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-layers fs-1 text-info"></i>
                <h5 class="mt-2 mb-0 fw-bold">{{ count($activeSessions) }}</h5>
                <small class="text-muted">الجلسات النشطة</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-arrow-down-up fs-1 text-warning"></i>
                <h5 class="mt-2 mb-0 fw-bold">{{ formatBytesHelper($dlMonth) }}</h5>
                <small class="text-muted">{{ formatBytesHelper($ulMonth) }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Active Sessions --}}
@if(count($activeSessions) > 0)
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-wifi text-success"></i> الجلسات النشطة
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Session</th>
                        <th> تحميل</th>
                        <th> رفع</th>
                        <th>IP</th>
                        <th>المدة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeSessions as $session)
                    <tr>
                        <td>#{{ $session->radacctid }}</td>
                        <td>{{ formatBytesHelper($session->acctoutputoctets) }}</td>
                        <td>{{ formatBytesHelper($session->acctinputoctets) }}</td>
                        <td><code class="small">{{ $session->framedipaddress }}</code></td>
                        <td>@php $h = floor($session->acctsessiontime / 3600); $m = floor(($session->acctsessiontime % 3600) / 60); @endphp {{ $h }}h {{ $m }}m</td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger px-2 py-0" onclick="radiusDisconnect({{ $client->id }})" title="قطع">🔌</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Last 3 Days Traffic --}}
@if(count($dailyTraffic) > 0)
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-bar-chart-line text-info"></i> استهلاك آخر {{ count($dailyTraffic) }} أيام
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="accordion" id="dailyTrafficAccordion">
            @foreach($dailyTraffic as $idx => $day)
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-white py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#day{{ $idx }}">
                        <div class="d-flex w-100 justify-content-between align-items-center small">
                            <span><i class="bi bi-calendar3 me-1"></i> {{ $day->day }}</span>
                            <span>
                                <span class="badge bg-success-soft text-success me-1">{{ formatBytesHelper($day->download) }}</span>
                                <span class="badge bg-info-soft text-info me-1">{{ formatBytesHelper($day->upload) }}</span>
                                <span class="badge bg-secondary">{{ $day->sessions }} جلسات</span>
                            </span>
                        </div>
                    </button>
                </h2>
                @php
                    $daySessions = \DB::connection('radius')->select("
                        SELECT acctstarttime, acctstoptime, acctinputoctets, acctoutputoctets,
                               acctsessiontime, acctterminatecause, framedipaddress
                        FROM radacct WHERE username = ? AND DATE(acctstarttime) = ?
                        ORDER BY acctstarttime DESC LIMIT 5
                    ", [$client->sas_username, $day->day]);
                @endphp
                <div id="day{{ $idx }}" class="accordion-collapse collapse" data-bs-parent="#dailyTrafficAccordion">
                    <div class="accordion-body p-2 small">
                        @foreach($daySessions as $s)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                            <div>{{ date('H:i', strtotime($s->acctstarttime)) }}@if($s->acctstoptime) → {{ date('H:i', strtotime($s->acctstoptime)) }} @else <span class="text-success">(ONLINE)</span> @endif</div>
                            <div> {{ formatBytesHelper($s->acctoutputoctets) }} {{ formatBytesHelper($s->acctinputoctets) }}</div>
                            <div class="text-muted">{{ $s->framedipaddress }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- RADIUS Actions --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-lightning text-warning"></i> الإجراءات
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 mb-1">
                <small class="text-muted">السرعة الحالية: <strong><span class="text-primary">{{ $currentSpeed }}</span></strong></small>
            </div>
            @if($isOnline)
            <div class="col-6">
                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="radiusDisconnect({{ $client->id }})">🔌 قطع</button>
            </div>
            @endif
            <div class="col-6">
                <button type="button" class="btn btn-outline-warning btn-sm w-100" onclick="radiusToggle({{ $client->id }})">⏸️ {{ $client->is_active ? 'تعطيل' : 'تفعيل' }}</button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="radiusChangeSpeed({{ $client->id }}, '10M/10M')">⚡ 10M</button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="radiusChangeSpeed({{ $client->id }}, '20M/20M')">⚡ 20M</button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="radiusChangeSpeed({{ $client->id }}, '50M/50M')">⚡ 50M</button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="radiusScheduleStop({{ $client->id }})">📅 جدولة إيقاف</button>
            </div>
        </div>
    </div>
</div>
