<table class="table s-table" id="disconnectedSessionsTable">
    <thead>
        <tr>
            <th style="width:40px;">#</th>
            <th>اسم المستخدم</th>
            <th>IP</th>
            <th>الراوتر (NAS)</th>
            <th>المدة</th>
            <th>وقت الانتهاء</th>
            <th>التحميل</th>
            <th>الرفع</th>
            <th>السبب</th>
        </tr>
    </thead>
    <tbody>
        @forelse($disconnectedSessions as $session)
            <tr>
                <td><span class="text-muted fw-bold" style="font-size:0.8rem;">{{ $loop->iteration }}</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="s-dot offline"></span>
                        <div>
                            <span class="user-name">{{ $session->username }}</span>
                            @if(isset($clients[$session->username]))
                                <br>
                                <small class="text-muted" style="font-size:0.75rem;">
                                    <i class="bi bi-person"></i> {{ $clients[$session->username] }}
                                </small>
                            @endif
                            <span class="user-sid">{{ $session->acctsessionid }}</span>
                        </div>
                    </div>
                </td>
                <td><span class="s-ip">{{ $session->framedipaddress }}</span></td>
                <td>
                    @if(isset($nasList[$session->nasipaddress]))
                        <span class="badge-session nas">
                            <i class="fas fa-network-wired"></i>
                            {{ $nasList[$session->nasipaddress]->shortname }}
                        </span>
                        <span class="s-nas-ip">{{ $session->nasipaddress }}</span>
                    @else
                        <span class="s-ip">{{ $session->nasipaddress }}</span>
                    @endif
                </td>
                <td>
                    @if($session->acctstarttime && $session->acctstoptime)
                        @php
                            $duration = \Carbon\Carbon::parse($session->acctstarttime)->diffInMinutes($session->acctstoptime);
                        @endphp
                        <span class="badge-session duration">
                            <i class="fas fa-clock"></i>{{ floor($duration / 60) }}h {{ $duration % 60 }}m
                        </span>
                    @elseif($session->acctsessiontime)
                        <span class="badge-session duration">
                            <i class="fas fa-clock"></i>{{ floor($session->acctsessiontime / 3600) }}h {{ floor(($session->acctsessiontime % 3600) / 60) }}m
                        </span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td>
                    @if($session->acctstoptime)
                        <span class="text-muted small">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($session->acctstoptime)->format('Y-m-d H:i') }}
                        </span>
                        <br>
                        <span class="badge bg-light text-muted" style="font-size:0.7rem;">
                            {{ \Carbon\Carbon::parse($session->acctstoptime)->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td class="traffic-down"><span class="traffic-val">{{ formatBytes($session->acctinputoctets ?? 0) }}</span></td>
                <td class="traffic-up"><span class="traffic-val">{{ formatBytes($session->acctoutputoctets ?? 0) }}</span></td>
                <td>
                    @php
                        $cause = $session->acctterminatecause ?? 'Unknown';
                        $badgeClass = match (true) {
                            str_contains($cause, 'Admin') => 'cause-admin',
                            str_contains($cause, 'User-Request') => 'cause-user',
                            str_contains($cause, 'Lost-Carrier') => 'cause-carrier',
                            str_contains($cause, 'Idle-Timeout') => 'cause-idle',
                            default => 'cause-other',
                        };
                    @endphp
                    <span class="badge-session {{ $badgeClass }}">
                        {{ $cause }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="s-empty">
                    <i class="fas fa-history"></i>
                    <h6>لا توجد جلسات منتهية</h6>
                    <p>خلال آخر 7 أيام</p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>