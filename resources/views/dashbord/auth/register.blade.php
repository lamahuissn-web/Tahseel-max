<html>
@include('dashbord.layouts.head')
<body id="kt_body" class="app-blank app-blank">

<div class="d-flex flex-column flex-root" id="kt_app_root">
    <!--begin::Authentication - Sign-up -->
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <!--begin::Aside-->
        <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center" style="background-image: url({{asset('assets/media/misc/auth-bg.png')}})">
            <!--begin::Content-->
            <div class="d-flex flex-column flex-center p-6 p-lg-10 w-100">
                <!--begin::Logo-->
                <!--	<a href="../../demo1/dist/index.html" class="mb-0 mb-lg-20"> -->
                <a href="{{route('admin.register')}}" class="mb-0 mb-lg-20">
                    <img alt="Logo" src="{{asset('assets/media/logos/freefare last logo-01.png')}}" class="h-40px h-lg-50px" />
                </a>
                <!--end::Logo-->
                <!--begin::Image-->
                <img class="d-none d-lg-block mx-auto w-300px w-lg-75 w-xl-500px mb-10 mb-lg-20" src="{{asset('assets/media/misc/LOGO.png')}}" alt="" />
                <!--end::Image-->
                <!--begin::Title-->
                <h1 class="d-none d-lg-block text-white fs-2qx fw-bold text-center mb-7">Fast, Efficient and Productive</h1>
                <!--end::Title-->
                <!--begin::Text-->
                <div class="d-none d-lg-block text-white fs-base text-center">In this kind of post,
                    <a href="#" class="opacity-75-hover text-warning fw-semibold me-1">the blogger</a>introduces a person they’ve interviewed
                    <br />and provides some background information about
                    <a href="#" class="opacity-75-hover text-warning fw-semibold me-1">the interviewee</a>and their
                    <br />work following this is a transcript of the interview.</div>
                <!--end::Text-->
            </div>
            <!--end::Content-->
        </div>
        <!--begin::Aside-->
        <!--begin::Body-->
        <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10">
            <!--begin::Form-->
            <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                <!--begin::Wrapper-->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="w-lg-500px p-10">
                    <!--begin::Form-->
                    <form method="POST" action="{{ route('admin.register') }}" class="form w-100" enctype="multipart/form-data">
                        <!-- id="kt_sign_up_form"  -->
                    @csrf
                    <!--begin::Heading-->
                        <div class="text-center mb-11">
                            <!--begin::Title-->
                            <h1 class="text-dark fw-bolder mb-3">Sign Up</h1>
                            <!--end::Title-->

                        </div>

                        <!--begin::Input group=-->
                        <div class="fv-row mb-8">



                            <input type="text" placeholder="name" name="name" autocomplete="off" class="form-control bg-transparent" />


                        </div>
                        <div class="fv-row mb-8">
                            <input type="text" placeholder="Email" name="email" autocomplete="off" class="form-control bg-transparent" />

                        </div>
                        <!--begin::Input group-->
                        <div class="fv-row mb-8" data-kt-password-meter="true">
                            <!--begin::Wrapper-->
                            <div class="mb-1">
                                <!--begin::Input wrapper-->
                                <div class="position-relative mb-3">
                                    <input class="form-control bg-transparent" type="password" placeholder="Password" name="password" autocomplete="off" />
                                    <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
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
                            <div class="text-muted">Use 8 or more characters with a mix of letters, numbers & symbols.</div>
                            <!--end::Hint-->
                        </div>
                        <!--end::Input group=-->
                        <!--end::Input group=-->
                        <div class="fv-row mb-8">
                            <!--begin::Repeat Password-->
                            <input placeholder="Repeat Password" name="password_confirmation" type="password" autocomplete="off" class="form-control bg-transparent" />
                            <!--end::Repeat Password-->
                        </div>

                      {{--  <!-- upload image-->
                        <div class="fv-row mb-8">
                            <input type="file" name="photo" >


                        </div>--}}
                        <!--end::Input group=-->
                        <!--begin::Accept-->
                        <div class="fv-row mb-8">
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="toc" value="1" />
                                <span class="form-check-label fw-semibold text-gray-700 fs-base ms-1">I Accept the
										<a href="#" class="ms-1 link-primary">Terms</a></span>
                            </label>
                        </div>
                        <!--end::Accept-->
                        <!--begin::Submit button-->
                        <div class="d-grid mb-10">
                            <button type="submit" id="kt_sign_up_submit" class="btn btn-primary">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">Sign up</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">Please wait...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                        <!--end::Submit button-->
                        <!--begin::Sign up-->
                        <div class="text-gray-500 text-center fw-semibold fs-6">Already have an Account?
                            <a href="{{route('admin.login')}}" class="link-primary fw-semibold">Sign in</a></div>
                        <!--end::Sign up-->
                    </form>

                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Form-->

        </div>

</body>
<!--end::Body-->
@include('dashbord.layouts.footer-scripts')
</html>

