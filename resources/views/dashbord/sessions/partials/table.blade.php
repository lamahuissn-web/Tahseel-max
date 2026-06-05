<table class="table table-hover align-middle mb-0" id="sessionsTable">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>اسم المستخدم</th>
            <th>IP</th>
            <th>الراوتر (NAS)</th>
            <th>المدة متصل</th>
            <th>التحميل 📥</th>
            <th>الرفع 📤</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sessions as $session)
            <tr class="align-middle">
                <td>{{ $loop->iteration }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="online-indicator">
                            <span class="status-dot status-dot-online"></span>
                        </span>
                        <div>
                            <strong class="d-block">{{ $session->username }}</strong>
                            <small class="text-muted" style="font-size: 11px;">{{ $session->acctsessionid }}</small>
                        </div>
                    </div>
                </td>
                <td><code class="bg-light px-2 py-1 rounded">{{ $session->framedipaddress }}</code></td>
                <td>
                    @if(isset($nasList[$session->nasipaddress]))
                        <span class="badge bg-info-subtle text-info-emphasis px-2 py-1">
                            <i class="bi bi-router me-1"></i>{{ $nasList[$session->nasipaddress]->shortname }}
                        </span>
                        <br>
                        <small class="text-muted" style="font-size: 10px;">{{ $session->nasipaddress }}</small>
                    @else
                        <span class="badge bg-secondary">{{ $session->nasipaddress }}</span>
                    @endif
                </td>
                <td>
                    @php
                        $uptime = $session->acctstarttime ? \Carbon\Carbon::parse($session->acctstarttime)->diffForHumans(null, true) : "-";
                    @endphp
                    <span class="badge bg-success-subtle text-success-emphasis px-2 py-1">
                        <i class="bi bi-clock me-1"></i>{{ $uptime }}
                    </span>
                </td>
                <td class="text-nowrap">
                    <span class="text-success fw-semibold small">{{ formatBytes($session->acctinputoctets ?? 0) }}</span>
                </td>
                <td class="text-nowrap">
                    <span class="text-warning fw-semibold small">{{ formatBytes($session->acctoutputoctets ?? 0) }}</span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route("admin.sessions.change-speed", $session->username) }}"
                           class="btn btn-outline-warning"
                           title="تغيير السرعة"
                           onclick="return confirm("تغيير سرعة {{ $session->username }}؟")">
                            <i class="bi bi-speedometer2"></i>
                        </a>
                        <a href="{{ route("admin.sessions.disconnect", $session->username) }}"
                           class="btn btn-outline-danger"
                           title="قطع الاتصال"
                           onclick="return confirm("متأكد من قطع {{ $session->username }}؟")">
                            <i class="bi bi-plug-fill"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-wifi-off fs-1 d-block mb-2"></i>
                        <p class="mb-0">لا يوجد متصلين حالياً</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
