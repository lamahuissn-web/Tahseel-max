@extends("dashbord.layouts.master")

@section("css")
<style>
    .status-dot {
        width: 10px; height: 10px; border-radius: 50%; display: inline-block; flex-shrink: 0;
    }
    .status-dot-online {
        background: #28a745;
        box-shadow: 0 0 6px rgba(40, 167, 69, 0.5);
        animation: pulse-online 2s infinite;
    }
    @keyframes pulse-online {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .session-tabs {
        border-bottom: 2px solid #e9ecef;
    }
    .session-tab {
        position: relative;
        border: none;
        background: none;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: #6c757d;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .session-tab:hover {
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.05);
    }
    .session-tab.active {
        color: #0d6efd;
    }
    .session-tab.active::after {
        content: "";
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #0d6efd;
    }

    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .stat-card .stat-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
    }

    .traffic-value {
        font-family: "SF Mono", "Cascadia Code", monospace;
        font-size: 0.85rem;
    }

    .card { border-radius: 12px; }
    .card-header { border-radius: 12px 12px 0 0; }
</style>
@endsection

@section("toolbar")
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
        $title = "الجلسات";
        $breadcrumbs = [
            ["label" => "الرئيسية", "link" => route("admin.dashboard")],
            ["label" => "الجلسات", "link" => ""],
        ];
        PageTitle($title, $breadcrumbs);
    @endphp
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <button class="btn btn-primary btn-sm" onclick="refreshActiveSessions()" id="refreshBtn">
            <i class="bi bi-arrow-clockwise"></i> تحديث
        </button>
    </div>
</div>
@endsection

@section("content")
<div id="kt_app_content_container" class="app-container container-xxl">

    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session("success") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session("error") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card stat-card shadow-sm border-start border-4 border-success h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-wifi"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-success" id="statOnline">{{ $totalOnline }}</div>
                        <div class="text-muted small">متصل الآن</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card shadow-sm border-start border-4 border-secondary h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-secondary" id="statDisconnected">{{ $totalDisconnected }}</div>
                        <div class="text-muted small">منتهية (7 أيام)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-primary traffic-value" id="statDown">{{ formatBytes($totalDown) }}</div>
                        <div class="text-muted small">إجمالي التحميل</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-warning traffic-value" id="statUp">{{ formatBytes($totalUp) }}</div>
                        <div class="text-muted small">إجمالي الرفع</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sessions Card --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="session-tabs d-flex">
                <button class="session-tab active" data-tab="active" onclick="switchTab("active")">
                    <i class="bi bi-wifi me-1"></i> متصلون الآن
                    <span class="badge bg-success ms-1" id="tabOnlineBadge">{{ $totalOnline }}</span>
                </button>
                <button class="session-tab" data-tab="disconnected" onclick="switchTab("disconnected")">
                    <i class="bi bi-clock-history me-1"></i> جلسات منتهية
                    <span class="badge bg-secondary ms-1" id="tabDisconnectedBadge">{{ $totalDisconnected }}</span>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            {{-- Active Tab --}}
            <div class="tab-pane p-3" id="tab-active">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        يتم التحديث تلقائياً كل 30 ثانية
                    </small>
                </div>
                <div class="table-responsive" id="sessionsTableContainer">
                    @include("dashbord.sessions.partials.table", ["sessions" => $sessions, "nasList" => $nasList])
                </div>
            </div>

            {{-- Disconnected Tab --}}
            <div class="tab-pane d-none p-3" id="tab-disconnected">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        آخر 7 أيام من الجلسات المنتهية
                    </small>
                </div>
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
    document.querySelectorAll(".session-tab").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.tab === tab);
    });
    document.querySelectorAll(".tab-pane").forEach(pane => {
        pane.classList.add("d-none");
    });
    document.getElementById("tab-" + tab).classList.remove("d-none");

    if (tab === "active") {
        refreshActiveSessions();
    } else {
        refreshDisconnectedSessions();
    }
}

function refreshActiveSessions() {
    const btn = document.getElementById("refreshBtn");
    btn.disabled = true;
    btn.innerHTML = "<span class=\"spinner-border spinner-border-sm me-1\"></span>";

    fetch("{{ route("admin.sessions.refresh") }}?tab=active")
        .then(r => r.json())
        .then(data => {
            document.getElementById("sessionsTableContainer").innerHTML = data.html;
            document.getElementById("statOnline").textContent = data.total;
            document.getElementById("tabOnlineBadge").textContent = data.total;
        })
        .catch(e => console.error("Refresh error:", e))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = "<i class=\"bi bi-arrow-clockwise\"></i> تحديث";
        });
}

function refreshDisconnectedSessions() {
    fetch("{{ route("admin.sessions.refresh") }}?tab=disconnected")
        .then(r => r.json())
        .then(data => {
            document.getElementById("disconnectedSessionsTableContainer").innerHTML = data.html;
            document.getElementById("statDisconnected").textContent = data.total;
            document.getElementById("tabDisconnectedBadge").textContent = data.total;
        })
        .catch(e => console.error("Refresh error:", e));
}

refreshInterval = setInterval(() => {
    if (currentTab === "active") {
        refreshActiveSessions();
    }
}, 30000);

document.addEventListener("turbolinks:before-visit", function () {
    clearInterval(refreshInterval);
});
</script>
@endsection
