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
                        <label>{{trans('complaints.name')}}</label>
                        <h5 class="fs-2qx fw-bold text-dark">{{$one_data->name}}</h5>
                        
                        <label>{{trans('complaints.Submitted_by')}}</label>
                        <h5 class="fs-2qx fw-bold text-dark">{{$one_data->Submitted_by}}</h5>
                        
                        <label>{{trans('complaints.type')}}</label>
                         <h5 class="fs-2qx fw-bold text-dark">{{$one_data->type}}</h5>
                   
                        <label>{{trans('complaints.description')}}</label>
                        <div class="fs-2qx fw-semibold text-black-600 mt-4">

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



