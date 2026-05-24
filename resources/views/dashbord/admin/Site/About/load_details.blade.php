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
                                style="background-image:url('{{$one_data->Image}}')"></div>
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
                        <!--begin::Title-->
                        <h3 class="fs-2qx fw-bold text-dark">{{$one_data->MainAddress}}</h3>
                        <span
                            class="fs-5 fw-semibold text-gray-400">{{$one_data->SecondAddress}}</span>
                        <!--end::Title-->
                        <!--begin::Text-->
                        <div class="fs-5 fw-semibold text-gray-600 mt-4">
                            {!! $one_data->Details !!}

                        </div>
                        <!--end::Text-->
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



