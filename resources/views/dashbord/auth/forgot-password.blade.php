<!DOCTYPE html>
<html>
@include('dashbord.layouts.head')
<!--begin::Body-->
<body id="kt_body" class="app-blank app-blank">
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
<!--begin::Root-->
<div class="d-flex flex-column flex-root" id="kt_app_root">
    <!--begin::Authentication - Password reset -->
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <!--begin::Aside-->
        <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center"
             style="background-image: url({{asset('assets/media/misc/auth-bg.png')}})">
            <!--begin::Content-->
            <div class="d-flex flex-column flex-center p-6 p-lg-10 w-100">
                <!--begin::Logo-->
                <a href="{{route('home')}}" class="mb-0 mb-lg-20">
                    <img alt="Logo" src="{{asset('assets/media/logos/freefare last logo-01.png')}}"
                         class="h-40px h-lg-50px"/>
                </a>
                <!--end::Logo-->
                <!--begin::Image-->
                <img class="d-none d-lg-block mx-auto w-300px w-lg-75 w-xl-500px mb-10 mb-lg-20"
                     src="{{asset('assets/media/auth/password-changed.png')}}" alt=""/>
                <!--end::Image-->
              

            </div>
            <!--end::Content-->
        </div>
        <!--begin::Aside-->
        <!--begin::Body-->
        <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10">
            <!--begin::Form-->
            <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                <!--begin::Wrapper-->
                <div class="w-lg-500px p-10">
                    <!--begin::Form-->
                    <form class="form w-100" novalidate="novalidate" method="POST"
                          action="{{ route('admin.password.email') }}">

                    {{--                        <form method="POST" action="{{ route('admin.password.email') }}">--}}
                    @csrf

                    <!--begin::Heading-->
                        <div class="text-center mb-10">
                            <!--begin::Title-->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif                            <h1 class="text-dark fw-bolder mb-3">Forgot Password ?</h1>
                            <!--end::Title-->
                            <!--begin::Link-->
                            <div class="text-gray-500 fw-semibold fs-6">{{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</div>
                            <!--end::Link-->
                        </div>
                        <!--begin::Heading-->
                        <!--begin::Input group=-->
                        <div class="fv-row mb-8">
                            <!--begin::Email-->
                            <input placeholder="Email" id="email" type="email" name="email" value="{{old('email')}}"
                                   required autofocus autocomplete="off" class="form-control bg-transparent"/>
                            <!--end::Email-->
                        </div>
                        <!--begin::Actions-->
                        <div class="d-flex flex-wrap justify-content-center pb-lg-0">
                            <button type="submit" id="" class="btn btn-primary me-4">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">{{ __('Email Password Reset Link') }}</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">Please wait...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                            <a href="{{route('admin.login')}}" class="btn btn-light">Cancel</a>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Form-->
            <!--begin::Footer-->
            <div class="d-flex flex-center flex-wrap px-5">
                <!--begin::Links-->
                <div class="d-flex fw-semibold text-primary fs-base">
                    <a href="https://keenthemes.com" class="px-5" target="_blank">Terms</a>
                    <a href="https://devs.keenthemes.com" class="px-5" target="_blank">Plans</a>
                    <a href="https://themes.getbootstrap.com/product/keen-the-ultimate-bootstrap-admin-theme/"
                       class="px-5" target="_blank">Contact Us</a>
                </div>
                <!--end::Links-->
            </div>
            <!--end::Footer-->
        </div>
        <!--end::Body-->
    </div>
    <!--end::Authentication - Password reset-->
</div>
<!--end::Root-->

@include('dashbord.layouts.footer-scripts')

<!--begin::Javascript-->
<script>var hostUrl = "{{URL::asset('assets')}}/";</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{URL::asset('assets/plugins/global/plugins.bundle.js')}}"></script>
<script src="{{URL::asset('assets/js/scripts.bundle.js')}}"></script>
<!--end::Global Javascript Bundle-->
<!--begin::Custom Javascript(used for this page only)-->
{{--<script src="{{asset('assets/js/custom/authentication/reset-password/reset-password.js')}}"></script>--}}
<!--end::Custom Javascript-->
<!--end::Javascript-->
</body>
<!--end::Body-->
</html>









{{--
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>--}}
