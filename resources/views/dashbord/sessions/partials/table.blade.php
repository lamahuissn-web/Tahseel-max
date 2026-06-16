<table class="table s-table" id="sessionsTable">
    <thead>
        <tr>
            <th style="width:40px;">#</th>
            <th>اسم المستخدم</th>
            <th>اسم الزبون</th>
            <th>IP</th>
            <th>الراوتر (NAS)</th>
            <th>المدة متصل</th>
            <th>التحميل</th>
            <th>الرفع</th>
            <th style="width:90px;">الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sessions as $session)
            <tr>
                <td><span class="text-muted fw-bold" style="font-size:0.8rem;">{{ $loop->iteration }}</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="s-dot online"></span>
                        <div>
                            <span class="user-name">{{ $session->username }}</span>
                            <span class="user-sid">{{ $session->acctsessionid }}</span>
                        </div>
                    </div>
                </td>
                <td>
                    @if(isset($clients[$session->username]))
                        <span class="fw-bold text-dark" style="font-size:0.9rem;">{{ $clients[$session->username] }}</span>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
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
                    @php
                        $uptime = $session->acctstarttime ? \Carbon\Carbon::parse($session->acctstarttime)->diffForHumans(null, true) : '-';
                    @endphp
                    <span class="badge-session duration">
                        <i class="fas fa-clock"></i>
                        {{ $uptime }}
                    </span>
                </td>
                <td class="traffic-down"><span class="traffic-val">{{ formatBytes($session->acctinputoctets ?? 0) }}</span></td>
                <td class="traffic-up"><span class="traffic-val">{{ formatBytes($session->acctoutputoctets ?? 0) }}</span></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route("admin.sessions.change-speed", $session->username) }}"
                           class="btn-s btn-s-speed" title="تغيير السرعة"
                           onclick="return confirm('تغيير سرعة {{ $session->username }}؟')">
                            <i class="fas fa-tachometer-alt"></i>
                        </a>
                        <a href="{{ route("admin.sessions.disconnect", $session->username) }}"
                           class="btn-s btn-s-disconnect" title="قطع الاتصال"
                           onclick="return confirm('متأكد من قطع {{ $session->username }}؟')">
                            <i class="fas fa-plug"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="s-empty">
                    <i class="fas fa-wifi-slash"></i>
                    <h6>لا يوجد متصلين حالياً</h6>
                    <p>عند اتصال أي جهاز سيظهر هنا</p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>