@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('tax_setting.create')}}</h1>
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
                    {{trans('Toolbar.finance')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.Create_tax_setting')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>


    </div>
    <!--end::Toolbar container-->

@endsection
@section('content')



    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="t_container">
            <div class="card shadow-sm ">
                <div class="card-header">
                    <h3 class="card-title"></i> {{trans('sub.tax_setting')}}</h3>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>

                    @endif
                </div>


                <form id="StorForm" method="post"
                      action="{{route('admin.finance.tax_setting.store')}}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">

                        <div class="row">

                            <div class="mb-10 col  fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('tax_setting.account')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('account_id') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                        name="account_id" id="account_id">
                                    <option>- {{trans('forms.select')}} -</option>
                                    @foreach($account_id as $row)
                                        <option value="{{ $row->id }}">{{ $row->name}}</option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!----------------------------------------------------------------->
                            <div class="mb-10 col  fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('tax_setting.type')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('type') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                        name="type" id="type">
                                        <?php
                                        $select_array = array('with'=>trans('forms.with'), 'without'=>trans('forms.without'))
                                        ?>
                                       <option></option>
                                        @foreach($select_array as $key=> $value)
                                        <option value="{{ $key }}"
                                        {{ old('with', $one_data->status) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                       </option>
                                        @endforeach
                                </select>
                                @error('type')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--------------------------------------------------------------------------------------->
                        <div class="row" style="margin-top: 10px">
                            <?php  $name = $one_data->getTranslations('name'); ?>
                            <div class="mb-10 col  fv-row col">
                                <label class="form-label">{{trans('tax_setting.name')}} (<span
                                        class="text-muted">{{trans('forms.lable_en')}}</span>)</label>
                                <input type="text" name="name_en" id="name_en" class="form-control mb-2"
                                       placeholder="{{trans('tax_setting.name')}}"
                                       value="{{old('name_en')}}" required autocomplete/>
                            </div>

                            <!----------------------------------------------------------------------------------->


                            <div class="mb-10 col  fv-row col">
                                <label class="form-label">{{trans('tax_setting.name')}} (<span
                                        class="text-muted">{{trans('forms.lable_ar')}}</span>)</label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control mb-2"
                                       placeholder="{{trans('tax_setting.name')}}"
                                       value="{{old('name_ar')}}" required autocomplete/>
                            </div>

                        </div>
                        <!--------------------------------------------------------------------------->
                        <div class="row" style="margin-top: 10px">
                            <div class="mb-10 col  fv-row col">
                                <label class="form-label">{{trans('tax_setting.percentage')}} (<span
                                        class="text-muted">{{trans('forms.lable_en')}}</span>)</label>
                                <input type="number" name="percentage" id="percentage" class="form-control mb-2"
                                       min=0 max=100 placeholder="0"
                                       value="{{old('percentage')}}" required autocomplete/>
                            </div>
                            <div class="mb-10 col  fv-row col">
                                <label class="required form-label">{{ trans('tax_setting.Status') }}</label>
                                <select class="form-select" data-control="select2" data-placeholder="Select an option"
                                        name="status" id="status">
                                        <?php
                                        $select_array = array('active'=>trans('forms.active'), 'notactive'=>trans('forms.not-active'))
                                        ?>
                                        <option></option>
                                        @foreach($select_array as $key=> $value)
                                        <option value="{{ $key }}"
                                        {{ old('status', $one_data->status) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                      </option>
                                     @endforeach
                                </select>
                            </div>


                        </div>


                        <div class="d-flex justify-content-end">

                            <button type="reset" class="btn btn-light me-5">{{trans('forms.cancel_btn')}}</button>

                            <button type="submit" id="" class="btn btn-primary">
                                <span class="indicator-label">{{trans('forms.save_btn')}}</span>
                                <span class="indicator-progress">Please wait...
						<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>

                        </div>

                    </div>


                </form>

            </div>


        </div>
    </div>










@stop
@section('js')




    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\finance\tax_setting\TaxRequest', '#StorForm') !!}

    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>


@endsection
