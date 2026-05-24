@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('videos.create')}}</h1>
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
                    <a href="{{ route('admin.videos.index') }}"
                       class="text-muted text-hover-primary"> {{trans('Toolbar.videos')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.eventDetails')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.videos.create')}}"
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
                <a href="{{route('admin.videos.index')}}"
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

                                    <!--begin::Youtube-->
                                    <a
                                        class="d-block bgi-no-repeat bgi-size-cover bgi-position-center rounded position-relative min-h-175px"
                                        style="background-image:url('{{$one_data->videoImage}}')"
                                        data-fslightbox="lightbox-youtube"
                                        href="https://www.youtube.com/embed/{{$one_data->videoLinkid}}"
                                    >
                                        <!--begin::Icon-->
                                        <img src="{{asset('assets/media/svg/misc/video-play.svg')}}"
                                             class="position-absolute top-50 start-50 translate-middle" alt=""/>
                                        <!--end::Icon-->
                                    </a>
                                    <!--end::Youtube-->


                                    <iframe id="vimeo" style="display:none"
                                            src="https://player.vimeo.com/video/22439234" width="1920px" height="1080px"
                                            frameBorder="0" allow="autoplay; fullscreen" allowFullScreen></iframe>
                                    <!--end::Custom source(Vimeo)-->
                                </div>
                                <!--end::Container-->
                            </div>
                            <!--end::Wrapper-->
                            <!--begin::Body-->
                            <div class="p-0">
                                <!--begin::Info-->
                                <div class="d-flex align-items-center justify-content-between pb-4">

                                    <!--end::Info-->
                                    <!--begin::Title-->
                                    <a href="#"
                                       class="fw-bold text-dark mb-3 fs-2hx lh-sm text-hover-primary">{{$one_data->videoTitle}}</a>
                                    <!--end::Title-->

                                    <!--end::Body-->
                                </div>
                                <!--end::Post content-->

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
    </div>
    <!--end::Content wrapper-->

@endsection
@section('js')
    <script src="{{asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js')}}"></script>

@endsection
