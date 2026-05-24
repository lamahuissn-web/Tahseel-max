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
                                <img src="{{asset('assets/media/svg/misc/video-play.svg')}}"  class="position-absolute top-50 start-50 translate-middle" alt=""/>
                                <!--end::Icon-->
                            </a>
                            <!--end::Youtube-->



                            <iframe id="vimeo" style="display:none" src="https://player.vimeo.com/video/22439234" width="1920px" height="1080px" frameBorder="0" allow="autoplay; fullscreen" allowFullScreen></iframe>
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



