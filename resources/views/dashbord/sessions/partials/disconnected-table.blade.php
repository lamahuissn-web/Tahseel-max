<table class="table table-hover align-middle mb-0" id="disconnectedSessionsTable">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>اسم المستخدم</th>
            <th>IP</th>
            <th>الراوتر (NAS)</th>
            <th>المدة</th>
            <th>وقت الانتهاء</th>
            <th>التحميل 📥</th>
            <th>الرفع 📤</th>
            <th>سبب القطع</th>
        </tr>
    </thead>
    <tbody>
        @forelse($disconnectedSessions as $session)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <strong>{{ $session->username }}</strong>
                    <br>
                    <small class="text-muted">{{ $session->acctsessionid }}</small>
                </td>
                <td><code>{{ $session->framedipaddress }}</code></td>
                <td>
                    @if(isset($nasList[$session->nasipaddress]))
                        <span class="badge bg-info">{{ $nasList[$session->nasipaddress]->shortname }}</span>
                        <br>
                        <small class="text-muted">{{ $session->nasipaddress }}</small>
                    @else
                        <span class="badge bg-secondary">{{ $session->nasipaddress }}</span>
                    @endif
                </td>
                <td>
                    @if($session->acctstarttime && $session->acctstoptime)
                        @php
                            $duration = \Carbon\Carbon::parse($session->acctstarttime)->diffInMinutes($session->acctstoptime);
                        @endphp
                        <span class="badge bg-secondary">
                            <i class="bi bi-clock me-1"></i>{{ floor($duration / 60) }}h {{ $duration % 60 }}m
                        </span>
                    @elseif($session->acctsessiontime)
                        <span class="badge bg-secondary">
                            <i class="bi bi-clock me-1"></i>{{ floor($session->acctsessiontime / 3600) }}h {{ floor(($session->acctsessiontime % 3600) / 60) }}m
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($session->acctstoptime)
                        <span class="text-muted small">
                            <i class="bi bi-calendar-check me-1"></i>
                            {{ \Carbon\Carbon::parse($session->acctstoptime)->format('Y-m-d H:i') }}
                        </span>
                        <br>
                        <span class="badge bg-light text-muted">
                            {{ \Carbon\Carbon::parse($session->acctstoptime)->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-nowrap">
                    <span class="text-success small">{{ formatBytes($session->acctinputoctets ?? 0) }}</span>
                </td>
                <td class="text-nowrap">
                    <span class="text-warning small">{{ formatBytes($session->acctoutputoctets ?? 0) }}</span>
                </td>
                <td>
                    @php
                        $cause = $session->acctterminatecause ?? 'Unknown';
                        $causeBadge = match (true) {
                            str_contains($cause, 'Admin') => 'bg-danger',
                            str_contains($cause, 'User-Request') => 'bg-warning text-dark',
                            str_contains($cause, 'Lost-Carrier') => 'bg-secondary',
                            str_contains($cause, 'Idle-Timeout') => 'bg-info',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $causeBadge }}">
                        {{ $cause }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                    <i class="bi bi-emoji-neutral fs-3 d-block mb-2"></i>
                    لا توجد جلسات منتهية في آخر 7 أيام
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
