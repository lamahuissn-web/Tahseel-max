@extends('dashbord.layouts.master')
@section('toolbar')
      <!--begin::Toolbar container-->
      <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('account_setting.create')}}</h1>
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
                    {{trans('Toolbar.Create_account_setting')}}
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
                    <h3 class="card-title"></i> {{trans('sub.account_setting')}}</h3>
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
                      action="{{route('admin.finance.account_setting.store')}}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">

                        <div class="row">

                            <div class="mb-10 col  fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account_setting.clients_account')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('clients_account') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                      name="clients_account"  id="clients_account">
                                    <option value=" ">- {{trans('forms.select')}} -</option>
                                    @foreach($accounts as $row)
                                        <option
                                            value="{{ $row->id }}" @if(optional($one_data)->clients_account==$row->id)
                                            {{'selected'}}
                                            @endif >{{ $row->name}}</option>
                                 @endforeach
                                </select>
                                @error('clients_account')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!----------------------------------------------------------------->
                            <div class="mb-10 col  fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account_setting.supplier_account')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('supplier_account') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                      name="supplier_account"  id="supplier_account">
                                    <option value=" ">- {{trans('forms.select')}} -</option>
                                    @foreach($accounts as $row)
                                        <option
                                            value="{{ $row->id }}" @if(optional($one_data)->supplier_account==$row->id)
                                            {{'selected'}}
                                            @endif >{{ $row->name}}</option>
                                 @endforeach
                                </select>
                                @error('supplier_account')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!----------------------------------------------------------------->
                            <div class="mb-10 col  fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account_setting.bonuses_account')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('bonuses_account') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                      name="bonuses_account"  id="bonuses_account">
                                    <option value=" ">- {{trans('forms.select')}} -</option>
                                    @foreach($accounts as $row)
                                        <option
                                            value="{{ $row->id }}" @if(optional($one_data)->bonuses_account==$row->id)
                                            {{'selected'}}
                                            @endif >{{ $row->name}}</option>
                                 @endforeach
                                </select>
                                @error('bonuses_account')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                         <!---*************************************************************************************-->
                        <div class="row" style="margin-top: 10px">
                            <div class="mb-10 col  fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account_setting.deductions_account')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('deductions_account') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                      name="deductions_account"  id="deductions_account">
                                    <option value=" ">- {{trans('forms.select')}} -</option>
                                    @foreach($accounts as $row)
                                        <option
                                            value="{{ $row->id }}" @if(optional($one_data)->deductions_account==$row->id)
                                            {{'selected'}}
                                            @endif >{{ $row->name}}</option>
                                 @endforeach
                                </select>
                                @error('deductions_account')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                          <!----------------------------------------------------------------->
                            <div class="mb-10 col  fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account_setting.loan_account')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('loan_account') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                      name="loan_account"  id="loan_account">
                                    <option value=" ">- {{trans('forms.select')}} -</option>
                                    @foreach($accounts as $row)
                                        <option value="{{ $row->id }}" @if(optional($one_data)->loan_account==$row->id)
                                            {{'selected'}}
                                            @endif >{{ $row->name}}</option>
                                 @endforeach
                                </select>
                                @error('loan_account')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!----------------------------------------------------------------->
                            <div class="mb-10 col  fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('account_setting.subscriptions_account')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('subscriptions_account') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                      name="subscriptions_account"  id="subscriptions_account">
                                    <option value=" ">- {{trans('forms.select')}} -</option>
                                    @foreach($accounts as $row)
                                        <option
                                            value="{{ $row->id }}" @if(optional($one_data)->subscriptions_account==$row->id)
                                            {{'selected'}}
                                            @endif >{{ $row->name}}</option>
                                 @endforeach
                                </select>
                                @error('subscriptions_account')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
<!--------------------------------------------------------------------------->
            <div class="row" style="">

                <div class="mb-10 col  fv-row col">
                    <!--begin::Label-->
                    <label class="required form-label">{{trans('account_setting.allowed_deduction_account')}}</label>
                    <!--end::Label-->
                    <select class="form-select mb-2 @error('allowed_deduction_account') is-invalid @enderror"
                            data-control="select2" data-hide-search="false"
                            data-placeholder="Select an option"
                          name="allowed_deduction_account"  id="allowed_deduction_account">
                        <option value=" ">- {{trans('forms.select')}} -</option>
                        @foreach($accounts as $row)
                            <option value="{{ $row->id }}" @if(optional($one_data)->allowed_deduction_account==$row->id)
                                {{'selected'}}
                                @endif >{{ $row->name}}</option>
                     @endforeach
                    </select>
                    @error('allowed_deduction_account')
                    <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-10 col  fv-row col">
                    <!--begin::Label-->
                    <label class="required form-label">{{trans('account_setting.earned_deduction_account')}}</label>
                    <!--end::Label-->
                    <select class="form-select mb-2 @error('earned_deduction_account') is-invalid @enderror"
                            data-control="select2" data-hide-search="false"
                            data-placeholder="Select an option"
                          name="earned_deduction_account"  id="earned_deduction_account">
                        <option value=" ">- {{trans('forms.select')}} -</option>
                        @foreach($accounts as $row)
                            <option value="{{ $row->id }}" @if(optional($one_data)->earned_deduction_account==$row->id)
                                {{'selected'}}
                                @endif >{{ $row->name}}</option>
                     @endforeach
                    </select>
                    @error('earned_deduction_account')
                    <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-10 col  fv-row col">
                    <!--begin::Label-->
                    <label class="required form-label">{{trans('account_setting.salaries_account')}}</label>
                    <!--end::Label-->
                    <select class="form-select mb-2 @error('salaries_account') is-invalid @enderror"
                            data-control="select2" data-hide-search="false"
                            data-placeholder="Select an option"
                          name="salaries_account"  id="salaries_account">
                        <option value=" ">- {{trans('forms.select')}} -</option>
                        @foreach($accounts as $row)
                            <option value="{{ $row->id }}" @if(optional($one_data)->salaries_account==$row->id)
                                {{'selected'}}
                                @endif >{{ $row->name}}</option>
                     @endforeach
                    </select>
                    @error('salaries_account')
                    <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                    @enderror
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
  {{--  {!! JsValidator::formRequest('App\Http\Requests\Subscriptions\account_setting\StoreRequest', '##StorForm') !!}
--}}
    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>

@endsection
