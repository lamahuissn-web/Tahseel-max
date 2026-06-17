@extends('dashbord.layouts.master')

@section('css')
<style>
    :root {
        --profile-border: #e2e8f0;
        --profile-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .profile-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: none;
        border-radius: 16px;
        margin-bottom: 28px;
        box-shadow: 0 4px 16px rgba(15,23,42,0.15);
    }
    .profile-header .card-title {
        color: #f1f5f9;
        font-size: 1.3rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .profile-header .card-title i {
        color: #3b82f6;
        font-size: 1.6rem;
    }
    .btn-add {
        background: #3b82f6;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 700;
        color: #fff;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    .btn-add:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        color: #fff;
    }

    .profile-stat-card {
        border: 1px solid var(--profile-border);
        border-radius: 14px;
        background: #fff;
        transition: all 0.2s ease-in-out;
        box-shadow: var(--profile-shadow);
    }
    .profile-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.02) !important;
    }
    .profile-stat-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748b;
        display: block;
    }
    .profile-stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .profile-stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-table-container {
        background: #fff;
        border: 1px solid var(--profile-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--profile-shadow);
    }
    .profile-table thead {
        background: #f8fafc;
    }
    .profile-table thead th {
        padding: 14px 16px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }
    .profile-table tbody tr {
        transition: background 0.15s ease;
        border-bottom: 1px solid #f1f5f9;
    }
    .profile-table tbody tr:last-child {
        border-bottom: none;
    }
    .profile-table tbody tr:hover {
        background: #f8fafc;
    }
    .profile-table tbody td {
        padding: 16px 16px;
        font-size: 0.9rem;
        color: #1e293b;
        vertical-align: middle;
    }

    .profile-name-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f1f5f9;
        color: #334155;
        padding: 6px 14px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        border: 1px solid #e2e8f0;
    }
    .profile-name-badge i {
        color: #64748b;
    }

    .speed-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fff0f6;
        color: #d946ef;
        border: 1px solid #fdf2f8;
        padding: 5px 14px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
    }
    .speed-badge i {
        font-size: 0.9rem;
    }

    .sim-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .sim-badge.single {
        background: #f0f9ff;
        color: #0284c7;
        border: 1px solid #e0f2fe;
    }
    .sim-badge.multi {
        background: #faf5ff;
        color: #9333ea;
        border: 1px solid #f3e8ff;
    }

    .sub-link-card {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f0fdf4;
        border: 1px solid #dcfce7;
        padding: 4px 10px;
        border-radius: 8px;
    }
    .sub-link-card .sub-name {
        font-weight: 700;
        color: #166534;
        font-size: 0.85rem;
    }
    .sub-link-card .sub-price {
        background: #166534;
        color: #fff;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        direction: ltr;
        display: inline-block;
    }
    .sub-unlinked-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fef3c7;
        padding: 5px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .user-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
    }
    .user-count-badge.active {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #dcfce7;
    }
    .user-count-badge.empty {
        background: #f8fafc;
        color: #94a3b8;
        border: 1px solid #e2e8f0;
    }

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
        font-size: 0.9rem;
    }
    .btn-action-edit {
        background: #fef3c7;
        color: #d97706;
    }
    .btn-action-edit:hover {
        background: #fde68a;
        color: #b45309;
        transform: translateY(-1px);
    }
    .btn-action-delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .btn-action-delete:hover {
        background: #fca5a5;
        color: #b91c1c;
        transform: translateY(-1px);
    }
</style>
@endsection

@section('content')
<div class="app-container container-xxl">

    {{-- Header --}}
    <div class="card profile-header shadow-sm">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="card-title mb-0">
                <i class="bi bi-speedometer2"></i>
                <div>
                    <span class="d-block">باقات السرعة (RADIUS Profiles)</span>
                    <small class="text-muted d-block mt-1 fs-7 text-light opacity-50" style="font-weight:500;">
                        إدارة خطط سرعات الإنترنت — Profile = Pool Name في الميكروتك
                    </small>
                </div>
            </div>
            <a href="{{ route('admin.profiles.create') }}" class="btn-add">
                <i class="bi bi-plus-lg"></i> إضافة باقة جديدة
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card profile-stat-card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="profile-stat-label">إجمالي باقات RADIUS</span>
                        <h3 class="profile-stat-value mb-0 mt-1">{{ $totalProfiles }}</h3>
                    </div>
                    <div class="profile-stat-icon-wrapper bg-primary bg-opacity-10">
                        <i class="bi bi-layers text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card profile-stat-card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="profile-stat-label">إجمالي المشتركين</span>
                        <h3 class="profile-stat-value mb-0 mt-1">{{ $totalUsers }}</h3>
                    </div>
                    <div class="profile-stat-icon-wrapper bg-success bg-opacity-10">
                        <i class="bi bi-people text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card profile-stat-card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="profile-stat-label">باقات مرتبطة بالخطط</span>
                        <h3 class="profile-stat-value mb-0 mt-1">{{ $totalLinkedSubs }}</h3>
                    </div>
                    <div class="profile-stat-icon-wrapper bg-warning bg-opacity-10">
                        <i class="bi bi-link-45deg text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card profile-table-container shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table profile-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">اسم الباقة</th>
                            <th>السرعة</th>
                            <th>الأجهزة المتزامنة</th>
                            <th>الخطة المرتبطة</th>
                            <th>عدد المشتركين</th>
                            <th class="text-end pe-4" style="width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats as $stat)
                        <tr>
                            <td class="ps-4">
                                <span class="profile-name-badge">
                                    <i class="bi bi-folder-fill me-1"></i> {{ $stat->name }}
                                </span>
                            </td>
                            <td>
                                @if($stat->speed)
                                <span class="speed-badge">
                                    <i class="bi bi-lightning-charge-fill me-1"></i> {{ $stat->speed }}
                                </span>
                                @else
                                <span class="sub-unlinked-badge">
                                    <i class="bi bi-dash-circle me-1"></i> غير محددة
                                </span>
                                @endif
                            </td>
                            <td>
                                <span class="sim-badge {{ $stat->simultaneous_use > 1 ? 'multi' : 'single' }}">
                                    <i class="bi {{ $stat->simultaneous_use > 1 ? 'bi-pc-display' : 'bi-laptop' }} me-1"></i>
                                    {{ $stat->simultaneous_use }} {{ $stat->simultaneous_use > 1 ? 'أجهزة' : 'جهاز' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $linkedSub = $linkedSubs->get($stat->id);
                                @endphp
                                @if($linkedSub)
                                    <div class="sub-link-card">
                                        <span class="sub-name">
                                            <i class="bi bi-journal-check me-1"></i>{{ $linkedSub->name }}
                                        </span>
                                        <span class="sub-price">${{ number_format($linkedSub->price, 2) }}</span>
                                    </div>
                                @else
                                    <span class="sub-unlinked-badge">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> غير مرتبطة
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="user-count-badge {{ $stat->clients_count > 0 ? 'active' : 'empty' }}">
                                    <i class="bi bi-people-fill me-1"></i> {{ $stat->clients_count ?: '—' }}
                                </span>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.profiles.edit', $stat->id) }}" class="btn-action btn-action-edit" title="تعديل الباقة">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form action="{{ route('admin.profiles.destroy', $stat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف الباقة {{ $stat->name }}؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-delete" title="حذف الباقة">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-folder-x fs-1 text-muted opacity-50 mb-3 d-block"></i>
                                    <h5 class="fw-bold text-gray-700">لا توجد باقات سرعة مضافة بعد</h5>
                                    <p class="text-muted small">قم بإضافة أول باقة سرعة لربطها بمشتركي الـ RADIUS</p>
                                    <a href="{{ route('admin.profiles.create') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-plus-lg me-1"></i> إضافة باقة الآن
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
