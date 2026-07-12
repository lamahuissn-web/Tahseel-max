@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_control_center') }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_control_center');
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-xxxl">

    {{-- Emergency Banner --}}
    @if($emergencyStop == '1')
    <div class="alert alert-danger d-flex align-items-center p-5 mb-8">
        <i class="bi bi-exclamation-triangle-fill fs-2x me-4 text-white"></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-white">🚨 {{ trans('clients.whatsapp_emergency_active') ?? 'حالة طوارئ مفعلة' }}</h4>
            <span class="text-white opacity-75">{{ trans('clients.whatsapp_emergency_active_desc') ?? 'خدمة الواتساب موقوفة بواسطة زر الطوارئ' }}</span>
        </div>
        <div class="ms-auto">
            <form action="{{ route('admin.settings.whatsapp.emergency_restart') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-light-success">
                    <i class="bi bi-play-fill"></i> {{ trans('clients.whatsapp_restart_service') ?? 'إعادة تشغيل الخدمة' }}
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row g-5 g-xl-8 mb-8">

        {{-- Connection Status Card --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch mb-xl-3">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-{{ $emergencyStop == '1' ? 'danger' : ($connectionStatus ? 'success' : 'warning') }}-light">
                            <i class="bi bi-whatsapp fs-2x text-{{ $emergencyStop == '1' ? 'danger' : ($connectionStatus ? 'success' : 'warning') }}"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column flex-grow-1 min-w-0">
                        <h6 class="fw-bold text-gray-800 mb-1">{{ trans('clients.whatsapp_connection') ?? 'حالة الاتصال' }}</h6>
                        @if($emergencyStop == '1')
                            <span class="badge badge-danger fs-7">{{ trans('clients.whatsapp_disconnected') ?? 'موقوف طوارئ' }}</span>
                        @elseif($connectionStatus)
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge badge-success fs-7">{{ trans('clients.whatsapp_connected') ?? 'متصل' }}</span>
                                @if($devicePhone)
                                    <span class="text-gray-700 fs-7 fw-semibold lh-1">{{ $devicePhone }}</span>
                                @endif
                            </div>
                        @else
                            <span class="badge badge-warning fs-7">{{ trans('clients.whatsapp_disconnected') ?? 'غير متصل' }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Messages Today --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch mb-xl-3">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-primary-light">
                            <i class="bi bi-send fs-2x text-primary"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column flex-grow-1">
                        <h6 class="fw-bold text-gray-800 mb-1">{{ trans('clients.whatsapp_today') ?? 'رسائل اليوم' }}</h6>
                        <div class="d-flex align-items-center">
                            <span class="fs-2x fw-bold text-gray-800 me-2">{{ $messagesToday }}</span>
                            @if($failuresToday > 0)
                                <span class="badge badge-danger">{{ $failuresToday }} {{ trans('clients.whatsapp_failed') ?? 'فشل' }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- This Month --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch mb-xl-3">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-info-light">
                            <i class="bi bi-calendar-check fs-2x text-info"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column flex-grow-1">
                        <h6 class="fw-bold text-gray-800 mb-1">{{ trans('clients.whatsapp_this_month') ?? 'هذا الشهر' }}</h6>
                        <span class="fs-2x fw-bold text-gray-800">{{ $messagesThisMonth }}</span>
                        <span class="text-muted fs-7">{{ trans('clients.whatsapp_total_messages') ?? 'إجمالي الرسائل' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Client Reachability --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch mb-xl-3">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-warning-light">
                            <i class="bi bi-people fs-2x text-warning"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column flex-grow-1">
                        <h6 class="fw-bold text-gray-800 mb-1">{{ trans('clients.whatsapp_reach') ?? 'الوصول للزبائن' }}</h6>
                        <div class="d-flex align-items-center">
                            <span class="fs-2x fw-bold text-gray-800 me-2">{{ $clientsWithPhone }}</span>
                            <span class="text-muted fs-7">/ {{ $totalClients }}</span>
                        </div>
                        <div class="progress h-5px mt-2 w-100">
                            @php $pct = $totalClients > 0 ? round(($clientsWithPhone / $totalClients) * 100) : 0; @endphp
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions + Last Activity Row --}}
    <div class="row g-5 g-xl-8">

        {{-- Quick Actions --}}
        <div class="col-xl-6">
            <div class="card card-xl-stretch mb-xl-3">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('clients.whatsapp_quick_actions') ?? 'إجراءات سريعة' }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-4">
                        <a href="{{ route('admin.whatsapp.send') }}" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i> {{ trans('clients.whatsapp_send_message') ?? 'إرسال رسالة' }}
                        </a>
                        <a href="{{ route('admin.whatsapp.templates') }}" class="btn btn-light-primary">
                            <i class="bi bi-pencil-square me-2"></i> {{ trans('clients.whatsapp_templates') ?? 'القوالب' }}
                        </a>
                        <a href="{{ route('admin.whatsapp.log') }}" class="btn btn-light-info">
                            <i class="bi bi-clock-history me-2"></i> {{ trans('clients.whatsapp_log') ?? 'سجل الرسائل' }}
                        </a>
                        <form action="{{ route('admin.settings.whatsapp.emergency_stop') }}" method="POST" style="display:inline"
                              onsubmit="return confirm('{{ trans('clients.whatsapp_emergency_confirm') ?? 'هل أنت متأكد من إيقاف خدمة الواتساب؟' }}')">
                            @csrf
                            <button type="submit" class="btn btn-danger" {{ $emergencyStop == '1' ? 'disabled' : '' }}>
                                <i class="bi bi-stop-circle me-2"></i> 🛑 {{ trans('clients.whatsapp_emergency_stop') ?? 'إيقاف الطوارئ' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.settings.whatsapp.emergency_restart') }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-light-success">
                                <i class="bi bi-arrow-clockwise me-2"></i> {{ trans('clients.whatsapp_restart_service') ?? 'إعادة تشغيل الخدمة' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Last Activity --}}
        <div class="col-xl-6">
            <div class="card card-xl-stretch mb-xl-3">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('clients.whatsapp_last_activity') ?? 'آخر نشاط' }}</h3>
                </div>
                <div class="card-body">
                    @if($lastSent)
                    <div class="d-flex align-items-center mb-4">
                        <div class="symbol symbol-40px me-3">
                            <span class="symbol-label bg-success-light">
                                <i class="bi bi-check-circle text-success fs-2"></i>
                            </span>
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-bold text-gray-800">{{ $lastSent->client_name ?? trans('clients.whatsapp_unknown') ?? 'غير معروف' }}</span>
                            <span class="text-muted fs-7">{{ $lastSent->phone }}</span>
                        </div>
                        <span class="text-muted fs-7">{{ $lastSent->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="separator separator-dashed my-3"></div>
                    <div class="text-center">
                        <a href="{{ route('admin.whatsapp.log') }}" class="btn btn-sm btn-light">
                            {{ trans('clients.whatsapp_view_all') ?? 'عرض الكل' }} <i class="bi bi-arrow-left ms-2"></i>
                        </a>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="bi bi-inbox fs-3x text-muted mb-3 d-block"></i>
                        <span class="text-muted">{{ trans('clients.whatsapp_no_activity') ?? 'لا يوجد نشاط حتى الآن' }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Automation Status Summary --}}
    <div class="row g-5 g-xl-8 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('clients.whatsapp_automation_status') ?? 'حالة التشغيل الآلي' }}</h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.whatsapp.automation') }}" class="btn btn-sm btn-light">
                            {{ trans('clients.whatsapp_manage') ?? 'إدارة' }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-6">
                        <div class="d-flex align-items-center">
                            <span class="badge {{ $connectionStatus ? 'badge-success' : 'badge-secondary' }} me-2 fs-7">
                                {{ $connectionStatus ? '🟢' : '⚪' }}
                            </span>
                            <span>{{ trans('clients.whatsapp_auto_reminders') ?? 'التذكيرات التلقائية' }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-success me-2 fs-7">🟢</span>
                            <span>{{ trans('clients.whatsapp_auto_receipts') ?? 'إيصالات الدفع التلقائية' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
