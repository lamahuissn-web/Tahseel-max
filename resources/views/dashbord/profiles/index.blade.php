@extends('dashbord.layouts.master')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-speedometer2 text-primary"></i> باقات السرعة (RADIUS Profiles)
        </h6>
        <a href="{{ route('admin.profiles.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> إضافة باقة
        </a>
    </div>
</div>

@php
    $totalUsers = 0; $totalSubs = 0; $speeds = [];
    foreach($profiles as $p) {
        $totalUsers += DB::connection('radius')->table('radusergroup')->where('groupname', $p)->count();
        if(isset($subscriptions[$p])) $totalSubs++;
        $speeds[] = $groupSpeeds[$p]->value ?? '—';
    }
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-3">
                <i class="bi bi-layers text-primary fs-2 d-block mb-1"></i>
                <strong class="fs-4">{{ count($profiles) }}</strong>
                <small class="text-muted d-block">إجمالي الباقات</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-3">
                <i class="bi bi-people text-success fs-2 d-block mb-1"></i>
                <strong class="fs-4">{{ $totalUsers }}</strong>
                <small class="text-muted d-block">المستخدمين</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-3">
                <i class="bi bi-link-45deg text-warning fs-2 d-block mb-1"></i>
                <strong class="fs-4">{{ $totalSubs }}</strong>
                <small class="text-muted d-block">خطط مرتبطة</small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>اسم الباقة</th>
                    <th>السرعة</th>
                    <th>تزامن (Sim-Use)</th>
                    <th>الخطة المرتبطة</th>
                    <th>المستخدمين</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $profile)
                @php
                    $speed = $groupSpeeds[$profile]->value ?? '—';
                    $sim = DB::connection('radius')->table('radgroupcheck')
                        ->where('groupname', $profile)->where('attribute','Simultaneous-Use')->value('value') ?? 1;
                    $sub = $subscriptions[$profile] ?? null;
                    $userCount = DB::connection('radius')->table('radusergroup')
                        ->where('groupname', $profile)->count();
                @endphp
                <tr>
                    <td class="fw-bold">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 fs-6">{{ $profile }}</span>
                    </td>
                    <td><code>{{ $speed }}</code></td>
                    <td><span class="badge bg-light text-dark">{{ $sim }}</span></td>
                    <td>
                        @if($sub)
                            <span>{{ $sub->name }}</span>
                            <small class="text-muted">(\${{ number_format($sub->price, 2) }})</small>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>{{ $userCount }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.profiles.edit', $profile) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.profiles.destroy', $profile) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف {{ $profile }}؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">لا توجد باقات بعد. <a href="{{ route('admin.profiles.create') }}">أضف باقة</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection