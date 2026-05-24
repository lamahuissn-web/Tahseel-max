<div class="d-flex flex-wrap flex-sm-nowrap  mb-6">
    <!--begin: Pic-->

    <div class="me-7 mb-4">
        <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
            <img src="{{ asset('images/avatar.jpg') }}" alt="image" />
            <div
                class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px">
            </div>
        </div>
    </div>

    <div class="flex-grow-1">

        <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">

            <div class="d-flex flex-column">

                <div class="d-flex align-items-center mb-2">
                    <a href="#"
                        class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{ $all_data->name }}</a>
                    <a href="#">
                        <i class="bi bi-patch-check fs-1 text-primary"></i>
                    </a>

                </div>

                <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $all_data->phone) }}"
                        target="_blank"
                        class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                        <i class="bi bi-telephone fs-4 me-1"></i> {{ $all_data->phone }}
                    </a>
                    <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                        <i class="bi bi-geo-alt fs-4 me-1"></i> {{ $all_data->address1 }}</a>
                </div>
                <!--end::Info-->
            </div>
            <!--end::User-->
            <!--begin::Actions-->
            <div class="d-flex my-4">
                <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-light me-2"
                    id="kt_user_follow_button">
                    <i class="ki-duotone ki-check fs-3 d-none"></i>
                    <span class="indicator-label">{{ trans('invoices.back') }}</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>

                </a>


            </div>
            <!--end::Actions-->
        </div>
        <!--end::Title-->
        <!--begin::Stats-->
        {{-- <div class="d-flex flex-wrap flex-stack">
            <!--begin::Wrapper-->
            <div class="d-flex flex-column flex-grow-1 pe-8">
                <!--begin::Stats-->
                <div class="d-flex flex-wrap">
                    <!--begin::Stat-->


                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <!--begin::Number-->
                        <div class="d-flex align-items-center">
                            <i class="bi bi-book-fill fs-3 text-info me-2"></i>
                            <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $unpaid_data->count() }}">{{ $unpaid_data->count() }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('invoices.unpaid_data') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <!--begin::Number-->
                        <div class="d-flex align-items-center">
                            <i class="bi bi-people-fill fs-3 text-primary me-2"></i>
                            <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $paid_data->count() }}"
                                data-kt-countup-prefix="">
                                {{ $paid_data->count() }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('invoices.paid_data') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle fs-3 text-success me-2"></i>
                            <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $total_unpaid }}"
                                data-kt-countup-prefix="$">
                                {{ $total_unpaid }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('invoices.total_unpaid') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle fs-3 text-success me-2"></i>
                            <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $total_paid }}"
                                data-kt-countup-prefix="$">
                                {{ $total_paid }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('invoices.total_paid') }}</div>
                    </div>



                </div>

            </div>

        </div> --}}

        <div class="d-flex flex-wrap flex-stack">
            <div class="d-flex flex-column flex-grow-1 pe-8">
                <div class="d-flex flex-wrap">
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-receipt fs-3 text-warning me-2"></i>  <!-- Changed to receipt with warning color -->
                            <div class="fs-2 fw-bold">{{ $unpaid_data->count() }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('invoices.unpaid_data') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-receipt-cutoff fs-3 text-success me-2"></i>  <!-- Paid invoice icon -->
                            <div class="fs-2 fw-bold">{{ $paid_data->count() }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('invoices.paid_data') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-currency-dollar fs-3 text-danger me-2"></i>  <!-- Dollar sign for money -->
                            <div class="fs-2 fw-bold">${{ $total_unpaid }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('invoices.total_unpaid') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cash-stack fs-3 text-success me-2"></i>  <!-- Stack of cash for paid amount -->
                            <div class="fs-2 fw-bold">${{ $total_paid }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('invoices.total_paid') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
