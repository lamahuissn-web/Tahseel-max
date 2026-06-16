@extends('dashbord.layouts.master')

@section('content')
<style>
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

    .profile-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .profile-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateY(-2px);
        border-color: #93c5fd;
    }
    .profile-card .card-header {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.25rem;
    }
    .profile-speed-badge {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
    }
    .profile-speed-unit {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 600;
    }
    .profile-stat {
        text-align: center;
        padding: 0.75rem 0.5rem;
    }
    .profile-stat-label {
        font-size: 0.7rem;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .profile-stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 2px;
    }
    .profile-sim-badge {
        background-color: #eef2ff;
        color: #4f46e5;
        font-weight: 700;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    .profile-plan-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
    }
    .profile-plan-link:hover {
        text-decoration: underline;
    }
</style>

<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="s-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 px-4 py-3">
        <div>
            <h4 class="mb-1 fw-bold text-white d-flex align-items-center gap-2">
                <i class="bi bi-speedometer2"></i>
                <span>باقات السرعة</span>
                <span class="badge bg-white bg-opacity-20 text-white fs-6 px-3 py-1 me-2">{{ count($profiles) }} باقات</span>
            </h4>
            <p class="mb-0 text-white-50 small">
                إدارة باقات السرعة المرتبطة باشتراكات الإنترنت
            </p>
        </div>
        <a href="{{ route('admin.profiles.create') }}" class="btn btn-light btn-sm fw-bold">
            <i class="bi bi-plus-lg me-1"></i> إضافة باقة
        </a>
    </div>

    {{-- Stats Row --}}
    @php
        $totalUsers = 0;
        $totalSubs = 0;
        foreach($profiles as $p) {
            $totalUsers += DB::connection('radius')->table('radusergroup')->where('groupname', $p)->count();
            if(isset($subscriptions[$p])) $totalSubs++;
        }
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                        <i class="bi bi-layers fs-2 text-primary"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">إجمالي الباقات</small>
                        <strong class="fs-4">{{ count($profiles) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3">
                        <i class="bi bi-people fs-2 text-success"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">المستخدمين</small>
                        <strong class="fs-4">{{ $totalUsers }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                        <i class="bi bi-link-45deg fs-2 text-warning"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">خطط مرتبطة</small>
                        <strong class="fs-4">{{ $totalSubs }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Profiles Grid --}}
    @if(count($profiles) > 0)
    <div class="row g-4">
        @foreach($profiles as $profile)
        @php
            $speed = $groupSpeeds[$profile]->value ?? '—';
            $sim = DB::connection('radius')->table('radgroupcheck')
                ->where('groupname', $profile)->where('attribute','Simultaneous-Use')->value('value') ?? 1;
            $sub = $subscriptions[$profile] ?? null;
            $userCount = DB::connection('radius')->table('radusergroup')
                ->where('groupname', $profile)->count();

            // Parse speed for display
            $speedDisplay = $speed;
            $speedParts = explode('/', $speed);
            $downloadSpeed = $speedParts[0] ?? $speed;
        @endphp
        <div class="col-lg-4 col-md-6">
            <div class="profile-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">{{ $profile }}</h5>
                    <span class="profile-sim-badge">
                        <i class="bi bi-devices me-1"></i> {{ $sim }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="profile-speed-badge">{{ $speedDisplay }}</div>
                        <div class="profile-speed-unit">تحميل / رفع</div>
                    </div>

                    <div class="row g-0 border-top border-bottom my-3">
                        <div class="col-4 profile-stat border-end">
                            <div class="profile-stat-label">تحميل</div>
                            <div class="profile-stat-value text-success">{{ $speedParts[0] ?? '—' }}</div>
                        </div>
                        <div class="col-4 profile-stat border-end">
                            <div class="profile-stat-label">رفع</div>
                            <div class="profile-stat-value text-primary">{{ $speedParts[1] ?? ($speedParts[0] ?? '—') }}</div>
                        </div>
                        <div class="col-4 profile-stat">
                            <div class="profile-stat-label">متزامن</div>
                            <div class="profile-stat-value text-warning">{{ $sim }}</div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted d-block">الخطة المرتبطة</small>
                        @if($sub)
                            <a href="#" class="profile-plan-link">{{ $sub->name }}</a>
                            <span class="badge bg-success bg-opacity-10 text-success ms-1">\${{ number_format($sub->price, 2) }}</span>
                        @else
                            <span class="text-muted small">— غير مرتبطة</span>
                        @endif
                    </div>
                    <div>
                        <small class="text-muted d-block">المستخدمين</small>
                        <strong>{{ $userCount }}</strong> <small class="text-muted">مستخدم</small>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0 d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.profiles.edit', $profile) }}" class="btn btn-sm btn-outline-primary px-3">
                        <i class="bi bi-pencil me-1"></i> تعديل
                    </a>
                    <form action="{{ route('admin.profiles.destroy', $profile) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف {{ $profile }}؟')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger px-3"><i class="bi bi-trash me-1"></i> حذف</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-5 px-4 bg-white border rounded-3 shadow-sm">
        <div class="mb-3 text-primary">
            <i class="bi bi-speedometer2 fs-1"></i>
        </div>
        <h5 class="fw-bold mb-2">لا توجد باقات سرعة</h5>
        <p class="text-muted small mb-3">لم يتم إضافة أي باقة سرعة بعد</p>
        <a href="{{ route('admin.profiles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> إضافة باقة
        </a>
    </div>
    @endif
</div>
@endsection