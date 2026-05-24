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
                        <label>{{trans('devices_exercises.name')}}</label>
                        <h3 class="fs-2qx fw-bold text-dark">{{$one_data->name}}</h3>
                         <label>{{trans('devices_exercises.device_code')}}</label>
                        
                           <h3 class="fs-2qx fw-bold text-dark"> {{$one_data->device_code}} </h3>
                           <label>{{trans('devices_exercises.exercise_type')}}</label>
                            <h3 class="fs-2qx fw-bold text-dark">{{$one_data->exercise_level}} </h3>
                       
                        <!--begin::Text-->
                        <label>{{trans('devices_exercises.groups')}}</label>
                        <h3 class="fs-2qx fw-bold text-dark">{{$one_data->groups}} </h3>
                        <label>{{trans('devices_exercises.numbers')}}</label>
                        <h3 class="fs-2qx fw-bold text-dark">{{$one_data->numbers}} </h3>

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



