@extends('dashbord.layouts.master')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold"><i class="bi bi-speedometer2 text-primary"></i> باقات السرعة (RADIUS Profiles)</h6>
        <a href="{{ route('admin.profiles.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> إضافة باقة</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>اسم الباقة</th>
                    <th>السرعة</th>
                    <th>تزامن (Sim-Use)</th>
                    <th>الخطة المرتبطة</th>
                    <th>المستخدمين</th>
                    <th></th>
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
                    <td class="fw-bold">{{ $profile }}</td>
                    <td>{{ $speed }}</td>
                    <td>{{ $sim }}</td>
                    <td>{{ $sub ? $sub->name . ' ($' . $sub->price . ')' : '<span class="text-muted">—</span>' }}</td>
                    <td>{{ $userCount }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.profiles.edit', $profile) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
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
