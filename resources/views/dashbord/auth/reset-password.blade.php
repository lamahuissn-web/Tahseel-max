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

                    <!--begin::Form-->
                    <form class="form w-100" method="POST"
                          action="{{route('admin.password.store')}}">
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
                            <h1 class="text-dark fw-bolder mb-3">Setup New Password</h1>
                            <!--end::Title-->
                            <!--begin::Link-->
                            <div class="text-gray-500 fw-semibold fs-6">Have you already reset the password ?
                                <a href="{{route('admin.login')}}"
                                   class="link-primary fw-bold">Sign in</a>
                            </div>
                            <!--end::Link-->
                        </div>
                        <!--begin::Heading-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-8" data-kt-password-meter="true">
                            <!--begin::Wrapper-->
                            <div class="mb-1">
                                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                                    <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                            <!--begin::Input wrapper-->
                                <div class="position-relative mb-3">
                                    <input class="form-control bg-transparent" type="password" placeholder="Password"
                                           name="password" autocomplete="off"
                                           id="password"
                                    />
                                    <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                          data-kt-password-meter-control="visibility">
												<i class="bi bi-eye-slash fs-2"></i>
												<i class="bi bi-eye fs-2 d-none"></i>
											</span>
                                </div>
                                <!--end::Input wrapper-->
                                <!--begin::Meter-->
                                <div class="d-flex align-items-center mb-3" data-kt-password-meter-control="highlight">
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
                                </div>
                                <!--end::Meter-->
                            </div>
                            <!--end::Wrapper-->
                            <!--begin::Hint-->
                            <div class="text-muted">Use 8 or more characters with a mix of letters, numbers & symbols.
                            </div>
                            <!--end::Hint-->
                        </div>
                        <!--end::Input group=-->
                        <!--end::Input group=-->
                        <div class="fv-row mb-8">
                            <!--begin::Repeat Password-->
                            <input type="password" placeholder="Repeat Password" id="password_confirmation"
                                   name="password_confirmation" autocomplete="off" class="form-control bg-transparent"/>
                            <!--end::Repeat Password-->
                        </div>
                        <!--end::Input group=-->
                        <!--begin::Input group=-->
{{--                        <div class="fv-row mb-8">--}}
{{--                            <label class="form-check form-check-inline">--}}
{{--                                <input class="form-check-input" type="checkbox" name="toc" value="1"/>--}}
{{--                                <span class="form-check-label fw-semibold text-gray-700 fs-6 ms-1">I Agree &--}}
{{--										<a href="#" class="ms-1 link-primary">Terms and conditions</a>.</span>--}}
{{--                            </label>--}}
{{--                        </div>--}}
                        <!--end::Input group=-->
                        <!--begin::Action-->
                        <div class="d-grid mb-10">
                            <button type="submit"  class="btn btn-primary">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">{{ __('Reset Password') }}</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">Please wait...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                        <!--end::Action-->
                    </form>

                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Form-->

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

{{--<x-guest-layout>--}}
{{--    <form method="POST" action="{{ route('admin.password.store') }}">--}}
{{--    @csrf--}}

{{--    <!-- Password Reset Token -->--}}
{{--        <input type="hidden" name="token" value="{{ $request->route('admin.token') }}">--}}

{{--        <!-- Email Address -->--}}
{{--        <div>--}}
{{--            <x-input-label for="email" :value="__('Email')"/>--}}
{{--            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"--}}
{{--                          :value="old('email', $request->email)" required autofocus autocomplete="username"/>--}}
{{--            <x-input-error :messages="$errors->get('email')" class="mt-2"/>--}}
{{--        </div>--}}

{{--        <!-- Password -->--}}
{{--        <div class="mt-4">--}}
{{--            <x-input-label for="password" :value="__('Password')"/>--}}
{{--            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required--}}
{{--                          autocomplete="new-password"/>--}}
{{--            <x-input-error :messages="$errors->get('password')" class="mt-2"/>--}}
{{--        </div>--}}

{{--        <!-- Confirm Password -->--}}
{{--        <div class="mt-4">--}}
{{--            <x-input-label for="password_confirmation" :value="__('Confirm Password')"/>--}}

{{--            <x-text-input id="password_confirmation" class="block mt-1 w-full"--}}
{{--                          type="password"--}}
{{--                          name="password_confirmation" required autocomplete="new-password"/>--}}

{{--            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>--}}
{{--        </div>--}}

{{--        <div class="flex items-center justify-end mt-4">--}}
{{--            <x-primary-button>--}}
{{--                {{ __('Reset Password') }}--}}
{{--            </x-primary-button>--}}
{{--        </div>--}}
{{--    </form>--}}
{{--</x-guest-layout>--}}
