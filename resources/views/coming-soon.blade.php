<!DOCTYPE html>
<!--
Author: Keenthemes
Product Name: Keen
Product Version: 3.0.3
Purchase: https://themes.getbootstrap.com/product/keen-the-ultimate-bootstrap-admin-theme/
Website: http://www.keenthemes.com
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
License: For each use you must have a valid license purchased only from above link in order to legally use the theme for your project.
-->
<html lang="en">
	<!--begin::Head-->
    {{-- @php
        $mainData=getMainData();
    @endphp --}}
	<head>
        <base href="../../"/>
        {{-- <title>{{(!empty($mainData->name)) ? $mainData->name : 'freefare'}}</title> --}}
        <title>Coming Soon</title>
		<meta charset="utf-8" />
		<meta name="description" content="The most advanced Bootstrap Admin Theme on Bootstrap Market trusted by over 4,000 beginners and professionals. Multi-demo, Dark Mode, RTL support. Grab your copy now and get life-time updates for free." />
		<meta name="keywords" content="keen, bootstrap, bootstrap 5, bootstrap 4, admin themes, web design, figma, web development, free templates, free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button, bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content="Keen - Multi-demo Bootstrap 5 HTML Admin Dashboard Theme" />
		<meta property="og:url" content="https://keenthemes.com/keen" />
		<meta property="og:site_name" content="Keenthemes | Keen" />
		<link rel="canonical" href="https://preview.keenthemes.com/keen" />
{{--		<link rel="shortcut icon" href="{{asset('assets/media/logos/favicon.ico')}}" />--}}
        <link rel="shortcut icon" href="{{asset((!empty($mainData->image)) ? $mainData->image : 'assets/media/logos/freefare last logo-04.png')}}"/>

        <!--begin::Fonts(mandatory for all pages)-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
		<link href="{{asset('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="app-blank app-blank bgi-size-cover bgi-position-center bgi-no-repeat">
		<!--begin::Theme mode setup on page load-->
		<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
		<!--end::Theme mode setup on page load-->
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<!--begin::Page bg image-->
			<style>body { background-image: url('{{asset('assets/media/auth/bg12.jpg')}}'); } [data-bs-theme="dark"] body { background-image: url('{{asset('assets/media/auth/bg12-dark.jpg')}}'); }</style>
			<!--end::Page bg image-->
			<!--begin::Authentication - Signup Welcome Message -->
			<div class="d-flex flex-column flex-center flex-column-fluid">
				<!--begin::Content-->
				<div class="d-flex flex-column flex-center text-center p-10">
					<!--begin::Wrapper-->
					<div class="card card-flush w-lg-650px py-5">
						<div class="card-body py-15 py-lg-20">
							<!--begin::Logo-->
							<div class="">
								{{--<a href="{{route('admin.dashboard')}}" class="">
									<img alt="Logo" src="{{asset('assets/media/logos/freefare last logo-01.png')}}" class="h-100px" />
								</a>--}}
							</div>
							<!--end::Logo-->
							<!--begin::Title-->
							<h1 class="fw-bolder text-gray-900 mb-7">We're Launching Soon</h1>
							<!--end::Title-->
							<!--begin::Counter-->
							<!--(uncomment to display coming soon counter)
    <div class="d-flex flex-center pb-10 pt-lg-5 pb-lg-12">
        <div class="w-65px rounded-3 bg-body shadow-sm py-4 px-5 mx-3">
            <div class="fs-2 fw-bold text-gray-800" id="kt_coming_soon_counter_days"></div>
            <div class="fs-7 fw-semibold text-muted">days</div>
        </div>

        <div class="w-65px rounded-3 bg-body shadow-sm py-4 px-5 mx-3">
            <div class="fs-2 fw-bold text-gray-800" id="kt_coming_soon_counter_hours"></div>
            <div class="fs-7 text-muted">hrs</div>
        </div>

        <div class="w-65px rounded-3 bg-body shadow-sm py-4 px-5 mx-3">
            <div class="fs-2 fw-bold text-gray-800" id="kt_coming_soon_counter_minutes"></div>
            <div class="fs-7 text-muted">min</div>
        </div>

        <div class="w-65px rounded-3 bg-body shadow-sm py-4 px-5 mx-3">
            <div class="fs-2 fw-bold text-gray-800" id="kt_coming_soon_counter_seconds"></div>
            <div class="fs-7 text-muted">sec</div>
        </div>
    </div>
    -->
							<!--end::Counter-->
							<!--begin::Text-->
							<div class="fw-semibold fs-6 text-gray-500 mb-7">This is your opportunity to get creative amazing opportunaties
							<br />that gives readers an idea</div>
							<!--end::Text-->
                            <div class="mb-0">
                                <a href="{{route('admin.dashboard')}}" class="btn btn-sm btn-success">Go To Dashboard</a>
                            </div>
							<!--begin::Form-->
					{{--		<form class="w-md-350px mb-2 mx-auto" action="#" id="kt_coming_soon_form">
								<div class="fv-row text-start">
									<div class="d-flex flex-column flex-md-row justify-content-center gap-3">
										<!--end::Input=-->
										<input type="text" placeholder="Email" name="email" autocomplete="off" class="form-control" />
										<!--end::Input=-->
										<!--begin::Submit-->
										<button class="btn btn-primary text-nowrap" id="kt_coming_soon_submit">
											<!--begin::Indicator label-->
											<span class="indicator-label">Notify Me</span>
											<!--end::Indicator label-->
											<!--begin::Indicator progress-->
											<span class="indicator-progress">Please wait...
											<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
											<!--end::Indicator progress-->
										</button>
										<!--end::Submit-->
									</div>
								</div>
							</form>--}}
							<!--end::Form-->
							<!--begin::Illustration-->
							<div class="mb-n5">
								<img src="{{asset('assets/media/auth/coming-soon.png')}}" class="mw-100 mh-300px theme-light-show" alt="" />
								<img src="{{asset('assets/media/auth/coming-soon-dark.png')}}" class="mw-100 mh-300px theme-dark-show" alt="" />
							</div>
							<!--end::Illustration-->
						</div>
					</div>
					<!--end::Wrapper-->
				</div>
				<!--end::Content-->
			</div>
			<!--end::Authentication - Signup Welcome Message-->
		</div>
		<!--end::Root-->
		<!--begin::Javascript-->
		<script>var hostUrl = "{{asset('assets/')}}";</script>
		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		<script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
		<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Custom Javascript(used for this page only)-->
		<script src="{{asset('assets/js/custom/authentication/sign-up/coming-soon.js')}}"></script>
		<!--end::Custom Javascript-->
		<!--end::Javascript-->
	</body>
	<!--end::Body-->
</html>
