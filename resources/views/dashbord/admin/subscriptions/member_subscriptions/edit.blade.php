@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{ trans('sub.main_subscriptions') }}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                        class="text-muted text-hover-primary">{{ trans('Toolbar.home') }}</a>
                </li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('Toolbar.subscriptions') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('sub.member_subscriptions') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('sub.add_new_subscription') }}</li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4"
                    href="{{ route('admin.subscriptions.member_subscriptions.index') }}">
                    {{--                    <i class="bi bi-arrow-clockwise ">{{trans('sub.back')}}</i> --}}
                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/arrows/arr054.svg-->
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                </a>
            </div>
        </div>
    </div>



@endsection
@section('content')



    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="t_container">
            <div class="card shadow-sm ">
                <div class="card-header">
                    <h3 class="card-title"></i> {{ trans('sub.add_new_subscription') }}</h3>

                </div>

                <form id="save_form" method="post"
                    action="{{ route('admin.subscriptions.member_subscriptions.update', $one_data[0]->process_num) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                        @endif

                        <div class="row" style="margin-top: 20px">

                            <div class="col-md-4">
                                <label class="required fs-6 fw-semibold mb-2">{{ trans('members.member_name') }}</label>
                                <select class="form-control form-control-solid" data-control="select2"
                                    data-hide-search="true" name="member_id" id="member_id">
                                    <option>{{ trans('forms.select') }}</option>

                                    @foreach ($members as $key)
                                        <option value="{{ $key->id }}"
                                            {{ old('member_id', $one_data[0]->member_id) == $key->id ? 'selected' : '' }}>
                                            {{ $key->member_name }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-3   mb-5">
                                <label class="required fs-6 fw-semibold mb-2">{{ trans('members.pay_method') }}</label>
                                <select onchange="pay_type(this, 1)"
                                    class="form-control form-control-solid pay-method-select" name="pay_method"
                                    id="pay_method-1">
                                    <?php $pay_method_arr = ['cache' => trans('members.cache'), 'visa' => trans('members.visa'), 'bank' => trans('members.bank')]; ?>
                                    <option>{{ trans('forms.select') }}</option>
                                    @foreach ($pay_method_arr as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ $key == $one_data[0]->pay_method ? 'selected' : '' }}> {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3   mb-5 transfer-image-dev"
                                style="{{ $one_data[0]->pay_method == 'bank' ? 'display: block' : 'display: none' }}"
                                id="transfer_image_dev-1">
                                <label class="required fs-6 fw-semibold mb-2">{{ trans('members.transfer_image') }}</label>
                                <input class="form-control form-control-solid" type="file" name="transfer_image"
                                    id="transfer_image-1" accept="image/*">
                            </div>


                        </div>


                        <br>
                        <hr>
                        <input type="hidden" name="process_num" value="{{ $one_data[0]->process_num }}">

                        <div class="form-group">
                            @foreach ($one_data as $index => $sub)
                                <div data-repeater-item>
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="form-group row mb-5">
                                                <div class="row" style="margin-top: 10px">
                                                    <div class="col-md-3   mb-5">
                                                        <label class="form-label">{{ trans('members.category') }}</label>
                                                        <select onchange="get_subscription(this, {{ $index }})"
                                                            class="form-control form-control-solid type-select"
                                                            data-control="select2" data-hide-search="true" name="type[]"
                                                            id="type-{{ $index }}">
                                                            <option>{{ trans('forms.select') }}</option>
                                                            @php $cat_arr=['main'=>trans('members.main_subscription'),'special'=>trans('members.special_subscription')] @endphp
                                                            @foreach ($cat_arr as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ $key == $sub->type ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3   mb-5">
                                                        <label
                                                            class="required fs-6 fw-semibold mb-2">{{ trans('members.subscription') }}</label>
                                                        <select onchange="get_sub_details({{ $index }})"
                                                            class="form-control form-control-solid subscription-select"
                                                            data-value-{{ $index }}="{{ $sub->subscription_id }}"
                                                            name="subscription_id[]"
                                                            id="subscription_id-{{ $index }}">
                                                            <option>{{ trans('forms.select') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3   mb-5">
                                                        <label
                                                            class="required fs-6 fw-semibold mb-2">{{ trans('members.subscription_start_date') }}</label>
                                                        <input onchange="get_sub_details({{ $index }})"
                                                            class="form-control form-control-solid" name="start_date[]"
                                                            type="date" value="{{ $sub->start_date }}"
                                                            id="start_date-{{ $index }}" />
                                                    </div>
                                                    <div class="col-md-3   mb-5" id="end_date_dev-{{ $index }}"
                                                        style="display: block">
                                                        <label
                                                            class="required fs-6 fw-semibold mb-2">{{ trans('members.subscription_end_date') }}</label>
                                                        <input class="form-control form-control-solid" name="end_date[]"
                                                            type="date" value="{{ $sub->end_date }}"
                                                            id="end_date-{{ $index }}" readonly />
                                                    </div>
                                                    <div class="col-md-3   mb-5">
                                                        <label
                                                            class="custom-label fs-6 fw-semibold mb-2">{{ trans('members.transportation') }}</label>
                                                        <div
                                                            class="form-check form-switch form-check-custom form-check-solid">
                                                            <input class="form-check-input" name="transportation[]"
                                                                type="checkbox" value="{{ $sub->transport }}"
                                                                id="flexSwitchDefault-{{ $index }}"
                                                                {{ $sub->transport == 'yes' ? 'checked' : '' }} />
                                                            <label class="form-check-label"
                                                                for="flexSwitchDefault-{{ $index }}">
                                                                {{ trans('members.transportation_sub') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-top: 10px">
                                                    <div class="col-md-3   mb-5" id="trainer_dev-{{ $index }}"
                                                        style="{{ $sub->type == 'special' ? 'display: block' : 'display: none' }}">
                                                        <label
                                                            class="required fs-6 fw-semibold mb-2">{{ trans('members.trainers') }}</label>
                                                        <select class="form-control form-control-solid"
                                                            data-control="select2" data-hide-search="true"
                                                            name="trainer_id[]" id="trainer_id-{{ $index }}">
                                                            <option>{{ trans('forms.select') }}</option>
                                                            @foreach ($trainers as $trainer)
                                                                <option value="{{ $trainer->id }}"
                                                                    {{ $trainer->id == $sub->trainer_id ? 'selected' : '' }}>
                                                                    {{ $trainer->user_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label
                                                            class="required fs-6 fw-semibold mb-2">{{ trans('members.discount') }}
                                                            (<span style="color: darkred"
                                                                id="max_discount-{{ $index }}">{{ $sub->max_discount }}</span>)</label>
                                                        <input onkeyup="checkMaxDiscount(this)"
                                                            class="form-control form-control-solid" name="discount[]"
                                                            type="number" step="any" value="{{ $sub->discount }}"
                                                            id="discount-{{ $index }}" />
                                                    </div>
                                                    <input type="hidden" id="max_sub_dicount-{{ $index }}"
                                                        value="{{ $sub->max_discount }}">
                                                    <input type="hidden" name="duration[]"
                                                        id="duration-{{ $index }}" value="">
                                                </div>
                                                <div class="d-flex align-items-center gap-2 gap-lg-3">
                                                    <div class="d-flex">
                                                        <a onclick="delete_sub_row({{ $sub->id }})"
                                                            class="btn btn-sm btn-icon btn-light-danger mt-3 mt-md-9 flex-shrink-0 ms-4">
                                                            <i class="fas fa-trash"></i>

                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3   mb-5">
                                            <div class="row">
                                                <div class="flex-column flex-lg-row-auto">
                                                    <!--begin::Card-->
                                                    <div class="card mb-5 mb-xl-8 border border-primary">
                                                        <!--begin::Card body-->
                                                        <div class="card-body pt-15">
                                                            <!--begin::Summary-->
                                                            <div class="d-flex flex-center flex-column mb-5">
                                                                <span id="sub_name-{{ $index }}"
                                                                    class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1"></span>
                                                                <!--end::Name-->
                                                                <!--begin::Info-->
                                                            </div>
                                                            <!--end::Details toggle-->
                                                            <div class="separator separator-dashed my-3"></div>
                                                            <!--begin::Details content-->
                                                            <div id="kt_customer_view_details-{{ $index }}"
                                                                class="collapse show">
                                                                <div class="py-5 fs-6">
                                                                    <div class="d-flex align-items-center mt-5">
                                                                        <div class="fw-bold">{{ trans('sub.duration') }}
                                                                            :
                                                                        </div>
                                                                        <span id="sub_duration-{{ $index }}"
                                                                            class="text-gray-600 ms-2">{{ $sub->duration }}</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-center mt-5">
                                                                        <div class="fw-bold">{{ trans('sub.price') }}:
                                                                        </div>
                                                                        <span id="sub_price-{{ $index }}"
                                                                            class="text-gray-600 ms-2">{{ $sub->price }}</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-center mt-5">
                                                                        <div class="fw-bold">
                                                                            {{ trans('sub.max_discount') }}
                                                                            (%):
                                                                        </div>
                                                                        <span id="sub_max_discount-{{ $index }}"
                                                                            class="text-gray-600 ms-2">{{ $sub->max_discount }}</span>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--end::Card-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <hr>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>


                        {{-- start form repeated --}}


                        <div id="kt_docs_repeater_advanced">
                            <!--begin::Form group-->
                            <div class="form-group">
                                <div data-repeater-list="kt_docs_repeater_advanced">
                                    <div data-repeater-item>
                                        <div class="row ">
                                            <div class="col-md-9">
                                                <div class="form-group row mb-5">
                                                    <div class="row" style="margin-top: 10px">
                                                        <div class="col-md-3   mb-5">
                                                            <label
                                                                class="form-label">{{ trans('members.category') }}</label>
                                                            <select onchange="get_subscription2(this)"
                                                                class="form-control form-control-solid type-select"
                                                                name="type" id="type">
                                                                <option>{{ trans('forms.select') }}</option>
                                                                @php $cat_arr=['main'=>trans('members.main_subscription'),'special'=>trans('members.special_subscription')] @endphp
                                                                @foreach ($cat_arr as $key => $value)
                                                                    <option value="{{ $key }}">
                                                                        {{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3   mb-5">
                                                            <label
                                                                class="required fs-6 fw-semibold mb-2">{{ trans('members.subscription') }}</label>
                                                            <select onchange="get_sub_details2(this)"
                                                                class="form-control form-control-solid subscription-select"
                                                                name="subscription_id" id="subscription_id">
                                                                <option>{{ trans('forms.select') }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 ">
                                                            <label
                                                                class="required fs-6 fw-semibold mb-2">{{ trans('members.subscription_start_date') }}</label>
                                                            <input onchange="get_sub_details2(this)"
                                                                class="form-control form-control-solid" name="start_date"
                                                                type="date" value="{{ date('Y-m-d') }}"
                                                                id="start_date" />

                                                        </div>
                                                        <div class="col-md-3 " id="end_date_dev" style="display: block">
                                                            <label
                                                                class="required fs-6 fw-semibold mb-2">{{ trans('members.subscription_end_date') }}</label>
                                                            <input class="form-control form-control-solid" name="end_date"
                                                                type="date" value="" id="end_date" readonly />

                                                        </div>
                                                        <div class="col-md-3 ">
                                                            <label
                                                                class="custom-label fs-6 fw-semibold mb-2">{{ trans('members.transportation') }}</label>

                                                            <div
                                                                class="form-check form-switch form-check-custom form-check-solid">
                                                                <!-- Hidden input to ensure unchecked state is sent -->
                                                                <input type="hidden" name="transportation"
                                                                    value="no">
                                                                <input class="form-check-input" name="transportation"
                                                                    type="checkbox" value="yes"
                                                                    id="flexSwitchDefault" />
                                                                <label class="form-check-label" for="flexSwitchDefault">
                                                                    {{ trans('members.transportation_sub') }}
                                                                </label>
                                                            </div>
                                                        </div>


                                                    </div>

                                                    <div class="row" style="margin-top: 10px">
                                                        <div class="col-md-3   mb-5" id="trainer_dev"
                                                            style="display: none">
                                                            <label
                                                                class="required fs-6 fw-semibold mb-2">{{ trans('members.trainers') }}</label>
                                                            <select class="form-control form-control-solid"
                                                                name="trainer_id" id="trainer_id">
                                                                <option>{{ trans('forms.select') }}</option>

                                                                @foreach ($trainers as $key)
                                                                    <option value="{{ $key->id }}">
                                                                        {{ $key->user_name }}</option>
                                                                @endforeach

                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label
                                                                class="required fs-6 fw-semibold mb-2">{{ trans('members.discount') }}
                                                                (<span style="color: darkred"
                                                                    id="max_discount"></span>)</label>
                                                            <input onkeyup="checkMaxDiscount(this)"
                                                                class="form-control form-control-solid" name="discount"
                                                                type="number" step="any" value=""
                                                                id="discount" />
                                                        </div>
                                                        <input type="hidden" id="max_sub_dicount" value="">


                                                        <div class="col-md-3   mb-5">
                                                            <label
                                                                class="required fs-6 fw-semibold mb-2">{{ trans('members.pay_method') }}</label>
                                                            <select onchange="pay_type2(this)"
                                                                class="form-control form-control-solid pay-method-select"
                                                                name="pay_method" id="pay_method">
                                                                <?php $pay_method_arr = ['cache' => trans('members.cache'), 'visa' => trans('members.visa'), 'bank' => trans('members.bank')]; ?>
                                                                <option>{{ trans('forms.select') }}</option>
                                                                @foreach ($pay_method_arr as $key => $value)
                                                                    <option value="{{ $key }}">
                                                                        {{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3   mb-5 transfer-image-dev"
                                                            style="display: none">
                                                            <label
                                                                class="required fs-6 fw-semibold mb-2">{{ trans('members.transfer_image') }}</label>
                                                            <input class="form-control form-control-solid" type="file"
                                                                name="transfer_image" id="transfer_image"
                                                                accept="image/*">
                                                        </div>

                                                        <input type="hidden" name="duration" id="duration">
                                                    </div>


                                                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                                                        <div class="d-flex">
                                                            <a href="javascript:;" data-repeater-delete
                                                                class="btn btn-sm btn-danger mt-3 mt-md-9 flex-shrink-0 ms-4">

                                                                <span class="svg-icon svg-icon-2">
                                                                    <svg width="24" height="24"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        viewBox="0 0 448 512">
                                                                        <path
                                                                            d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.7 23.7 0 0 0 -21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0 -16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z" />
                                                                    </svg>

                                                                </span>


                                                            </a>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="col-md-3   mb-5">
                                                <div class="row">
                                                    <div class="flex-column flex-lg-row-auto ">
                                                        <!--begin::Card-->
                                                        <div class="card mb-5 mb-xl-8 border border-primary">
                                                            <!--begin::Card body-->
                                                            <div class="card-body pt-15">
                                                                <!--begin::Summary-->
                                                                <div class="d-flex flex-center flex-column mb-5">
                                                                    <span id="sub_name"
                                                                        class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">

                                                                    </span>
                                                                    <!--end::Name-->
                                                                    <!--begin::Info-->
                                                                </div>
                                                                <!--end::Details toggle-->
                                                                <div class="separator separator-dashed my-3"></div>
                                                                <!--begin::Details content-->
                                                                <div id="kt_customer_view_details" class="collapse show">
                                                                    <div class="py-5 fs-6">
                                                                        <div class="d-flex align-items-center mt-5">
                                                                            <div class="fw-bold">
                                                                                {{ trans('sub.duration') }}
                                                                                :
                                                                            </div>
                                                                            <span id="sub_duration"
                                                                                class="text-gray-600 ms-2"></span>
                                                                        </div>
                                                                        <div class="d-flex align-items-center mt-5">
                                                                            <div class="fw-bold">{{ trans('sub.price') }}
                                                                                :
                                                                            </div>
                                                                            <span id="sub_price"
                                                                                class="text-gray-600 ms-2"></span>
                                                                        </div>
                                                                        <div class="d-flex align-items-center mt-5">
                                                                            <div class="fw-bold">
                                                                                {{ trans('sub.max_discount') }}
                                                                                (%):
                                                                            </div>
                                                                            <span id="sub_discount"
                                                                                class="text-gray-600 ms-2"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!--end::Details content-->
                                                            </div>
                                                            <!--end::Card body-->
                                                        </div>

                                                        <!--end::Card-->

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        <br>
                                        <hr>
                                    </div>

                                </div>
                            </div>
                            <!--end::Form group-->
                            <!--begin::Form group-->

                            <div class="d-flex align-items-center gap-2 gap-lg-3">
                                <div class="d-flex">
                                    <a data-repeater-create class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4">
                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                                        <span class="svg-icon svg-icon-2">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                                                    rx="1" transform="rotate(-90 11.364 20.364)"
                                                    fill="currentColor" />
                                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                                    fill="currentColor" />
                                            </svg>
                                        </span>

                                    </a>
                                </div>


                            </div>

                        </div>
                        {{-- end form repeated --}}


                        <div class="col-md-12">
                            <div class="form-group text-end" style="margin-top: 27px;">
                                <button type="submit" name="btnSave" value="btnSave" id="btnSave"
                                    class="btn btn-success btn-flat ">
                                    <i class="bi bi-save"></i> {{ trans('sub.save') }}
                                </button>
                            </div>
                        </div>

                    </div>


                </form>

            </div>


        </div>
    </div>










@stop
@section('js')




    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest(
        'App\Http\Requests\Admin\subscription\member_subscriptions\SaveMemberSubscriptions',
        '#save_form',
    ) !!}
    <script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script> !!}
    <script src="{{ asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
    <script>
        var KTAppBlogSave = function() {
            const initTagify = () => {
                // The DOM elements you wish to replace with Tagify
                var input1 = document.querySelector("#details_tag_ar");
                var input2 = document.querySelector("#details_tag_en");

                // Initialize Tagify components on the above inputs
                new Tagify(input1);
                new Tagify(input2);

            };
            const initckeditor = () => {

                const elements_en = [
                    '#details_en'
                ];
                const elements_ar = [
                    '#details_ar'
                ];

                // Loop all elements
                elements_en.forEach((element, index) => {
                    // Get quill element
                    let ckeditor = document.querySelector(element);

                    // Break if element not found
                    if (!ckeditor) {
                        return;
                    }

                    // Init quill --- more info: https://quilljs.com/docs/quickstart/
                    ClassicEditor
                        .create(ckeditor, {
                            toolbar: {
                                items: [
                                    'undo', 'redo',
                                    '|', 'heading',
                                    '|', 'bold', 'italic',
                                    '|', 'bulletedList', 'numberedList', 'outdent', 'indent'
                                ]
                            },
                            heading: {
                                options: [{
                                        model: 'paragraph',
                                        title: 'Paragraph',
                                        class: 'ck-heading_paragraph'
                                    },
                                    {
                                        model: 'heading1',
                                        view: 'h1',
                                        title: 'Heading 1',
                                        class: 'ck-heading_heading1'
                                    },
                                    {
                                        model: 'heading2',
                                        view: 'h2',
                                        title: 'Heading 2',
                                        class: 'ck-heading_heading2'
                                    },
                                    {
                                        model: 'heading3',
                                        view: 'h3',
                                        title: 'Heading 3',
                                        class: 'ck-heading_heading3'
                                    }
                                ]
                            },
                            language: 'en'
                        })
                        .then(editor => {
                            console.log(editor);
                        })
                        .catch(error => {
                            console.error(error);
                        });


                });
                // Loop all elements
                elements_ar.forEach((element, index) => {
                    // Get quill element
                    let ckeditor = document.querySelector(element);

                    // Break if element not found
                    if (!ckeditor) {
                        return;
                    }

                    // Init quill --- more info: https://quilljs.com/docs/quickstart/
                    ClassicEditor
                        .create(ckeditor, {
                            toolbar: {
                                items: [
                                    'undo', 'redo',
                                    '|', 'heading',
                                    '|', 'bold', 'italic',
                                    '|', 'bulletedList', 'numberedList', 'outdent', 'indent'
                                ]
                            },
                            heading: {
                                options: [{
                                        model: 'paragraph',
                                        title: 'Paragraph',
                                        class: 'ck-heading_paragraph'
                                    },
                                    {
                                        model: 'heading1',
                                        view: 'h1',
                                        title: 'Heading 1',
                                        class: 'ck-heading_heading1'
                                    },
                                    {
                                        model: 'heading2',
                                        view: 'h2',
                                        title: 'Heading 2',
                                        class: 'ck-heading_heading2'
                                    },
                                    {
                                        model: 'heading3',
                                        view: 'h3',
                                        title: 'Heading 3',
                                        class: 'ck-heading_heading3'
                                    }
                                ]
                            },
                            language: 'ar'
                        })
                        .then(editor => {
                            console.log(editor);
                        })
                        .catch(error => {
                            console.error(error);
                        });


                });

            }

            // Public methods
            return {
                init: function() {
                    // Init forms
                    initckeditor();
                    initTagify();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppBlogSave.init();
            setTimeout(function() {
                $('[id^=type-]').each(function() {
                    $(this).trigger('change');
                });
                $('[id^=pay_method-]').each(function() {
                    $(this).trigger('change');
                });
                $('[id^=subscription_id]').each(function() {
                    $(this).trigger('change');
                });
            }, 300);
        });
    </script>


    <script>
        function get_subscription(selectElement, index) {
            var type = selectElement.value;
            var subscriptionSelect = document.querySelector(`#subscription_id-${index}`);
            var subscription_id = subscriptionSelect.getAttribute('data-value-' + index);

            console.log('Selected type:', type);
            console.log('Subscription ID:', subscription_id);
            $.ajax({
                url: '{{ route('admin.get-subscription') }}',
                type: 'get',
                data: {
                    type: type,
                },
                success: function(response) {
                    $('#subscription_id-' + index).empty();
                    $('#subscription_id-' + index).append('<option>{{ trans('forms.select') }}</option>');
                    var currentLocale = '{{ app()->getLocale() }}';
                    response.forEach(function(subscription) {
                        var name = subscription.name[
                            currentLocale]; // Access the translation for the current locale
                        $('#subscription_id-' + index).append('<option value="' + subscription.id +
                            '">' + name + '</option>');
                    });
                    $('#subscription_id-' + index).val(subscription_id);

                    if (type == 'special') {
                        $('#trainer_dev-' + index).show();
                        $('#end_date_dev-' + index).hide();
                    } else {
                        $('#trainer_dev-' + index).hide();
                        $('#end_date_dev-' + index).show();
                    }
                },
                error: function(xhr, status, error) {
                    // Handle any errors here
                    console.error(error);
                }
            });
        }

        function get_sub_details(index) {
            var type = $('#type-' + index).val();

            var subscriptionSelect = document.querySelector(`#subscription_id-${index}`);
            var subscription_id = subscriptionSelect.getAttribute('data-value-' + index);
            var start_date = $('#start_date-' + index).val();
            $.ajax({
                url: '{{ route('admin.get-subscription-details') }}',
                type: 'get',
                data: {
                    type: type,
                    id: subscription_id,
                    start_date: start_date,
                },
                success: function(response) {
                    if (type == 'main') {
                        $('#end_date-' + index).val(response.end_date);
                    }
                    $('#sub_duration-' + index).text(response.subscription.duration);
                    $('#duration-' + index).val(response.subscription.duration);
                    var local = '{{ \Illuminate\Support\Facades\App::getLocale() }}';
                    console.log(index);
                    $('#sub_name-' + index).text(response.subscription.name[local]);
                    $('#sub_max_discount-' + index).text(response.subscription.max_discount);
                    $('#sub_price-' + index).text(response.subscription.price);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function pay_type(selectElement, index) {
            var id = selectElement.value;
            if (id == 'bank') {
                $('#transfer_image_dev-' + index).show();
            } else {
                $('#transfer_image_dev-' + index).hide();
            }
        }
    </script>

    <script>
        function delete_sub_row(id) {
            Swal.fire({
                text: "{{ trans('forms.delete_quetion') }}?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "{{ trans('forms.delete_btn') }}",
                cancelButtonText: "{{ trans('forms.action_no') }}",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function(result) {
                if (result.value) {
                    Swal.fire({
                        imageUrl: 'https://media.tenor.com/C7KormPGIwQAAAAi/epic-loading.gif',
                        imageWidth: 200,
                        imageHeight: 200,
                        buttonsStyling: false,
                        showConfirmButton: false,
                        timer: 2000,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(function() {
                        // Perform deletion
                        $.ajax({
                            url: '{{ route('admin.subscriptions.delete-subscription') }}', // Use the correct route for deletion
                            type: 'get', // Use DELETE method for deleting resources
                            data: {
                                id: id,
                                _token: '{{ csrf_token() }}' // Add CSRF token for security
                            },
                            success: function(response) {
                                Swal.fire({
                                    text: "{{ trans('forms.delete_success') }}", // Success message
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "{{ trans('forms.action_done') }}",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function() {
                                    // Optionally, reload or update the page to reflect changes
                                    location.reload(); // Refresh the page
                                });
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    text: "{{ trans('forms.delete_error') }}", // Error message
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "{{ trans('forms.action_done') }}",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        });
                    });
                } else if (result.dismiss === 'cancel') {
                    Swal.fire({
                        text: "{{ trans('forms.cancelled') }}", // Cancelled message
                        icon: "info",
                        buttonsStyling: false,
                        confirmButtonText: "{{ trans('forms.action_done') }}",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                }
            });
        }
    </script>



    <script>
        function get_subscription2(element) {
            var $repeaterItem = $(element).closest('[data-repeater-item]');
            var type = $(element).val();
            var $subscriptionSelect = $repeaterItem.find('.subscription-select');
            var subscription_id = ' '; // Placeholder, adjust as needed

            console.log('subscription_id' + subscription_id);
            $.ajax({
                url: '{{ route('admin.get-subscription') }}',
                type: 'get',
                data: {
                    type: type,
                },
                success: function(response) {
                    $subscriptionSelect.empty();
                    $subscriptionSelect.append('<option>{{ trans('forms.select') }}</option>');
                    var currentLocale = '{{ app()->getLocale() }}';
                    response.forEach(function(subscription) {
                        var name = subscription.name[
                            currentLocale]; // Access the translation for the current locale
                        $subscriptionSelect.append('<option value="' + subscription.id + '">' + name +
                            '</option>');
                        if (subscription_id != ' ') {
                            $subscriptionSelect.append('<option value="' + subscription.id + '">' +
                                name + '</option>');
                            $subscriptionSelect.val(subscription_id);
                        }
                    });

                    if (type == 'special') {
                        $repeaterItem.find('#trainer_dev').show();
                        $repeaterItem.find('#end_date_dev').hide();
                    } else {
                        $repeaterItem.find('#trainer_dev').hide();
                        $repeaterItem.find('#end_date_dev').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function get_sub_details2(element) {
            var $repeaterItem = $(element).closest('[data-repeater-item]');
            var type = $repeaterItem.find('#type').val();
            var subscription_id = $repeaterItem.find('#subscription_id').val();
            var start_date = $repeaterItem.find('#start_date').val();

            $.ajax({
                url: '{{ route('admin.get-subscription-details') }}',
                type: 'get',
                data: {
                    type: type,
                    id: subscription_id,
                    start_date: start_date,
                },
                success: function(response) {
                    console.log(response.end_date);
                    if (type == 'main') {
                        $repeaterItem.find('#end_date').val(response.end_date);
                    }
                    $repeaterItem.find('#duration').val(response.subscription.duration);
                    $repeaterItem.find('#sub_duration').text(response.subscription.duration);
                    $repeaterItem.find('#sub_price').text(response.subscription.price);
                    $repeaterItem.find('#sub_discount').text(response.subscription.max_discount);
                    $repeaterItem.find('#max_sub_dicount').text(response.subscription.max_discount);
                    var local = '{{ \Illuminate\Support\Facades\App::getLocale() }}';
                    $repeaterItem.find('#sub_name').text(response.subscription.name[local]);
                    $repeaterItem.find('#max_discount').text('{{ trans('members.max_discount') }}' + ' ' +
                        response.subscription.max_discount + ' % ');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }


        function pay_type2(element) {
            var $repeaterItem = $(element).closest('[data-repeater-item]');
            var id = $(element).val();
            var $transferImageDev = $repeaterItem.find('.transfer-image-dev');

            if (id === 'bank') {
                $transferImageDev.show();
            } else {
                $transferImageDev.hide();
            }
        }
    </script>

    <script>
        function checkMaxDiscount(input) {
            var $repeaterItem = $(input).closest('[data-repeater-item]');
            var discount = parseFloat(input.value);
            var maxDiscount = parseFloat($repeaterItem.find('#max_discount').val());
            var $errorMessage = $repeaterItem.find('.error-message');

            if (isNaN(discount) || discount < 0) {
                $errorMessage.hide();
                return;
            }

            if (discount > maxDiscount) {
                $errorMessage.text(`Discount cannot be greater than ${maxDiscount}.`).show();
                input.value = ''; // Clear the input field
                $(input).css('border-color', 'red');
            } else {
                $errorMessage.hide();
                $(input).css('border-color', '');
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            // Initialize Repeater
            $('#kt_docs_repeater_advanced').repeater({
                initEmpty: true, // Change this to true
                defaultValues: {
                    'text-input': 'foo'
                },
                show: function() {
                    $(this).slideDown();
                },
                hide: function(deleteElement) {
                    $(this).slideUp(deleteElement);
                },
                ready: function() {
                    // Initialize select2 or other plugins here
                }
            });

            // Event delegation for dynamically created elements


        });
    </script>



@endsection
