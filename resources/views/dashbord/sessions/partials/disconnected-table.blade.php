<table class=table table-hover align-middle mb-0 id=disconnectedSessionsTable>
    <thead class=table-dark>
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
        @forelse( as )
            <tr>
                <td>{{ ->iteration }}</td>
                <td>
                    <strong>{{ ->username }}</strong>
                    <br>
                    <small class=text-muted>{{ ->acctsessionid }}</small>
                </td>
                <td><code>{{ ->framedipaddress }}</code></td>
                <td>
                    @if(isset([->nasipaddress]))
                        <span class=badge bg-info>{{ [->nasipaddress]->shortname }}</span>
                        <br>
                        <small class=text-muted>{{ ->nasipaddress }}</small>
                    @else
                        <span class=badge bg-secondary>{{ ->nasipaddress }}</span>
                    @endif
                </td>
                <td>
                    @if(->acctstarttime && ->acctstoptime)
                        @php
                             = \Carbon\Carbon::parse(->acctstarttime)->diffInMinutes(->acctstoptime);
                        @endphp
                        <span class=badge bg-secondary>
                            <i class=bi bi-clock me-1></i>{{ floor( / 60) }}h {{  % 60 }}m
                        </span>
                    @elseif(->acctsessiontime)
                        <span class=badge bg-secondary>
                            <i class=bi bi-clock me-1></i>{{ floor(->acctsessiontime / 3600) }}h {{ floor((->acctsessiontime % 3600) / 60) }}m
                        </span>
                    @else
                        <span class=text-muted>-</span>
                    @endif
                </td>
                <td>
                    @if(->acctstoptime)
                        <span class=text-muted small>
                            <i class=bi bi-calendar-check me-1></i>
                            {{ \Carbon\Carbon::parse(->acctstoptime)->format('Y-m-d H:i') }}
                        </span>
                        <br>
                        <span class=badge bg-light text-muted>
                            {{ \Carbon\Carbon::parse(->acctstoptime)->diffForHumans() }}
                        </span>
                    @else
                        <span class=text-muted>-</span>
                    @endif
                </td>
                <td class=text-nowrap>
                    <span class=text-success small>{{ formatBytes(->acctinputoctets ?? 0) }}</span>
                </td>
                <td class=text-nowrap>
                    <span class=text-warning small>{{ formatBytes(->acctoutputoctets ?? 0) }}</span>
                </td>
                <td>
                    @php
                         = ->acctterminatecause ?? 'Unknown';
                         = match (true) {
                            str_contains(, 'Admin') => 'bg-danger',
                            str_contains(, 'User-Request') => 'bg-warning text-dark',
                            str_contains(, 'Lost-Carrier') => 'bg-secondary',
                            str_contains(, 'Idle-Timeout') => 'bg-info',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <span class=badge {{ }}>
                        {{  }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan=9 class=text-center py-4 text-muted>
                    <i class=bi bi-emoji-neutral fs-3 d-block mb-2></i>
                    لا توجد جلسات منتهية في آخر 7 أيام
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
