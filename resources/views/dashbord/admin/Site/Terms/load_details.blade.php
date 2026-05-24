<div class="card">
    <!--begin::Body-->
    <div class="card-body p-lg-10 pb-lg-0">
        <!--begin::Layout-->
        <div class="d-flex flex-column flex-xl-row">
            <!--begin::Content-->
            <div class="flex-lg-row-fluid me-xl-15">
                <!--begin::Post content-->
                <div class="mb-17">
                    <!--begin::Body-->
                    <div class="p-0">
                        <!--begin::Title-->
                        <h3 class="fs-2qx fw-bold text-dark">{{$one_data->address}}</h3>
                        <span
                            class="fs-5 fw-semibold text-gray-400">{{$one_data->sub_address}}</span>
                        <!--end::Title-->
                        <!--begin::Text-->
                        <div class="fs-5 fw-semibold text-gray-600 mt-4">
                            {!! $one_data->details !!}

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



