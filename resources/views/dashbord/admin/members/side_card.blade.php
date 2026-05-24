<div class="flex-column flex-lg-row-auto w-100 w-xl-350px mb-10">
    <!--begin::Card-->
    <div class="card mb-5 mb-xl-8">
        <!--begin::Card body-->
        <div class="card-body pt-15">
            <!--begin::Summary-->
            <div class="d-flex flex-center flex-column mb-5">
                <!--begin::Avatar-->
                <div class="symbol symbol-100px symbol-circle mb-7">
                    <img
                        src="{{$one_data->memberImage}}"
                        alt="assets/media/avatars/300-23.jpg"
                        class="border border-white border-4 intense"
                        style="border-radius: 50%"/>

                </div>
                <!--end::Avatar-->
                <!--begin::Name-->
                <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">
                    {{$one_data->memberName}}
                </a>
                <!--end::Name-->

                <!--begin::Info-->
                <div class="d-flex flex-wrap flex-center">
                    <!--begin::Stats-->
                    <div class="border border-gray-300 border-dashed rounded py-3 px-3 mb-3">
                        <div class="fs-4 fw-bold text-gray-700">
                            <span class="w-75px">5</span>
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
                            <span class="svg-icon svg-icon-3 svg-icon-success">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512">
                                                <path
                                                    d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm32 32h-64c-17.6 0-33.5 7.1-45.1 18.6 40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64zm-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32 208 82.1 208 144s50.1 112 112 112zm76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2zm-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"/></svg>

                                        </span>
                            <!--end::Svg Icon-->
                        </div>
                        <div class="fw-semibold text-muted">{{trans('members.subscriptions')}}</div>
                    </div>
                    <!--end::Stats-->
                    <!--begin::Stats-->
                    <div
                        class="border border-gray-300 border-dashed rounded py-3 px-3 mx-4 mb-3">
                        <div class="fs-4 fw-bold text-gray-700">
                            <span class="w-50px">good</span>
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr065.svg-->
                            <span class="svg-icon svg-icon-3 svg-icon-danger">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <rect opacity="0.5" x="11" y="18" width="13" height="2" rx="1"
                                                      transform="rotate(-90 11 18)" fill="currentColor"/>
                                                <path
                                                    d="M11.4343 15.4343L7.25 11.25C6.83579 10.8358 6.16421 10.8358 5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75L11.2929 18.2929C11.6834 18.6834 12.3166 18.6834 12.7071 18.2929L18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25C17.8358 10.8358 17.1642 10.8358 16.75 11.25L12.5657 15.4343C12.2533 15.7467 11.7467 15.7467 11.4343 15.4343Z"
                                                    fill="currentColor"/>
                                            </svg>
                                        </span>
                            <!--end::Svg Icon-->
                        </div>
                        <div
                            class="fw-semibold text-muted">{{trans('members.body_status')}}</div>
                    </div>
                    <!--end::Stats-->
                    <!--begin::Stats-->
                    <div class="border border-gray-300 border-dashed rounded py-3 px-3 mb-3">
                        <div class="fs-4 fw-bold text-gray-700">
                            <span class="w-50px">10</span>
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
                            <span class="svg-icon svg-icon-3 svg-icon-success">
                                          <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                                              <path
                                                  d="M499.99 176h-59.87l-16.64-41.6C406.38 91.63 365.57 64 319.5 64h-127c-46.06 0-86.88 27.63-103.99 70.4L71.87 176H12.01C4.2 176-1.53 183.34.37 190.91l6 24C7.7 220.25 12.5 224 18.01 224h20.07C24.65 235.73 16 252.78 16 272v48c0 16.12 6.16 30.67 16 41.93V416c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32v-32h256v32c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32v-54.07c9.84-11.25 16-25.8 16-41.93v-48c0-19.22-8.65-36.27-22.07-48H494c5.51 0 10.31-3.75 11.64-9.09l6-24c1.89-7.57-3.84-14.91-11.65-14.91zm-352.06-17.83c7.29-18.22 24.94-30.17 44.57-30.17h127c19.63 0 37.28 11.95 44.57 30.17L384 208H128l19.93-49.83zM96 319.8c-19.2 0-32-12.76-32-31.9S76.8 256 96 256s48 28.71 48 47.85-28.8 15.95-48 15.95zm320 0c-19.2 0-48 3.19-48-15.95S396.8 256 416 256s32 12.76 32 31.9-12.8 31.9-32 31.9z"/></svg>
                                        </span>
                            <!--end::Svg Icon-->
                        </div>
                        <div class="fw-semibold text-muted">{{trans('members.inbody')}}</div>
                    </div>
                    <!--end::Stats-->
                </div>
                <!--end::Info-->
            </div>
            <!--end::Summary-->
            <!--begin::Details toggle-->
            <div class="d-flex flex-stack fs-4 py-3">
                <div class="fw-bold rotate collapsible" data-bs-toggle="collapse"
                     href="#kt_customer_view_details"
                     role="button" aria-expanded="false"
                     aria-controls="kt_customer_view_details">{{trans('members.details')}}
                    <span class="ms-2 rotate-180">
                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr072.svg-->
                                <span class="svg-icon svg-icon-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z"
                                            fill="currentColor"/>
                                    </svg>
                                </span>
                        <!--end::Svg Icon-->
                            </span></div>

            </div>
            <!--end::Details toggle-->
            <div class="separator separator-dashed my-3"></div>
            <!--begin::Details content-->
            <div id="kt_customer_view_details" class="collapse show">
                <div class="py-5 fs-6">
                    <div class="fw-bold mt-5">{{trans('members.email')}}</div>
                    <div class="text-gray-600">{{$one_data->memberEmail}}</div>
                    <div class="fw-bold mt-5">{{trans('members.phone')}}</div>
                    <div class="text-gray-600">{{$one_data->memberPhone}}</div>
                    <div class="fw-bold mt-5">{{trans('members.birth_date')}}</div>
                    <div class="text-gray-600">{{$one_data->memberBirthDate}}</div>


                </div>
            </div>
            <!--end::Details content-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

</div>
