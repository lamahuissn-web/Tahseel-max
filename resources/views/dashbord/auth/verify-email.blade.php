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
    <!--begin::Authentication - Verify Email -->
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
                    <div class="text-center mb-10">
                        <!--begin::Title-->
                        <h1 class="text-dark fw-bolder mb-3">Verify Email</h1>
                        <!--end::Title-->
                        <!--begin::Link-->
                        <div class="text-gray-500 fw-semibold fs-6">
                            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                        </div>
                        <!--end::Link-->
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success mb-4 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                        </div>
                    @endif

                    <div class="d-flex flex-wrap justify-content-center pb-lg-0">
                        <form method="POST" action="{{ route('admin.verification.send') }}" class="me-4">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">{{ __('Resend Verification Email') }}</span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-light">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Form-->
        </div>
        <!--end::Body-->
    </div>
    <!--end::Authentication - Verify Email-->
</div>
<!--end::Root-->

@include('dashbord.layouts.footer-scripts')

<!--begin::Javascript-->
<script>var hostUrl = "{{URL::asset('assets')}}/";</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{URL::asset('assets/plugins/global/plugins.bundle.js')}}"></script>
<script src="{{URL::asset('assets/js/scripts.bundle.js')}}"></script>
<!--end::Global Javascript Bundle-->
</body>
</html>
