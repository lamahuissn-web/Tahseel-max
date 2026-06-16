@extends("dashbord.layouts.master")

@section("content")
<style>
    :root {
        --s-green: #10b981;
        --s-red: #ef4444;
        --s-blue: #3b82f6;
        --s-yellow: #f59e0b;
        --s-purple: #8b5cf6;
    }

    .s-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border: none;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(30,41,59,0.25);
    }
    .s-header .card-title {
        color: #f1f5f9;
        font-size: 1.3rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    .s-header .card-title i { color: #3b82f6; font-size: 1.5rem; }
    .s-auto-note {
        color: #94a3b8;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .s-stats { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
    .s-stat-card {
        flex: 1; min-width: 140px; background: #fff;
        border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: all 0.2s;
    }
    .s-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
    .s-stat-label {
        font-size: 0.75rem; font-weight: 600; color: #6b7280;
        text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;
    }
    .s-stat-value { font-size: 1.6rem; font-weight: 800; color: #1e293b; line-height: 1.2; }
    .s-stat-value .unit { font-size: 0.85rem; font-weight: 600; color: #9ca3af; }
    .s-stat-icon { font-size: 1.6rem; opacity: 0.25; }

    .s-tabs {
        display: flex; gap: 4px; border-bottom: 2px solid #f1f5f9;
        padding: 0 20px;
    }
    .s-tab {
        padding: 12px 20px; font-size: 0.85rem; font-weight: 600;
        color: #64748b; border: none; background: none; cursor: pointer;
        border-bottom: 2px solid transparent; margin-bottom: -2px;
        transition: all 0.2s; display: flex; align-items: center; gap: 6px;
    }
    .s-tab:hover { color: #3b82f6; background: rgba(59,130,246,0.05); }
    .s-tab.active { color: #3b82f6; border-bottom-color: #3b82f6; }
    .s-tab .badge {
        font-size: 0.7rem; font-weight: 700; padding: 2px 8px;
        border-radius: 10px;
    }
    .s-tab.active .badge { background: #3b82f6; color: #fff; }
    .s-tab .badge.bg-green { background: #ecfdf5; color: #059669; }
    .s-tab .badge.bg-gray { background: #f1f5f9; color: #64748b; }

    .s-card {
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .s-dot {
        width: 8px; height: 8px; border-radius: 50%; display: inline-block;
        flex-shrink: 0;
    }
    .s-dot.online { background: var(--s-green); box-shadow: 0 0 6px rgba(16,185,129,0.5); animation: pulse-s 2s infinite; }
    @keyframes pulse-s { 0%,100%{opacity:1} 50%{opacity:0.4} }
    .s-dot.offline { background: var(--s-red); }

    .s-table { margin-bottom: 0; }
    .s-table thead { background: #f8fafc; }
    .s-table thead th {
        padding: 12px 16px; font-size: 0.75rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.4px; color: #64748b;
        border-bottom: 2px solid #e2e8f0; white-space: nowrap;
    }
    .s-table tbody tr { transition: background 0.15s; border-bottom: 1px solid #f1f5f9; }
    .s-table tbody tr:last-child { border-bottom: none; }
    .s-table tbody tr:hover { background: #f8fafc; }
    .s-table tbody td { padding: 14px 16px; font-size: 0.85rem; color: #1e293b; vertical-align: middle; }
    .s-table .user-name { font-weight: 700; color: #0f172a; display: block; }
    .s-table .user-sid { font-size: 0.7rem; color: #94a3b8; }

    .badge-session { font-weight: 600; font-size: 0.75rem; padding: 4px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px; }
    .badge-session.duration { background: #ecfdf5; color: #059669; }
    .badge-session.nas { background: #f0f9ff; color: #0284c7; }
    .badge-session.cause-admin { background: #fef2f2; color: #dc2626; }
    .badge-session.cause-user { background: #fffbeb; color: #d97706; }
    .badge-session.cause-carrier { background: #f1f5f9; color: #64748b; }
    .badge-session.cause-idle { background: #f0f9ff; color: #0284c7; }
    .badge-session.cause-other { background: #f1f5f9; color: #64748b; }

    .traffic-val { font-family: "SF Mono", "Fira Code", monospace; font-weight: 600; font-size: 0.8rem; }
    .traffic-down .traffic-val { color: var(--s-blue); }
    .traffic-up .traffic-val { color: var(--s-yellow); }

    .btn-s {
        width: 32px; height: 32px; border-radius: 8px; border: none;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all 0.15s; font-size: 0.75rem;
    }
    .btn-s-speed { background: #fef3c7; color: #d97706; }
    .btn-s-speed:hover { background: #fde68a; }
    .btn-s-disconnect { background: #fee2e2; color: #dc2626; }
    .btn-s-disconnect:hover { background: #fca5a5; }

    .s-empty { padding: 48px 20px; text-align: center; }
    .s-empty i { font-size: 3rem; color: #cbd5e1; margin-bottom: 12px; }
    .s-empty h6 { color: #64748b; font-weight: 600; }
    .s-empty p { color: #94a3b8; font-size: 0.875rem; }

    .s-ip {
        font-family: "SF Mono", "Fira Code", monospace; direction: ltr; display: inline-block;
        background: #f1f5f9; padding: 2px 8px; border-radius: 6px;
        font-size: 0.8rem; color: #475569;
    }
    .s-nas-ip { font-size: 0.7rem; color: #94a3b8; display: block; margin-top: 2px; }
</style>

<div class="container-fluid">

    @if(session("success"))
        <div class="alert alert-success d-flex align-items-center gap-2 shadow-sm" style="border:none;border-radius:10px;border-right:4px solid #059669;">
            <i class="fas fa-check-circle text-success"></i> {{ session("success") }}
        </div>
    @endif
    @if(session("error"))
        <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm" style="border:none;border-radius:10px;border-right:4px solid #dc2626;">
            <i class="fas fa-exclamation-triangle text-danger"></i> {{ session("error") }}
        </div>
    @endif

    <div class="card s-header">
        <div class="card-body d-flex justify-content-between align-items-center py-3 px-4">
            <h5 class="card-title mb-0">
                <i class="fas fa-wifi"></i>
                الجلسات
                <span class="badge bg-light text-dark ms-2" style="font-size:0.7rem;">{{ $totalOnline }} نشطة</span>
            </h5>
            <div class="d-flex align-items-center gap-3">
                <span class="s-auto-note">
                    <i class="fas fa-sync-alt" style="font-size:0.7rem;"></i>
                    تلقائي كل 30 ثانية
                </span>
                <a href="{{ route("admin.sessions.index") }}" class="btn btn-sm text-white" style="background:#3b82f6;border:none;border-radius:10px;padding:6px 14px;font-weight:600;">
                    <i class="fas fa-redo-alt"></i> تحديث
                </a>
            </div>
        </div>
    </div>

        {{-- Router Live Health --}}
    @if($routerStats)
    <div class="row g-3 mb-4" id="routerHealth">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <span class="badge bg-dark fs-6 px-3 py-2"><i class="bi bi-router text-warning"></i> CHR (192.168.0.51)</span>
                        </div>
                        <div class="col">
                            <div class="row g-2 text-center">
                                <div class="col-3 col-md">
                                    <small class="text-muted d-block">CPU</small>
                                    <strong class="text-primary">{{ $routerStats['cpu'] }}</strong>
                                </div>
                                <div class="col-3 col-md">
                                    <small class="text-muted d-block">RAM حر</small>
                                    <strong class="text-success">{{ $routerStats['free_memory'] }}</strong>
                                </div>
                                <div class="col-3 col-md">
                                    <small class="text-muted d-block">HDD حر</small>
                                    <strong class="text-info">{{ $routerStats['free_hdd'] }}</strong>
                                </div>
                                <div class="col-3 col-md">
                                    <small class="text-muted d-block">PPPoE</small>
                                    <strong class="text-warning">{{ $routerStats['ppp_active'] }}</strong>
                                </div>
                                <div class="col-3 col-md">
                                    <small class="text-muted d-block">Hotspot</small>
                                    <strong class="text-secondary">{{ $routerStats['hotspot_active'] }}</strong>
                                </div>
                                <div class="col-3 col-md">
                                    <small class="text-muted d-block">الإجمالي</small>
                                    <strong class="fw-bold">{{ $routerStats['total_active'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted"><i class="bi bi-lightning-fill text-warning me-1"></i>Live</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif


<div class="s-stats">
        <div class="s-stat-card d-flex justify-content-between align-items-center">
            <div>
                <div class="s-stat-label"><i class="fas fa-wifi me-1"></i>متصل الآن</div>
                <div class="s-stat-value" style="color:var(--s-green);">{{ $totalOnline }} <span class="unit">جهاز</span></div>
            </div>
            <i class="fas fa-wifi s-stat-icon" style="color:var(--s-green);opacity:0.3;"></i>
        </div>
        <div class="s-stat-card d-flex justify-content-between align-items-center">
            <div>
                <div class="s-stat-label"><i class="fas fa-clock me-1"></i>منتهية (7 أيام)</div>
                <div class="s-stat-value" style="color:var(--s-blue);">{{ $totalDisconnected }} <span class="unit">جلسة</span></div>
            </div>
            <i class="fas fa-history s-stat-icon" style="color:var(--s-blue);opacity:0.2;"></i>
        </div>
        <div class="s-stat-card d-flex justify-content-between align-items-center">
            <div>
                <div class="s-stat-label"><i class="fas fa-download me-1"></i>إجمالي التحميل</div>
                <div class="s-stat-value" style="font-size:1.1rem;">{{ formatBytes($totalDown) }}</div>
            </div>
            <i class="fas fa-arrow-down s-stat-icon" style="color:var(--s-blue);opacity:0.2;"></i>
        </div>
        <div class="s-stat-card d-flex justify-content-between align-items-center">
            <div>
                <div class="s-stat-label"><i class="fas fa-upload me-1"></i>إجمالي الرفع</div>
                <div class="s-stat-value" style="font-size:1.1rem;">{{ formatBytes($totalUp) }}</div>
            </div>
            <i class="fas fa-arrow-up s-stat-icon" style="color:var(--s-yellow);opacity:0.2;"></i>
        </div>
    </div>

    <div class="s-card">
        <div class="s-tabs">
            <button class="s-tab active" data-tab="active" onclick="switchTab('active')">
                <i class="fas fa-wifi"></i>
                متصلون الآن
                <span class="badge ms-1 bg-green">{{ $totalOnline }}</span>
            </button>
            <button class="s-tab" data-tab="disconnected" onclick="switchTab('disconnected')">
                <i class="fas fa-clock"></i>
                جلسات منتهية
                <span class="badge ms-1 bg-gray">{{ $totalDisconnected }}</span>
            </button>
        </div>
        <div class="p-0">
            <div id="tab-active" class="p-3">
                <div class="table-responsive" id="sessionsTableContainer">
                    @include("dashbord.sessions.partials.table", ["sessions" => $sessions, "nasList" => $nasList])
                </div>
            </div>
            <div id="tab-disconnected" class="p-3 d-none">
                <div class="table-responsive" id="disconnectedSessionsTableContainer">
                    @include("dashbord.sessions.partials.disconnected-table", ["disconnectedSessions" => $disconnectedSessions, "nasList" => $nasList])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
let currentTab = "active";
let refreshInterval;

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll(".s-tab").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.tab === tab);
    });
    document.getElementById("tab-active").classList.toggle("d-none", tab !== "active");
    document.getElementById("tab-disconnected").classList.toggle("d-none", tab !== "disconnected");

    if (tab === "active") refreshActiveSessions();
    else refreshDisconnectedSessions();
}

function refreshActiveSessions() {
    fetch("{{ route("admin.sessions.refresh") }}?tab=active")
        .then(r => r.json())
        .then(data => {
            document.getElementById("sessionsTableContainer").innerHTML = data.html;
            document.querySelector(".s-tab[data-tab=active] .badge").textContent = data.total;
            document.querySelector(".s-stat-card:first-child .s-stat-value").innerHTML = data.total + ' <span class="unit">جهاز</span>';
        })
        .catch(e => console.error("Refresh error:", e));
}

function refreshDisconnectedSessions() {
    fetch("{{ route("admin.sessions.refresh") }}?tab=disconnected")
        .then(r => r.json())
        .then(data => {
            document.getElementById("disconnectedSessionsTableContainer").innerHTML = data.html;
            document.querySelector(".s-tab[data-tab=disconnected] .badge").textContent = data.total;
            document.querySelectorAll(".s-stat-card")[1].querySelector(".s-stat-value").innerHTML = data.total + ' <span class="unit">جلسة</span>';
        })
        .catch(e => console.error("Refresh error:", e));
}

refreshInterval = setInterval(() => {
    if (currentTab === "active") refreshActiveSessions();
}, 30000);

document.addEventListener("turbolinks:before-visit", function () { clearInterval(refreshInterval); });
</script>
@endsection
