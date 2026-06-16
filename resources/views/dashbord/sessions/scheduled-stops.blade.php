@extends('dashbord.layouts.master')

@section('content')
<style>
    .s-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        padding: 1.5rem 2rem;
        border-radius: 12px;
        color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .s-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    .s-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        border-color: #cbd5e1;
    }
    .s-stat-info {
        display: flex;
        flex-direction: column;
        text-align: right;
    }
    .s-stat-label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .s-stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .s-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        transition: all 0.3s ease;
    }
    .s-table-wrap {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-top: 1.5rem;
    }
    .s-table-wrap table {
        margin-bottom: 0;
    }
    .s-table-wrap th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 1rem 1.25rem;
        border-bottom: 2px solid #e2e8f0;
    }
    .s-table-wrap td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .s-table-wrap tbody tr {
        transition: background-color 0.2s ease;
    }
    .s-row-overdue {
        background-color: rgba(239, 68, 68, 0.02) !important;
        border-right: 4px solid #ef4444 !important;
    }
    .s-row-overdue:hover {
        background-color: rgba(239, 68, 68, 0.05) !important;
    }
    .s-row-warning {
        background-color: rgba(245, 158, 11, 0.02) !important;
        border-right: 4px solid #f59e0b !important;
    }
    .s-row-warning:hover {
        background-color: rgba(245, 158, 11, 0.05) !important;
    }
    .s-row-normal {
        border-right: 4px solid transparent;
    }
    .s-row-normal:hover {
        background-color: #f8fafc !important;
    }
    .badge-soft-danger {
        background-color: #fef2f2;
        color: #ef4444;
        border: 1px solid #fca5a5;
        font-weight: 600;
    }
    .badge-soft-warning {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
        font-weight: 600;
    }
    .badge-soft-success {
        background-color: #f0fdf4;
        color: #16a34a;
        border: 1px solid #86efac;
        font-weight: 600;
    }
    .badge-soft-secondary {
        background-color: #f8fafc;
        color: #64748b;
        border: 1px solid #cbd5e1;
        font-weight: 600;
    }
    .status-dot {
        font-size: 0.55rem;
        vertical-align: middle;
    }
    .sas-username-badge {
        background-color: #f1f5f9;
        color: #475569;
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }
    .hover-primary {
        transition: color 0.15s ease;
    }
    .hover-primary:hover {
        color: #2563eb !important;
        text-decoration: underline !important;
    }
    .quick-action-btn {
        font-size: 0.75rem !important;
        padding: 0.3rem 0.6rem !important;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 1px solid #cbd5e1;
        color: #475569;
        background-color: #ffffff;
    }
    .quick-action-btn:hover {
        background-color: #10b981;
        border-color: #10b981;
        color: #ffffff;
    }
    .btn-action-view {
        color: #0f172a;
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .btn-action-view:hover {
        background-color: #e2e8f0;
        color: #0f172a;
    }
    .client-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .fa-sync-alt.bi-spin-hover:hover {
        animation: bi-spin 1.5s infinite linear;
    }
    @keyframes bi-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

@php
    $today = now()->format('Y-m-d');
    $activeClientsCount = 0;
    foreach($clients as $c) {
        if ($c->is_active) { $activeClientsCount++; }
    }
@endphp

<div class="container-fluid px-0">
    <div class="s-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-white d-flex align-items-center gap-2">
                <i class="bi bi-calendar-x text-danger"></i>
                <span>جدولة إيقاف الإنترنت</span>
            </h4>
            <p class="mb-0 text-white-50 small">
                إدارة ومتابعة جدولة الإيقاف التلقائي لاشتراكات الإنترنت الخاصة بالزبائن.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-2 rounded-3 small">
                <i class="bi bi-arrow-clockwise bi-spin-hover me-1"></i>
                آخر تحديث: {{ now()->format('Y-m-d H:i') }}
            </span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="s-stat-card">
                <div class="s-stat-info">
                    <span class="s-stat-label">إجمالي المجدول</span>
                    <span class="s-stat-value text-primary">{{ $totalScheduled }}</span>
                </div>
                <div class="s-stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-list-check"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="s-stat-card">
                <div class="s-stat-info">
                    <span class="s-stat-label">قادم قريباً</span>
                    <span class="s-stat-value text-warning">{{ $upcoming }}</span>
                </div>
                <div class="s-stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="s-stat-card">
                <div class="s-stat-info">
                    <span class="s-stat-label">متأخر (انتهى)</span>
                    <span class="s-stat-value text-danger">{{ $overdue }}</span>
                </div>
                <div class="s-stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="s-stat-card">
                <div class="s-stat-info">
                    <span class="s-stat-label">نشط حالياً</span>
                    <span class="s-stat-value text-success">{{ $activeClientsCount }}</span>
                </div>
                <div class="s-stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-wifi"></i>
                </div>
            </div>
        </div>
    </div>

    @if(count($clients) > 0)
    <div class="s-table-wrap">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">#</th>
                        <th>الزبون</th>
                        <th>اسم المستخدم (SAS)</th>
                        <th>الهاتف</th>
                        <th>الباقة المشترك بها</th>
                        <th>تاريخ الإيقاف</th>
                        <th>المتبقي</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center" style="width: 250px;">تمديد سريع</th>
                        <th class="text-end" style="width: 100px;">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    @php
                        $daysLeft = now()->diffInDays(Carbon\Carbon::parse($client->radius_stop_at), false);
                        $isOverdue = $client->radius_stop_at <= $today;
                        $rowClass = $isOverdue ? 's-row-overdue' : (($daysLeft <= 3 && $daysLeft >= 0) ? 's-row-warning' : 's-row-normal');
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td class="text-center text-muted fw-bold">{{ $client->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="client-avatar">
                                    <i class="bi bi-person small"></i>
                                </div>
                                <a href="{{ route('admin.clients.show', $client->id) }}" class="fw-bold text-decoration-none hover-primary">
                                    {{ $client->name }}
                                </a>
                            </div>
                        </td>
                        <td>
                            @if($client->sas_username)
                                <span class="sas-username-badge">{{ $client->sas_username }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td dir="ltr" class="text-start">
                            @if($client->phone)
                                <a href="tel:{{ $client->phone }}" class="text-decoration-none">
                                    <i class="bi bi-telephone text-muted me-1 small"></i>
                                    {{ $client->phone }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span>
                                <i class="bi bi-hdd-network text-muted me-1 small"></i>
                                {{ $client->plan_name ?? '—' }}
                            </span>
                        </td>
                        <td class="fw-bold">
                            <i class="bi bi-calendar3 text-muted me-1"></i>
                            {{ $client->radius_stop_at }}
                        </td>
                        <td>
                            @if($isOverdue)
                                <span class="badge badge-soft-danger rounded-pill px-2 py-1">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    متأخر {{ abs($daysLeft) }} يوم
                                </span>
                            @elseif($daysLeft == 0)
                                <span class="badge badge-soft-danger rounded-pill px-2 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    اليوم
                                </span>
                            @else
                                <span class="badge badge-soft-warning rounded-pill px-2 py-1">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $daysLeft }} أيام
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($client->is_active)
                                <span class="badge badge-soft-success rounded-pill px-2 py-1">
                                    <i class="bi bi-circle-fill status-dot me-1"></i> نشط
                                </span>
                            @else
                                <span class="badge badge-soft-secondary rounded-pill px-2 py-1">
                                    <i class="bi bi-circle-fill status-dot me-1"></i> موقوف
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-sm quick-action-btn" onclick="handleQuickExtend({{ $client->id }}, 3, '{{ addslashes($client->name) }}')" title="تمديد 3 أيام">
                                    <i class="bi bi-plus-lg me-1 text-success"></i> 3 أيام
                                </button>
                                <button type="button" class="btn btn-sm quick-action-btn" onclick="handleQuickExtend({{ $client->id }}, 7, '{{ addslashes($client->name) }}')" title="تمديد أسبوع">
                                    <i class="bi bi-plus-lg me-1 text-success"></i> أسبوع
                                </button>
                                <button type="button" class="btn btn-sm quick-action-btn" onclick="handleQuickExtend({{ $client->id }}, 14, '{{ addslashes($client->name) }}')" title="تمديد أسبوعين">
                                    <i class="bi bi-plus-lg me-1 text-success"></i> أسبوعين
                                </button>
                            </div>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-sm btn-action-view" title="عرض التفاصيل">
                                <i class="bi bi-eye me-1 text-primary"></i> عرض
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-5 px-4 bg-white border rounded-3 shadow-sm my-4">
        <div class="mb-3 text-success">
            <i class="bi bi-calendar-check fa-3x"></i>
        </div>
        <h5 class="fw-bold mb-2">لا يوجد زبائن لديهم جدولة إيقاف حالياً</h5>
        <p class="text-muted small mb-4 mx-auto" style="max-width: 480px;">
            جميع حسابات الزبائن تعمل بشكل اعتيادي ولا يوجد أي إيقاف مجدول في الوقت الحالي.
        </p>
        <div class="d-inline-flex align-items-center gap-2 bg-light px-3 py-2 rounded-pill small border">
            <i class="bi bi-info-circle text-info"></i>
            <span>يمكنك جدولة إيقاف الإنترنت من: <strong>صفحة تفاصيل الزبون ← 🌐 الإنترنت ← 📅 جدولة إيقاف</strong></span>
        </div>
    </div>
    @endif
</div>

<form id="quick-extend-form" action="" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="days" id="extend-days-input">
</form>

<script>
function handleQuickExtend(clientId, days, clientName) {
    let daysText = days === 3 ? '3 أيام' : (days === 7 ? 'أسبوع' : 'أسبوعين');
    if (confirm('هل أنت متأكد من تمديد إيقاف الإنترنت للزبون "' + clientName + '" بمقدار ' + daysText + '؟')) {
        let form = document.getElementById('quick-extend-form');
        form.action = '/ar/admin/scheduled-stops/' + clientId + '/extend';
        document.getElementById('extend-days-input').value = days;
        form.submit();
    }
}
</script>
@endsection