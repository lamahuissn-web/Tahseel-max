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

@include('dashbord.whatsapp._partials.tab-nav')
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

    {{-- QR Code Section (shown when disconnected) --}}
    @if(!$connectionStatus && $emergencyStop != '1')
    <div class="row g-5 g-xl-8 mb-8" id="qr-section">
        <div class="col-12">
            <div class="card card-xl-stretch">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-qr-code-scan me-2 text-warning"></i>
                        📱 رمز QR لإعادة الاتصال
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" id="refresh-qr-btn" onclick="fetchQRCode()">
                            <i class="bi bi-arrow-clockwise me-1"></i> تحديث
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center" id="qr-container">
                        <div class="spinner-border text-primary mb-3" role="status" id="qr-loading">
                            <span class="visually-hidden">جاري تحميل رمز QR...</span>
                        </div>
                        <p class="text-muted" id="qr-loading-text">جاري تحميل رمز QR من OpenWA...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Connection Success Banner (hidden by default, shown after scan) --}}
    <div class="row g-5 g-xl-8 mb-8" id="connection-success" style="display:none">
        <div class="col-12">
            <div class="alert alert-success d-flex align-items-center p-5">
                <i class="bi bi-check-circle-fill fs-2x me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1">✅ تم الاتصال بنجاح!</h4>
                    <span>يمكنك الآن إرسال رسائل الواتساب</span>
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

<script>
    // QR Code polling configuration
    var qrPollInterval = null;
    var connectionPollInterval = null;
    var qrBaseUrl = '{{ route("admin.whatsapp.qr_code") }}';
    var checkConnectionUrl = '{{ route("admin.whatsapp.check_connection") }}';

    // Fetch QR code from OpenWA
    function fetchQRCode() {
        var $container = $('#qr-container');
        var $loading = $('#qr-loading');
        var $loadingText = $('#qr-loading-text');

        // Show loading
        $container.html(
            '<div class="spinner-border text-primary mb-3" role="status">' +
            '<span class="visually-hidden">جاري تحميل...</span>' +
            '</div>' +
            '<p class="text-muted">جاري تحميل رمز QR من OpenWA...</p>'
        );

        $.ajax({
            url: qrBaseUrl,
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (res.connected) {
                    // Already connected!
                    showConnected(res.phone);
                    return;
                }

                if (res.success && res.qr) {
                    // Show QR code
                    var qrHtml = '';
                    if (res.qr.startsWith('data:')) {
                        // Base64 data URL
                        qrHtml = '<img src="' + res.qr + '" alt="QR Code" style="max-width:280px; width:100%;" class="img-fluid rounded border p-2 mb-3">';
                    } else if (res.qr.startsWith('http')) {
                        // URL
                        qrHtml = '<img src="' + res.qr + '" alt="QR Code" style="max-width:280px; width:100%;" class="img-fluid rounded border p-2 mb-3">';
                    } else if (res.qr.length > 100) {
                        // Likely base64 without prefix
                        qrHtml = '<img src="data:image/png;base64,' + res.qr + '" alt="QR Code" style="max-width:280px; width:100%;" class="img-fluid rounded border p-2 mb-3">';
                    } else {
                        // Text QR code
                        qrHtml = '<pre style="font-size:12px; line-height:1.2; display:inline-block; text-align:left; direction:ltr;" class="border p-3 bg-white rounded">' + res.qr + '</pre>';
                    }

                    $container.html(
                        qrHtml +
                        '<p class="text-muted mt-3 mb-1"><i class="bi bi-info-circle me-1"></i> افتح واتساب على هاتفك وامسح الرمز</p>' +
                        '<p class="text-muted fs-7">الرمز صالح لمدة محدودة — امسحه قبل انتهاء الصلاحية</p>' +
                        '<p class="text-muted fs-7 mt-1">🔄 تحديث تلقائي بعد <span id="qr-countdown" class="fw-bold text-primary">18s</span></p>' +
                        '<button class="btn btn-sm btn-outline-primary mt-2" onclick="fetchQRCode()">' +
                        '<i class="bi bi-arrow-clockwise me-1"></i> رمز جديد</button>'
                    );

                    // Start polling for connection status
                    startConnectionPolling();
                } else {
                    // QR not available
                    $container.html(
                        '<i class="bi bi-exclamation-triangle fs-3x text-warning mb-3 d-block"></i>' +
                        '<p class="text-muted">' + (res.message || 'رمز QR غير متاح حالياً') + '</p>' +
                        '<button class="btn btn-sm btn-outline-primary mt-2" onclick="fetchQRCode()">' +
                        '<i class="bi bi-arrow-clockwise me-1"></i> إعادة المحاولة</button>'
                    );
                }
            },
            error: function() {
                $container.html(
                    '<i class="bi bi-exclamation-triangle fs-3x text-danger mb-3 d-block"></i>' +
                    '<p class="text-muted">فشل الاتصال بـ OpenWA</p>' +
                    '<button class="btn btn-sm btn-outline-primary mt-2" onclick="fetchQRCode()">' +
                    '<i class="bi bi-arrow-clockwise me-1"></i> إعادة المحاولة</button>'
                );
            }
        });
    }

    // Poll connection status after QR scan
    function startConnectionPolling() {
        if (connectionPollInterval) clearInterval(connectionPollInterval);

        connectionPollInterval = setInterval(function() {
            $.ajax({
                url: checkConnectionUrl,
                type: 'GET',
                timeout: 5000,
                success: function(res) {
                    if (res.connected) {
                        clearInterval(connectionPollInterval);
                        showConnected(res.phone);
                    }
                }
            });
        }, 3000); // Check every 3 seconds
    }

    // Show connected state
    function showConnected(phone) {
        if (connectionPollInterval) clearInterval(connectionPollInterval);

        $('#qr-section').slideUp(300, function() {
            $(this).remove();
        });
        $('#connection-success').slideDown(300);

        // Update the connection status card
        setTimeout(function() {
            location.reload();
        }, 2000);
    }

    // Start QR auto-refresh (every 18 seconds — QR expires in ~20s)
    function startQrAutoRefresh() {
        if (qrPollInterval) clearInterval(qrPollInterval);
        var secondsLeft = 18;

        // Show countdown
        function updateCountdown() {
            var $timer = $('#qr-countdown');
            if ($timer.length) {
                if (secondsLeft > 0) {
                    $timer.text(secondsLeft + 's');
                    secondsLeft--;
                } else {
                    $timer.text('...');
                }
            }
        }

        qrPollInterval = setInterval(function() {
            secondsLeft = 18;
            fetchQRCode();
        }, 18000);

        // Countdown display
        setInterval(updateCountdown, 1000);
    }

    // Auto-fetch QR on page load if disconnected
    $(document).ready(function() {
        if ($('#qr-section').length) {
            setTimeout(function() {
                fetchQRCode();
                startQrAutoRefresh();
            }, 500);
        }
    });
</script>
@endsection
