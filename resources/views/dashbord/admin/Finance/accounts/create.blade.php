@extends('dashbord.layouts.master')

@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('account.create')}}</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">

                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                        {{trans('Toolbar.home')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.finance')}}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.finance.accounts.index') }}"
                       class="text-muted text-hover-primary"> {{trans('Toolbar.account')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.accountCreate')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.finance.accounts.index')}}"
                   class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4">

                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/arrows/arr054.svg-->
                    <span class="svg-icon svg-icon-2">
                                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                       <path
                                           d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z"
                                           fill="currentColor"/>
                                   </svg>
                                </span>
                    <!--end::Svg Icon-->
                </a>
            </div>
            <!--end::Filter menu-->
            <!--begin::Secondary button-->
            <!--end::Secondary button-->
            <!--begin::Primary button-->
            <!--end::Primary button-->
        </div>
        <!--end::Actions-->

    </div>
    <!--end::Toolbar container-->
@endsection

@section('content')

    <div id="kt_app_content_container" class="app-container container-xxxl">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form id="StorForm" class="form d-flex flex-column flex-lg-row "
              action="{{route('admin.finance.accounts.store')}}" method="post" enctype="multipart/form-data">
        @csrf

        <!--begin::Main column-->
            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <!--begin::General options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('account.mainData')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row row">
                            <div class="col-md-6">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account.name')}}
                                    <span class="text-muted fs-7">"{{trans('forms.lable_en')}}"</span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="name[en]"
                                       class="form-control mb-2  @error('name[en]') is-invalid @enderror"
                                       placeholder="{{trans('account.name')}}" value="{{old('name[en]')}}"/>
                                <!--end::Input-->
                                @error('name[en]')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account.name')}}
                                    <span class="text-muted fs-7">"{{trans('forms.lable_ar')}}"</span>

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="name[ar]"
                                       class="form-control mb-2  @error('name[ar]') is-invalid @enderror"
                                       placeholder="{{trans('account.name')}}" value="{{old('name[ar]')}}"/>
                                <!--end::Input-->
                                @error('name[ar]')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-10 fv-row row">
                            <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account.code')}}
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="code"
                                       class="form-control mb-2  @error('code') is-invalid @enderror"
                                       placeholder="{{trans('account.code')}}" value="{{old('code')}}"/>
                                <!--end::Input-->
                                @error('code')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{--
                                                        <div class="col-md-4">
                                                            <!--begin::Label-->
                                                            <label class="required form-label">{{trans('account.account_num')}}

                                                            </label>
                                                            <!--end::Label-->
                                                            <!--begin::Input-->
                                                            <input type="text" name="account_num"
                                                                   class="form-control mb-2  @error('account_num') is-invalid @enderror"
                                                                   placeholder="{{trans('account.account_num')}}" value="{{old('account_num')}}"/>
                                                            <!--end::Input-->
                                                            @error('account_num')
                                                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                            --}}
                            <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account.account_type')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="account_type"
                                        class="form-control mb-2  @error('account_type') is-invalid @enderror"
                                        data-control="select2" data-placeholder="{{trans('forms.Select')}}">
                                    <option value="0"> {{trans('account.account_type')}}</option>
                                    @foreach($accounts_type as $account_type)
                                        <option value="{{$account_type->id}}">{{$account_type->name}}</option>
                                    @endforeach
                                </select>
                                <!--end::Input-->
                                @error('account_type')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account.parent_id')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="parent_id" class="form-control mb-2  @error('parent_id') is-invalid @enderror"
                                        data-control="select2" data-placeholder="{{trans('forms.Select')}}">
                                    <option value="0"> {{trans('account.parent')}}</option>
                                    @foreach($accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}}</option>
                                    @endforeach
                                </select>
                                <!--end::Input-->
                                @error('parent_id')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <!--end::Card header-->
                </div>
                <!--end::General options-->


                <div class="d-flex justify-content-end">
                    <!--begin::Button-->
                    <button type="reset" class="btn btn-light me-5">{{trans('forms.cancel_btn')}}</button>
                    <!--end::Button-->
                    <!--begin::Button-->
                    <button type="submit" id="" class="btn btn-primary">
                        <span class="indicator-label">{{trans('forms.save_btn')}}</span>
                        <span class="indicator-progress">Please wait...
													<span
                                                        class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                    <!--end::Button-->
                </div>
            </div>
            <!--end::Main column-->
        </form>
    </div>

@endsection
@section('js')
    <!--begin::Vendors Javascript(used for this page only)-->



    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\finance\accounts\StoreRequest', '#StorForm') !!}
    <script>
        var KTAppaccountSave = function () {


            // Public methods
            return {
                init: function () {
                    // Init forms
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppaccountSave.init();
        });
    </script>
@endsection

