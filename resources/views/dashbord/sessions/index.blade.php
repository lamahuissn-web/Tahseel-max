@extends("dashbord.layouts.master")

@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity me-2"></i>
                        المتصلون الآن
                    </h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success fs-6 d-flex align-items-center" id="onlineCount">
                            <i class="bi bi-circle-fill me-1" style="font-size: 10px;"></i>
                            {{ $totalOnline }} متصل
                        </span>
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshSessions()">
                            <i class="bi bi-arrow-clockwise"></i> تحديث
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session("success"))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-1"></i> {{ session("success") }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session("error"))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i> {{ session("error") }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Stats Cards --}}
                    <div class="row mb-4">
                        <div class="col-md-4 col-6 mb-2">
                            <div class="card bg-success bg-opacity-10 border-success h-100">
                                <div class="card-body text-center py-3">
                                    <div class="fs-1 fw-bold text-success">{{ $totalOnline }}</div>
                                    <div class="text-muted small">متصل الآن</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="card bg-primary bg-opacity-10 border-primary h-100">
                                <div class="card-body text-center py-3">
                                    <div class="fs-1 fw-bold text-primary">{{ formatBytes($totalDown) }}</div>
                                    <div class="text-muted small">إجمالي التحميل</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="card bg-warning bg-opacity-10 border-warning h-100">
                                <div class="card-body text-center py-3">
                                    <div class="fs-1 fw-bold text-warning">{{ formatBytes($totalUp) }}</div>
                                    <div class="text-muted small">إجمالي الرفع</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sessions Table --}}
                    <div class="table-responsive" id="sessionsTableContainer">
                        @include("dashbord.sessions.partials.table", ["sessions" => $sessions, "nasList" => $nasList])
                    </div>

                    @if($sessions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-emoji-neutral display-1 text-muted"></i>
                            <p class="mt-3 text-muted">لا يوجد متصلين حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
function refreshSessions() {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري التحديث...';

    fetch("{{ route('admin.sessions.refresh') }}")
        .then(r => r.json())
        .then(data => {
            document.getElementById('sessionsTableContainer').innerHTML = data.html;
            document.getElementById('onlineCount').innerHTML = `
                <i class="bi bi-circle-fill me-1" style="font-size: 10px;"></i>
                ${data.total} متصل
            `;
        })
        .catch(e => console.error(e))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> تحديث';
        });
}

// Auto-refresh every 30 seconds
setInterval(refreshSessions, 30000);
</script>
@endsection
