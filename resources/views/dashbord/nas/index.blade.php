@extends("dashbord.layouts.master")

@section("content")
<style>
    :root {
        --nas-online: #10b981;
        --nas-offline: #ef4444;
        --nas-card-bg: #ffffff;
        --nas-border: #e5e7eb;
        --nas-shadow: 0 1px 3px rgba(0,0,0,0.08);
        --nas-shadow-hover: 0 4px 12px rgba(0,0,0,0.12);
    }

    {{-- NAS Header Card --}}
    .nas-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border: none;
        border-radius: 16px;
        margin-bottom: 28px;
        box-shadow: 0 4px 16px rgba(30,41,59,0.25);
    }
    .nas-header .card-title {
        color: #f1f5f9;
        font-size: 1.3rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .nas-header .card-title i {
        color: #3b82f6;
        font-size: 1.5rem;
    }
    .nas-header .btn-add {
        background: #3b82f6;
        border: none;
        border-radius: 10px;
        padding: 8px 18px;
        font-weight: 600;
        color: #fff;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .nas-header .btn-add:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59,130,246,0.4);
    }

    {{-- Stats Mini Row --}}
    .nas-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .nas-stat-card {
        flex: 1;
        min-width: 140px;
        background: #fff;
        border: 1px solid var(--nas-border);
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: var(--nas-shadow);
        transition: all 0.2s;
    }
    .nas-stat-card:hover {
        box-shadow: var(--nas-shadow-hover);
    }
    .nas-stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .nas-stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }
    .nas-stat-value .stat-unit {
        font-size: 0.85rem;
        font-weight: 600;
        color: #9ca3af;
    }
    .nas-stat-icon {
        font-size: 1.6rem;
        opacity: 0.25;
    }

    {{-- NAS Table --}}
    .nas-table-container {
        background: #fff;
        border: 1px solid var(--nas-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--nas-shadow);
    }
    .nas-table {
        margin-bottom: 0;
    }
    .nas-table thead {
        background: #f8fafc;
    }
    .nas-table thead th {
        padding: 12px 16px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .nas-table tbody tr {
        transition: background 0.15s ease;
        border-bottom: 1px solid #f1f5f9;
    }
    .nas-table tbody tr:last-child {
        border-bottom: none;
    }
    .nas-table tbody tr:hover {
        background: #f8fafc;
    }
    .nas-table tbody td {
        padding: 14px 16px;
        font-size: 0.875rem;
        color: #1e293b;
        vertical-align: middle;
    }

    {{-- Status Badge --}}
    .nas-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .nas-status.online {
        background: #ecfdf5;
        color: #059669;
    }
    .nas-status.offline {
        background: #fef2f2;
        color: #dc2626;
    }
    .nas-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .nas-status.online .nas-status-dot {
        background: var(--nas-online);
        box-shadow: 0 0 6px rgba(16,185,129,0.5);
        animation: pulse-online 2s infinite;
    }
    .nas-status.offline .nas-status-dot {
        background: var(--nas-offline);
    }

    @keyframes pulse-online {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    {{-- Secret --}}
    .nas-secret {
        font-family: "SF Mono", "Fira Code", monospace;
        font-size: 0.8rem;
        background: #f1f5f9;
        padding: 3px 8px;
        border-radius: 6px;
        color: #475569;
        direction: ltr;
        display: inline-block;
    }

    {{-- Sessions Count --}}
    .session-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.78rem;
        background: #f0f9ff;
        color: #0284c7;
    }
    .session-badge.has-sessions {
        background: #ecfdf5;
        color: #059669;
    }

    {{-- IP Tag --}}
    .nas-ip {
        font-family: "SF Mono", "Fira Code", monospace;
        font-size: 0.85rem;
        font-weight: 600;
        color: #0f172a;
        direction: ltr;
        display: inline-block;
    }

    {{-- Action Buttons --}}
    .btn-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .btn-action-edit {
        background: #fef3c7;
        color: #d97706;
    }
    .btn-action-edit:hover {
        background: #fde68a;
        color: #b45309;
    }
    .btn-action-delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .btn-action-delete:hover {
        background: #fca5a5;
        color: #b91c1c;
    }

    {{-- Empty State --}}
    .nas-empty {
        padding: 48px 20px;
        text-align: center;
    }
    .nas-empty i {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 12px;
    }
    .nas-empty h6 {
        color: #64748b;
        font-weight: 600;
    }
    .nas-empty p {
        color: #94a3b8;
        font-size: 0.875rem;
    }

    {{-- Alert --}}
    .nas-alert {
        border-radius: 10px;
        border: none;
        border-right: 4px solid #059669;
        font-size: 0.875rem;
    }

    .type-tag {
        background: #e2e8f0;
        padding: 2px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        display: inline-block;
        text-transform: uppercase;
    }
</style>

<div class="container-fluid">

    @if(session("success"))
        <div class="alert nas-alert alert-success d-flex align-items-center gap-2">
            <i class="fas fa-check-circle text-success"></i> {{ session("success") }}
        </div>
    @endif

    {{-- Header Card --}}
    <div class="card nas-header">
        <div class="card-body d-flex justify-content-between align-items-center py-3 px-4">
            <h5 class="card-title mb-0">
                <i class="fas fa-network-wired"></i>
                أجهزة NAS (الرواتر)
                <span class="badge bg-light text-dark ms-2" style="font-size:0.7rem;">{{ count($nasDevices) }}</span>
            </h5>
            <a href="{{ route("admin.nas.create") }}" class="btn-add">
                <i class="fas fa-plus"></i> إضافة جهاز
            </a>
        </div>
    </div>

    @php
        $totalNas = count($nasDevices);
        $onlineCount = count(array_filter($statuses, fn($s) => $s["online"]));
        $offlineCount = $totalNas - $onlineCount;
        $totalSessions = array_sum(array_column($statuses, "active_sessions"));
    @endphp
    <div class="nas-stats">
        <div class="nas-stat-card d-flex justify-content-between align-items-center">
            <div>
                <div class="nas-stat-label">إجمالي الأجهزة</div>
                <div class="nas-stat-value">{{ $totalNas }} <span class="stat-unit">جهاز</span></div>
            </div>
            <i class="fas fa-server nas-stat-icon"></i>
        </div>
        <div class="nas-stat-card d-flex justify-content-between align-items-center">
            <div>
                <div class="nas-stat-label">متصل</div>
                <div class="nas-stat-value" style="color: var(--nas-online);">{{ $onlineCount }} <span class="stat-unit">جهاز</span></div>
            </div>
            <i class="fas fa-wifi nas-stat-icon" style="color: var(--nas-online); opacity:0.3;"></i>
        </div>
        <div class="nas-stat-card d-flex justify-content-between align-items-center">
            <div>
                <div class="nas-stat-label">غير متصل</div>
                <div class="nas-stat-value" style="color: var(--nas-offline);">{{ $offlineCount }} <span class="stat-unit">جهاز</span></div>
            </div>
            <i class="fas fa-wifi-slash nas-stat-icon" style="color: var(--nas-offline); opacity:0.3;"></i>
        </div>
        <div class="nas-stat-card d-flex justify-content-between align-items-center">
            <div>
                <div class="nas-stat-label">جلسات نشطة</div>
                <div class="nas-stat-value" style="color: #3b82f6;">{{ $totalSessions }} <span class="stat-unit">{{ $totalSessions == 1 ? "جلسة" : "جلسات" }}</span></div>
            </div>
            <i class="fas fa-users nas-stat-icon" style="opacity:0.2;"></i>
        </div>
    </div>

    <div class="nas-table-container">
        @if(count($nasDevices) > 0)
        <div class="table-responsive">
            <table class="table nas-table">
                <thead>
                    <tr>
                        <th>الحالة</th>
                        <th>IP Address</th>
                        <th>الاسم المختصر</th>
                        <th>النوع</th>
                        <th>RTT</th>
                        <th>Packet Loss</th>
                        <th>OMS</th>
                        <th>الجلسات</th>
                        <th>الوصف</th>
                        <th style="width:90px;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nasDevices as $nas)
                        @php $s = $statuses[$nas->id] ?? ["online" => false, "active_sessions" => 0, "rtt" => 0, "packet_loss" => 100, "oms" => "offline"]; @endphp
                        <tr>
                            <td>
                                <span class="nas-status {{ $s["online"] ? "online" : "offline" }}">
                                    <span class="nas-status-dot"></span>
                                    {{ $s["online"] ? "متصل" : "غير متصل" }}
                                </span>
                            </td>
                            <td><span class="nas-ip">{{ $nas->nasname }}</span></td>
                            <td><strong>{{ $nas->shortname }}</strong></td>
                            <td><span class="type-tag">{{ $nas->type }}</span></td>
                            <td>
                                @if($s["online"])
                                    <span class="fw-bold {{ $s["rtt"] < 80 ? "text-success" : ($s["rtt"] < 200 ? "text-warning" : "text-danger") }}">{{ $s["rtt"] }}ms</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($s["online"])
                                    <span class="fw-bold {{ $s["packet_loss"] == 0 ? "text-success" : ($s["packet_loss"] < 5 ? "text-warning" : "text-danger") }}">{{ $s["packet_loss"] }}%</span>
                                @else
                                    <span class="text-muted">100%</span>
                                @endif
                            </td>
                            <td>
                                @if($s["oms"] == "excellent")
                                    <span class="badge bg-success">ممتاز</span>
                                @elseif($s["oms"] == "good")
                                    <span class="badge bg-primary">جيد</span>
                                @elseif($s["oms"] == "fair")
                                    <span class="badge bg-warning">ضعيف</span>
                                @elseif($s["oms"] == "poor")
                                    <span class="badge bg-danger">سيء</span>
                                @else
                                    <span class="badge bg-secondary">غير متصل</span>
                                @endif
                            </td>
                            <td><span class="nas-secret" title="انقر لعرض كامل">••••••••</span></td>
                            <td>
                                <span class="session-badge {{ $s["active_sessions"] > 0 ? "has-sessions" : "" }}">
                                    <i class="fas fa-{{ $s["active_sessions"] > 0 ? "circle" : "circle-notch" }}"></i>
                                    {{ $s["active_sessions"] }}
                                </span>
                            </td>
                            <td style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $nas->description }}">
                                {{ $nas->description ?? "—" }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route("admin.nas.edit", $nas->id) }}" class="btn-action btn-action-edit" title="تعديل">
                                        <i class="fas fa-pen" style="font-size:0.75rem;"></i>
                                    </a>
                                    <form action="{{ route("admin.nas.destroy", $nas->id) }}" method="POST"
                                          onsubmit="return confirm(هل أنت متأكد من حذف جهاز NAS؟)" style="display:inline;">
                                        @csrf @method("DELETE")
                                        <button class="btn-action btn-action-delete" title="حذف">
                                            <i class="fas fa-trash" style="font-size:0.75rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="nas-empty">
                <i class="fas fa-router"></i>
                <h6>لا توجد أجهزة NAS مضافة بعد</h6>
                <p>أضف أول جهاز للبدء بإدارة الجلسات والمصادقة عبر RADIUS</p>
                <a href="{{ route("admin.nas.create") }}" class="btn btn-primary btn-sm mt-2">
                    <i class="fas fa-plus"></i> أضف أول جهاز
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
