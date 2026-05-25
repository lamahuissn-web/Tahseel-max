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
    <!--begin::Authentication - Password confirm -->
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
                    <form class="form w-100" method="POST" action="{{ route('admin.password.confirm') }}">
                        @csrf
                        <!--begin::Heading-->
                        <div class="text-center mb-10">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <!--begin::Title-->
                            <h1 class="text-dark fw-bolder mb-3">Confirm Password</h1>
                            <!--end::Title-->
                            <!--begin::Link-->
                            <div class="text-gray-500 fw-semibold fs-6">
                                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                            </div>
                            <!--end::Link-->
                        </div>
                        <!--begin::Heading-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-8">
                            <!--begin::Password-->
                            <input class="form-control bg-transparent" type="password" placeholder="Password"
                                   name="password" autocomplete="current-password" required id="password"/>
                            <!--end::Password-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Actions-->
                        <div class="d-grid mb-10">
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">{{ __('Confirm') }}</span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Form-->
        </div>
        <!--end::Body-->
    </div>
    <!--end::Authentication - Password confirm-->
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
