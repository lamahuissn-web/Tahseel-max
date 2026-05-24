



<div class="d-flex flex-wrap flex-sm-nowrap  mb-6">
   

    <div class="me-7 mb-4">
        <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
            <img src="{{ $all_data->profile_picture && file_exists(public_path('images/' . $all_data->profile_picture)) ? asset('images/' . $all_data->profile_picture) : asset('images/default-user-icon.png') }}" alt="{{ $all_data->first_name }} {{ $all_data->last_name }}" />
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
                        class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{ $all_data->first_name }} {{ $all_data->last_name }}</a>
                    <a href="#">
                        <i class="bi bi-patch-check fs-1 text-primary"></i>
                    </a>

                </div>

                <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                    <a href="#"
                        target="_blank"
                        class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                        <i class="bi bi-person-badge fs-4 me-1"></i> {{ $all_data->emp_code }}
                    </a>
                    <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                        <i class="bi bi-envelope fs-4 me-1"></i> {{ $all_data->email }}</a>
                    <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                        <i class="bi bi-briefcase fs-4 me-1"></i> {{ $all_data->position }}</a>
                    <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                        <i class="bi bi-currency-dollar fs-4 me-1"></i> {{ number_format($all_data->salary, 2) }}</a>
                </div>
                <!--end::Info-->
            </div>
            <!--end::User-->
            <!--begin::Actions-->
            <div class="d-flex my-4">
                <a href="{{ route('admin.employee_data') }}" class="btn btn-sm btn-light me-2"
                    id="kt_user_follow_button">
                    <i class="ki-duotone ki-check fs-3 d-none"></i>
                    <span class="indicator-label">{{ trans('invoices.back') }}</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>

                </a>


            </div>
            <!--end::Actions-->
        </div>


        <div class="d-flex flex-wrap flex-stack">
            <div class="d-flex flex-column flex-grow-1 pe-8">
                <div class="d-flex flex-wrap">
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-badge fs-3 text-primary me-2"></i>
                            <div class="fs-2 fw-bold">{{ $all_data->emp_code }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('employees.employee_code') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-briefcase fs-3 text-info me-2"></i>
                            <div class="fs-2 fw-bold">{{ $all_data->position }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('employees.position') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-currency-dollar fs-3 text-success me-2"></i>
                            <div class="fs-2 fw-bold">{{ number_format($all_data->salary, 2) }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('employees.salary') }}</div>
                    </div>

                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cash-coin fs-3 text-warning me-2"></i>
                            <div class="fs-2 fw-bold">{{ number_format($revenues_data->sum('amount'), 2) }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('employees.total_revenue') }}</div>
                    </div>


                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cash-coin fs-3 text-warning me-2"></i>
                            <div class="fs-2 fw-bold">{{ number_format(get_employee_account_balance($all_data->id), 2) }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('employees.balance') }}</div>
                    </div>

                    @php
                        $deficit_surplus = $revenues_data->sum('amount')-get_employee_account_balance($all_data->id);
                    @endphp

                    @if($deficit_surplus < 0)
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle fs-3 text-danger me-2"></i>
                            <div class="fs-2 fw-bold text-danger">{{ number_format($deficit_surplus, 2) }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('employees.deficit') }}</div>
                    </div>
                    @endif

                    @if($deficit_surplus > 0)
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle fs-3 text-success me-2"></i>
                            <div class="fs-2 fw-bold text-success">{{ number_format($deficit_surplus, 2) }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500">{{ trans('employees.surplus') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

