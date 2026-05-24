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
            <base href="../../"/>
            {{-- <title>{{(!empty($mainData->name)) ? $mainData->name : 'Rashaketik'}}</title> --}}
            <title>Tahsel Dish</title>
            <meta charset="utf-8"/>
            <meta name="description"
                  content="The most advanced Bootstrap Admin Theme on Bootstrap Market trusted by over 4,000 beginners and professionals. Multi-demo, Dark Mode, RTL support. Grab your copy now and get life-time updates for free."/>
            <meta name="keywords"
                  content="keen, bootstrap, bootstrap 5, bootstrap 4, admin themes, web design, figma, web development, free templates, free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button, bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon"/>
            <meta name="viewport" content="width=device-width, initial-scale=1"/>
            <meta property="og:locale" content="en_US"/>
            <meta property="og:type" content="article"/>
            <meta property="og:title" content="Keen - Multi-demo Bootstrap 5 HTML Admin Dashboard Theme"/>
            <meta property="og:url" content="https://keenthemes.com/keen"/>
            <meta property="og:site_name" content="Keenthemes | Keen"/>
            <meta name="csrf-token" content="{{ csrf_token() }}">
            @include('dashbord.layouts.head')

        </head>
        <!--end::Head-->
        <!--begin::Body-->
     {{--   <body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
              data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
              data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
              data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default"
              data-kt-app-sidebar-minimize="on"
              data-kt-app-page-loading-enabled="true" data-kt-app-page-loading="on"
        >--}}


        <body  id="kt_app_body" data-kt-app-page-loading-enabled="true" data-kt-app-page-loading="on" data-kt-app-layout="dark-sidebar"
               data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-sidebar-enabled="true"
               data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-minimize="off" data-kt-app-sidebar-hoverable="true"
               data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true"  class="app-default" >

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
        <script>var defaultThemeMode = "light";
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
            }</script>
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
                    <!--begin::Sidebar-->
                @include('dashbord.layouts.main-sidebar')

                <!--end::Sidebar-->
                    <!--begin::Main-->
                    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                        <!--begin::Content wrapper-->
                        <div class="d-flex flex-column flex-column-fluid">


                                <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                                    @yield('toolbar')
                                </div>
                                <div id="kt_app_content" class="app-content flex-column-fluid">

                                    @yield('content')
                                </div>
                        </div>
                        <!--end::Content wrapper-->
                        <!--begin::Footer-->
{{--                    @include('dashbord.layouts.footer')--}}

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
        </body>
        <!--end::Body-->
        </html>
