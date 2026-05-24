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
                       
                        <!--end::Container-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Body-->
                    <div class="p-0">
                        <!--begin::Title-->
                        <label>{{trans('notifications.Title')}}</label>
                        <h5 class="fs-2qx fw-bold text-dark">{{$one_data->title}}</h5>
                        
                        <label>{{trans('notifications.Send_To')}}</label>
                        <h5 class="fs-2qx fw-bold text-dark">{{$one_data->send_to}}</h5>
                  
                        <label>{{trans('notifications.Details')}}</label>
                        <div class="fs-2qx fw-semibold text-black-600 mt-4">

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



