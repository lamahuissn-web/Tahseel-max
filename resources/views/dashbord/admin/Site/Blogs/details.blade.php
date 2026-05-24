@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('blog.create')}}</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">

                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                        {{trans('Toolbar.home')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.site')}}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.blog.index') }}"
                       class="text-muted text-hover-primary"> {{trans('Toolbar.blog')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.blogDetails')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.blog.create')}}"
                   class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                    <span class="svg-icon svg-icon-2">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
														<rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                                                              rx="1" transform="rotate(-90 11.364 20.364)"
                                                              fill="currentColor"/>
														<rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                                              fill="currentColor"/>
													</svg>
												</span>
                    <!--end::Svg Icon-->
                </a>
                <a href="{{route('admin.blog.index')}}"
                   class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4">

                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/arrows/arr054.svg-->
                    <span class="svg-icon svg-icon-2">
                                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                       <path
                                           d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z"
                                           fill="currentColor"/>
                                   </svg>
                                </span>
                    <!--end::Svg Icon-->
                </a>

            </div>
            <!--end::Filter menu-->
            <!--begin::Secondary button-->
            <!--end::Secondary button-->
            <!--begin::Primary button-->
            <!--end::Primary button-->
        </div>
        <!--end::Actions-->
    </div>
    <!--end::Toolbar container-->
@endsection
@section('content')
    {{--    {{dd($one_data)}}--}}

    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxxl">
        <!--begin::Post card-->
        <div class="card">
            <!--begin::Body-->
            <div class="card-body p-lg-10 pb-lg-0">
                <!--begin::Layout-->
                <div class="d-flex flex-column flex-xl-row">
                    <!--begin::Content-->
                    <div class="flex-lg-row-fluid me-xl-15">
                        <!--begin::Post content-->
                        <div class="mb-17">
                            <!--begin::Wrapper-->
                            <div class="mb-8">
                                <!--begin::Container-->
                                <div class="overlay mt-0">
                                    <!--begin::Image-->
                                    <div
                                        class="bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-325px"
                                        style="background-image:url('{{$one_data->blogImage}}')"></div>
                                    <!--end::Image-->
                                    <!--begin::Links-->
                                {{--  <div class="overlay-layer card-rounded bg-dark bg-opacity-25">
                                      <a href="../../demo1/dist/pages/about.html" class="btn btn-primary">About Us</a>
                                      <a href="../../demo1/dist/pages/careers/apply.html" class="btn btn-light-primary ms-3">Join Us</a>
                                  </div>--}}
                                <!--end::Links-->
                                </div>
                                <!--end::Container-->
                            </div>
                            <!--end::Wrapper-->
                            <!--begin::Body-->
                            <div class="p-0">
                                <!--begin::Info-->
                                <div class="d-flex align-items-center justify-content-between pb-4">
                                    <!--begin::Date-->
                                    <div class="text-gray-500 fs-5">
                                        <!--begin::Date-->
                                        <span class="me-2 fw-bold">Posted {{$one_data->blogDate}}. by</span>
                                        <!--end::Date-->
                                        <!--begin::Author-->
                                        <span class="fw-semibold">{{$one_data->blogPublisher}}</span>
                                        <!--end::Author-->
                                    </div>
                                    <!--end::Date-->

                                </div>
                                <!--end::Info-->
                                <!--begin::Title-->
                                <a href="#"
                                   class="fw-bold text-dark mb-3 fs-2hx lh-sm text-hover-primary">{{$one_data->blogTitle}}</a>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <div class="fs-5 fw-semibold text-gray-600 mt-4">
                                    {!! $one_data->blogDetails !!}

                                </div>
                                <!--end::Text-->
                                <!--end::Body-->
                            </div>
                            <!--end::Post content-->
                            <!--begin::Block-->
                            <div class="d-flex align-items-center border border-dashed card-rounded p-5 p-lg-10 mb-14">
                                <!--begin::Section-->
                                <div class="text-center flex-shrink-0 me-7 me-lg-13">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-70px symbol-circle mb-2">
                                        <img src="assets/media/avatars/300-2.jpg" class="" alt=""/>
                                    </div>
                                    <!--end::Avatar-->
                                    <!--begin::Info-->
                                    <div class="mb-0">
                                        <a href="../../demo1/dist/pages/user-profile/overview.html"
                                           class="text-gray-700 fw-bold text-hover-primary">Jane Johnson</a>
                                        <span class="text-gray-400 fs-7 fw-semibold d-block mt-1">Co-founder</span>
                                    </div>
                                    <!--end::Info-->
                                </div>
                                <!--end::Section-->
                                <!--begin::Text-->
                                <div class="mb-0 fs-6">
                                    <div class="text-muted fw-semibold lh-lg mb-2">First, a disclaimer – the entire
                                        process of writing a blog post often takes more than a couple of hours, even if
                                        you can type eighty words per minute and your writing skills are sharp writing a
                                        blog post often takes more than a couple.
                                    </div>
                                    <a href="../../demo1/dist/pages/user-profile/overview.html"
                                       class="fw-semibold link-primary">Author’s Profile</a>
                                </div>
                                <!--end::Text-->
                            </div>
                            <!--end::Block-->
                            <!--begin::Link-->
                            <div class="mb-17">
                                <!--begin::Icons-->
                                <div class="d-flex flex-center">
                                    <!--begin::Icon-->
                                    <a href="#" class="mx-4">
                                        <img src="assets/media/svg/brand-logos/facebook-4.svg" class="h-20px my-2"
                                             alt=""/>
                                    </a>
                                    <!--end::Icon-->
                                    <!--begin::Icon-->
                                    <a href="#" class="mx-4">
                                        <img src="assets/media/svg/brand-logos/instagram-2016.svg" class="h-20px my-2"
                                             alt=""/>
                                    </a>
                                    <!--end::Icon-->
                                    <!--begin::Icon-->
                                    <a href="#" class="mx-4">
                                        <img src="assets/media/svg/brand-logos/github.svg" class="h-20px my-2" alt=""/>
                                    </a>
                                    <!--end::Icon-->
                                    <!--begin::Icon-->
                                    <a href="#" class="mx-4">
                                        <img src="assets/media/svg/brand-logos/behance.svg" class="h-20px my-2" alt=""/>
                                    </a>
                                    <!--end::Icon-->
                                    <!--begin::Icon-->
                                    <a href="#" class="mx-4">
                                        <img src="assets/media/svg/brand-logos/pinterest-p.svg" class="h-20px my-2"
                                             alt=""/>
                                    </a>
                                    <!--end::Icon-->
                                    <!--begin::Icon-->
                                    <a href="#" class="mx-4">
                                        <img src="assets/media/svg/brand-logos/twitter.svg" class="h-20px my-2" alt=""/>
                                    </a>
                                    <!--end::Icon-->
                                    <!--begin::Icon-->
                                    <a href="#" class="mx-4">
                                        <img src="assets/media/svg/brand-logos/dribbble-icon-1.svg" class="h-20px my-2"
                                             alt=""/>
                                    </a>
                                    <!--end::Icon-->
                                </div>
                                <!--end::Icons-->
                            </div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Layout-->

                </div>

                <!--end::Body-->
            </div>
            <!--end::Post card-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->

@endsection
@section('js')


@endsection
