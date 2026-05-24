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
                            @if(isset($one_data->photoImgaes)&&(!empty($one_data->photoImgaes)))
                                <div class="tns" style="direction: ltr">
                                    <div data-tns="true" data-tns-nav-position="bottom" data-tns-mouse-drag="true"
                                         data-tns-controls="true">
                                    @foreach($one_data->photoImgaes as $photoImgae)
                                        <!--begin::Item-->
                                            <div class="text-center px-5 pt-5 pt-lg-10 px-lg-10">
                                                <img src="{{$photoImgae->image}}" class="card-rounded shadow mw-100"
                                                     alt=""/>
                                            </div>
                                            <!--end::Item-->
                                        @endforeach
                                    </div>
                                </div>
                        @endif
                        {{--{{dd($one_data->photoImgaes)}}--}}
                            <!--begin::Image-->
{{--                            <div--}}
{{--                                class="bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-325px"--}}
{{--                                style="background-image:url('{{$one_data->photoImage}}')"></div>--}}
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

                            <!--end::Info-->
                            <!--begin::Title-->
                            <a href="#"
                               class="fw-bold text-dark mb-3 fs-2hx lh-sm text-hover-primary">{{$one_data->photoTitle}}</a>
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



