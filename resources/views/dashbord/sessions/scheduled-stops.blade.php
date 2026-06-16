@extends('dashbord.layouts.master')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-calendar-stop text-danger"></i> جدولة إيقاف الإنترنت
        </h6>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary fs-6 px-3 py-2">المجموع: {{ $totalScheduled }}</span>
            <span class="badge bg-warning fs-6 px-3 py-2">قادم: {{ $upcoming }}</span>
            <span class="badge bg-danger fs-6 px-3 py-2">متأخر: {{ $overdue }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if(count($clients) > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الزبون</th>
                        <th>user</th>
                        <th>الهاتف</th>
                        <th>الباقة</th>
                        <th>تاريخ الإيقاف</th>
                        <th>باقي</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $today = now()->format('Y-m-d'); @endphp
                    @foreach($clients as $client)
                    @php
                        $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($client->radius_stop_at), false);
                        $isOverdue = $client->radius_stop_at <= $today;
                    @endphp
                    <tr class="{{ $isOverdue ? 'table-danger' : ($daysLeft <= 3 && $daysLeft >= 0 ? 'table-warning' : '') }}">
                        <td>{{ $client->id }}</td>
                        <td class="fw-bold">
                            <a href="{{ route('admin.clients.details', $client->id) }}">{{ $client->name }}</a>
                        </td>
                        <td><code>{{ $client->sas_username ?? '—' }}</code></td>
                        <td dir="ltr">{{ $client->phone ?? '—' }}</td>
                        <td>{{ $client->plan_name ?? '—' }}</td>
                        <td class="fw-bold">{{ $client->radius_stop_at }}</td>
                        <td>
                            @if($isOverdue)
                                <span class="badge bg-danger">متأخر {{ abs($daysLeft) }} يوم</span>
                            @elseif($daysLeft == 0)
                                <span class="badge bg-danger">اليوم</span>
                            @else
                                <span class="badge bg-warning">{{ $daysLeft }} أيام</span>
                            @endif
                        </td>
                        <td>
                            @if($client->is_active)
                                <span class="badge bg-success">🟢 نشط</span>
                            @else
                                <span class="badge bg-secondary">🔴 موقوف</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.clients.details', $client->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-check fs-1 text-success d-block mb-2"></i>
            <p>لا يوجد زبون لديه جدولة إيقاف حالياً</p>
            <p class="small">يمكنك جدولة إيقاف الإنترنت من صفحة تفاصيل الزبون → 🌐 الإنترنت → 📅 جدولة إيقاف</p>
        </div>
        @endif
    </div>
</div>
@endsection
