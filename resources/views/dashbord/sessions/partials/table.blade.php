<table class="table table-hover table-bordered align-middle mb-0" id="sessionsTable">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>اسم المستخدم</th>
            <th>IP</th>
            <th>الراوتر (NAS)</th>
            <th>المدة</th>
            <th>التحميل 📥</th>
            <th>الرفع 📤</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sessions as $session)
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
                    @php
                        $uptime = $session->acctstarttime ? \Carbon\Carbon::parse($session->acctstarttime)->diffForHumans(null, true) : '-';
                    @endphp
                    <span class="badge bg-success-subtle text-success">
                        <i class="bi bi-clock me-1"></i>{{ $uptime }}
                    </span>
                </td>
                <td>{{ formatBytes($session->acctinputoctets ?? 0) }}</td>
                <td>{{ formatBytes($session->acctoutputoctets ?? 0) }}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('admin.sessions.change-speed', $session->username) }}"
                           class="btn btn-warning"
                           title="تغيير السرعة"
                           onclick="return confirm('تغيير سرعة {{ $session->username }}؟')">
                            <i class="bi bi-speedometer2"></i>
                        </a>
                        <a href="{{ route('admin.sessions.disconnect', $session->username) }}"
                           class="btn btn-danger"
                           title="قطع الاتصال"
                           onclick="return confirm('متأكد من قطع {{ $session->username }}؟')">
                            <i class="bi bi-plug-fill"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="bi bi-emoji-neutral fs-3 d-block mb-2"></i>
                    لا يوجد متصلين حالياً
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
