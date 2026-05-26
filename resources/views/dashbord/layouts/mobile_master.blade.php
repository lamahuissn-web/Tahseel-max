<!DOCTYPE html>
{{-- @php
    $mainData=getMainData();
@endphp --}}
@if(app()->getLocale() =='ar')
<html direction="rtl" dir="rtl" style="direction: rtl">

@else
<html lang="en">

@endif
<!--begin::Head-->

<head>
    <base href="../../" />
    {{-- <title>{{(!empty($mainData->name)) ? $mainData->name : 'Rashaketik'}}</title> --}}
    <title>Tahsel Dish</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="The most advanced Bootstrap Admin Theme on Bootstrap Market trusted by over 4,000 beginners and professionals. Multi-demo, Dark Mode, RTL support. Grab your copy now and get life-time updates for free." />
    <meta name="keywords"
        content="keen, bootstrap, bootstrap 5, bootstrap 4, admin themes, web design, figma, web development, free templates, free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button, bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Keen - Multi-demo Bootstrap 5 HTML Admin Dashboard Theme" />
    <meta property="og:url" content="https://keenthemes.com/keen" />
    <meta property="og:site_name" content="Keenthemes | Keen" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('dashbord.layouts.head')

</head>
<!--end::Head-->
<!--begin::Body-->
{{-- <body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
              data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
              data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
              data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default"
              data-kt-app-sidebar-minimize="on"
              data-kt-app-page-loading-enabled="true" data-kt-app-page-loading="on"
        >--}}


<body id="kt_app_body" data-kt-app-page-loading-enabled="true" data-kt-app-page-loading="on" data-kt-app-layout="dark-sidebar"
    data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-sidebar-enabled="false"
    data-kt-app-sidebar-fixed="false" data-kt-app-sidebar-minimize="off" data-kt-app-sidebar-hoverable="false"
    data-kt-app-sidebar-push-header="false" data-kt-app-sidebar-push-toolbar="false" data-kt-app-sidebar-push-footer="false" class="app-default">

    <!--begin::loader-->
    <!--  <div class="page-loader flex-column">
            <img alt="Logo" class="theme-light-show max-h-50px" src="{{asset((!empty($mainData->image)) ? $mainData->image : 'assets/media/logos/keenthemes.svg')}}"/>
            <img alt="Logo" class="theme-dark-show max-h-50px" src="{{asset((!empty($mainData->image)) ? $mainData->image : 'assets/media/logos/keenthemes-dark.svg')}}"/>
            <div class="d-flex align-items-center mt-5">
                <span class="spinner-border text-primary" role="status"></span>
                <span class="text-muted fs-6 fw-semibold ms-5">Loading...</span>
            </div>
        </div> -->
    <!--end::Loader-->
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <!--begin::Header-->
            @include('dashbord.layouts.main-headerbar')

            <!--end::Header-->
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">


                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            @yield('toolbar')
                        </div>
                        <div id="kt_app_content" class="app-content flex-column-fluid px-3 px-sm-4 px-md-5">

                            @yield('content')
                        </div>
                    </div>
                    <!--end::Content wrapper-->
                    <!--begin::Footer-->
                    {{-- @include('dashbord.layouts.footer')--}}

                    <!--end::Footer-->
                </div>
                <!--end:::Main-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::App-->

    <!--begin::Javascript-->

    @include('dashbord.layouts.footer-scripts')
    @include('notify::components.notify')
    <!--end::Javascript-->
    <div class="fixed-bottom mobile-bottom-wrapper" style="z-index: 9999; padding-bottom: env(safe-area-inset-bottom);">
        <div class="mobile-bottom-nav d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.mobile_view') }}" class="mobile-nav-item {{ request()->routeIs('admin.mobile_view') ? 'active' : '' }}">
                <div class="mobile-nav-icon-wrapper">
                    <i class="bi bi-house"></i>
                </div>
                <span class="mobile-nav-label">{{ trans('mobile.home') ?? 'الرئيسية' }}</span>
            </a>
            <a href="{{ route('admin.mobile_clients') }}" class="mobile-nav-item {{ request()->routeIs('admin.mobile_clients') || request()->routeIs('admin.mobile_client_details') ? 'active' : '' }}">
                <div class="mobile-nav-icon-wrapper">
                    <i class="bi bi-people"></i>
                </div>
                <span class="mobile-nav-label">{{ trans('mobile.clients') ?? 'العملاء' }}</span>
            </a>
            <a href="{{ route('admin.mobile_invoices') }}" class="mobile-nav-item {{ request()->routeIs('admin.mobile_invoices') ? 'active' : '' }}">
                <div class="mobile-nav-icon-wrapper">
                    <i class="bi bi-receipt"></i>
                </div>
                <span class="mobile-nav-label">{{ trans('mobile.invoices') ?? 'الفواتير' }}</span>
            </a>
            @if(auth()->guard('admin')->user()->hasRole('Super-Admin'))
            <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item">
                <div class="mobile-nav-icon-wrapper">
                    <i class="bi bi-grid"></i>
                </div>
                <span class="mobile-nav-label">{{ trans('mobile.admin_panel') ?? 'لوحة التحكم' }}</span>
            </a>
            @endif
        </div>
    </div>

    <style>
        body {
            padding-bottom: 90px !important;
        }

        .mobile-bottom-wrapper {
            padding-bottom: env(safe-area-inset-bottom);
        }

        .mobile-bottom-nav {
            width: 100%;
            border-radius: 0;
            padding: 6px 8px;
            background:  #0ea5e9 ;
          
        }

        .mobile-nav-item {
            flex: 1;
            text-align: center;
            text-decoration: none;
            color: rgba(248, 250, 252, 0.7);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .mobile-nav-icon-wrapper {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.7);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.6);
            transition: all 0.22s ease;
        }

        .mobile-nav-item i {
            font-size: 1.35rem;
            color: rgba(226, 232, 240, 0.9);
        }

        .mobile-nav-item.active .mobile-nav-icon-wrapper {
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            box-shadow: 0 6px 14px rgba(56, 189, 248, 0.7);
            transform: translateY(-3px) scale(1.05);
        }

        .mobile-nav-item.active i {
            color: #ffffff;
        }

        .mobile-nav-label {
            font-size: 0.7rem;
            font-weight: 700;
            line-height: 1.1;
        }
    </style>
</body>
<!--end::Body-->

</html>
