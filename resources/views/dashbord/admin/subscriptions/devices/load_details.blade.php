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
                        <div class="row col-12">
                            <div class=" col-6">
                                <img style="width: 100%" src="{{$one_data->image_url}}">
                            </div>
                            <div class="col-6">
                                {!! $qrCode !!}
                            </div>
                        </div>
                        {{--<!--begin::Container-->
                        <div class="overlay mt-0">
                            <!--begin::Image-->
                            <div
                                class="bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-325px"
                                style="background-image:url('{{$one_data->image_url}}')"></div>

                           </div>
                        <!--end::Container-->--}}
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Body-->
                    <div class="p-0">
                        <!--begin::Title-->
                        <label>{{trans('devices.name')}}</label>

                        <h3 class="fs-2qx fw-bold text-dark">{{$one_data->name}}</h3>
                        <label>{{trans('devices.code')}}</label>

                        <h3 class="fs-2qx fw-bold text-dark">{{$one_data->code}}</h3>

                        <label>{{trans('devices.exercise_type')}}</label>

                        <h3 class="fs-2qx fw-bold text-dark">{{$one_data->exercise_type}}</h3>

                        <!--begin::Text-->
                        <label>{{trans('devices.description')}}</label>

                        <div class="fs-5 fw-semibold text-gray-600 mt-4">

                            {!! $one_data->description !!}

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



