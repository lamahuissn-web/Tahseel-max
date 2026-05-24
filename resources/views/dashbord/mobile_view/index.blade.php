@extends('dashbord.layouts.mobile_master')

@section('toolbar')
@endsection

@section('content')
<div class="row g-4 mb-5 justify-content-center px-2">
    
    <!-- Header Section - Glassmorphism Profile Card -->
    <div class="col-12 mb-3">
        <div class="profile-card position-relative overflow-hidden">
            <!-- Background Gradient -->
            <div class="bg-gradient-primary position-absolute top-0 start-0 w-100 h-100"></div>
            
            <!-- Decorative Circles -->
            <div class="deco-circle deco-circle-1"></div>
            <div class="deco-circle deco-circle-2"></div>
            
            <div class="card-body position-relative d-flex justify-content-between align-items-center text-white py-4 px-4">
                <div class="d-flex flex-column z-index-1">
                    <span class="fs-6 fw-medium opacity-90 mb-1">مرحباً بك</span>
                    <span class="fs-2 fw-bold mb-2">{{ auth()->guard('admin')->user()->name }}</span>
                    @php
                    $balance = auth()->guard('admin')->user()->account ? auth()->guard('admin')->user()->account->totalAmount() : 0;
                    @endphp
                    <div class="balance-badge d-inline-flex align-items-center gap-2 mt-1">
                        <i class="bi bi-wallet2 fs-6"></i>
                        <span class="fs-6 fw-semibold dir-ltr">{{ number_format($balance, 2) }} {{ get_app_config_data('currency') }}</span>
                    </div>
                </div>
                <div class="profile-image-wrapper">
                    <div class="symbol symbol-60px symbol-circle border-3 border-white shadow-sm">
                        <img src="{{ auth()->guard('admin')->user()->image }}" alt="profile" class="object-fit-cover" />
                    </div>
                    <div class="online-indicator"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-3">
        <a href="{{ route('admin.logout') }}" class="btn btn-light-danger w-100 d-flex align-items-center justify-content-center gap-2 py-2">
            <i class="bi bi-box-arrow-right fs-5"></i>
            <span class="fw-bold">تسجيل خروج</span>
        </a>
    </div>

    @if(auth()->guard('admin')->user()->hasRole(['Super-Admin', 'admin']))
    <!-- Stats Button - Featured Card -->
    <div class="col-12 mb-2">
        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
            <div class="feature-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-wrapper bg-primary-light">
                            <i class="bi bi-bar-chart-line-fill fs-2 text-primary"></i>
                        </div>
                        <div>
                            <span class="fs-5 fw-bold text-gray-800 d-block">الإحصائيات</span>
                            <span class="fs-7 text-gray-500">لوحة التحكم الرئيسية</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-left fs-4 text-gray-400"></i>
                </div>
            </div>
        </a>
    </div>
    @endif

    <!-- Grid Menu -->
    <div class="col-12">
        <div class="row g-3">
            
            @if(auth()->guard('admin')->user()->hasRole(['Super-Admin', 'accountant']))
            <!-- المحصلين -->
            <div class="col-6">
                <a href="{{ route('admin.users.index', ['mobile' => 'collectors']) }}" class="text-decoration-none">
                    <div class="menu-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="icon-wrapper bg-warning-light mb-3">
                                <i class="bi bi-person-badge-fill fs-2 text-warning"></i>
                            </div>
                            <span class="fs-6 fw-bold text-gray-800">المحصلين</span>
                            <span class="fs-8 text-gray-500 mt-1">فريق العمل</span>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            <!-- المشتركين -->
            <div class="col-6">
                <a href="{{ route('admin.mobile_clients') }}" class="text-decoration-none">
                    <div class="menu-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="icon-wrapper bg-info-light mb-3">
                                <i class="bi bi-people-fill fs-2 text-info"></i>
                            </div>
                            <span class="fs-6 fw-bold text-gray-800">المشتركين</span>
                            <span class="fs-8 text-gray-500 mt-1">قاعدة العملاء</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- مراجعة الفواتير -->
            <div class="col-6">
                <a href="{{ route('admin.mobile_invoices', ['review' => 'mine']) }}" class="text-decoration-none">
                    <div class="menu-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="icon-wrapper bg-primary-light mb-3">
                                <i class="bi bi-file-earmark-check-fill fs-2 text-primary"></i>
                            </div>
                            <span class="fs-6 fw-bold text-gray-800">مراجعة الفواتير</span>
                            <span class="fs-8 text-gray-500 mt-1">الفواتير المعلقة</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- الفواتير -->
            <div class="col-6">
                <a href="{{ route('admin.mobile_invoices') }}" class="text-decoration-none">
                    <div class="menu-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="icon-wrapper bg-success-light mb-3">
                                <i class="bi bi-receipt-cutoff fs-2 text-success"></i>
                            </div>
                            <span class="fs-6 fw-bold text-gray-800">الفواتير</span>
                            <span class="fs-8 text-gray-500 mt-1">جميع الفواتير</span>
                        </div>
                    </div>
                </a>
            </div>

            @if(auth()->guard('admin')->user()->hasRole(['Super-Admin', 'admin']))
            <!-- الإشعارات -->
            <div class="col-6">
                <a href="{{ route('admin.new_clients_notifications') }}" class="text-decoration-none">
                    <div class="menu-card h-100 position-relative">
                        <div class="notification-badge">3</div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="icon-wrapper bg-danger-light mb-3">
                                <i class="bi bi-bell-fill fs-2 text-danger"></i>
                            </div>
                            <span class="fs-6 fw-bold text-gray-800">الإشعارات</span>
                            <span class="fs-8 text-gray-500 mt-1">تنبيهات جديدة</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- المصروفات -->
            <div class="col-6">
                <a href="{{ route('admin.masrofat.index') }}" class="text-decoration-none">
                    <div class="menu-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="icon-wrapper bg-dark-light mb-3">
                                <i class="bi bi-cash-coin fs-2 text-dark"></i>
                            </div>
                            <span class="fs-6 fw-bold text-gray-800">المصروفات</span>
                            <span class="fs-8 text-gray-500 mt-1">المصاريف والتكاليف</span>
                        </div>
                    </div>
                </a>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

@section('css')
<style>
    body {
        background: linear-gradient(180deg, #eef2ff 0%, #f8f9fa 60%, #ffffff 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-attachment: fixed;
    }

    .profile-card {
        border-radius: 28px;
        border: none;
        box-shadow: 0 18px 36px rgba(13, 110, 253, 0.20);
        background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
        position: relative;
        overflow: hidden;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0099ff 50%, #00d4ff 100%);
    }

    .deco-circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }

    .deco-circle-1 {
        width: 200px;
        height: 200px;
        top: -100px;
        right: -50px;
    }

    .deco-circle-2 {
        width: 150px;
        height: 150px;
        bottom: -50px;
        left: -30px;
        background: rgba(255, 255, 255, 0.05);
    }

    .z-index-1 {
        z-index: 1;
    }

    .balance-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .profile-image-wrapper {
        position: relative;
        z-index: 1;
    }

    .online-indicator {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 14px;
        height: 14px;
        background-color: #2ecc71;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .feature-card {
        background: rgba(255, 255, 255, 0.92);
        border-radius: 22px;
        border: none;
        backdrop-filter: blur(6px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .feature-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(13, 110, 253, 0.15);
    }

    .menu-card {
        background: rgba(255, 255, 255, 0.92);
        border-radius: 22px;
        border: 1px solid rgba(13, 110, 253, 0.06);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        backdrop-filter: blur(6px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .menu-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: transparent;
        transition: all 0.3s ease;
    }

    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 32px rgba(0, 0, 0, 0.12);
    }

    .menu-card:hover::before {
        background: linear-gradient(90deg, #0d6efd, #0099ff);
    }

    .icon-wrapper {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }
    .bg-dark-light { background-color: rgba(33, 37, 41, 0.1); }

    .menu-card:hover .icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    .notification-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: linear-gradient(135deg, #dc3545, #ff6b6b);
        color: white;
        font-size: 12px;
        font-weight: bold;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .fs-8 {
        font-size: 0.75rem !important;
    }

    .dir-ltr {
        direction: ltr;
        display: inline-block;
    }

    html {
        scroll-behavior: smooth;
    }

    @media (hover: none) {
        .menu-card:active {
            transform: scale(0.98);
        }
        .feature-card:active {
            transform: scale(0.98);
        }
    }

    .px-2 {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
</style>
@endsection
