@extends('dashbord.layouts.master')
@section('toolbar')
      <!--begin::Toolbar container-->
      <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('payment.create')}}</h1>
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
                    {{trans('Toolbar.Create_payment_method')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
     

    </div>
    <!--end::Toolbar container--> 

@endsection
@section('content')

    <!--begin::Content container-->
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

    <!----------------------------------------------------------------->
        <form id="StorForm" class="form d-flex flex-column flex-lg-row "
              action="{{route('admin.finance.payment.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="0">

        
            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <!--begin::General options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                   
                 
                     <div class="card-body">
                        <div class="row">
                        <!--begin::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 col  fv-row col">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('payment.icon')}}

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select class="form-select mb-2 @error('icon') is-invalid @enderror"
                                    data-control="select2" data-hide-search="true"
                                    data-placeholder="Select an option"
                                    id="icon" name="icon">
                            @include('dashbord.admin.Finance.payment.options')
                            </select>

                            <!--end::Input-->
                            @error('icon')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-10 col  fv-row col">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('payment.account')}}</label>
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
                        <div class="mb-10 col  fv-row col">
                            <label class="required form-label">{{ trans('payment.Status') }}</label>
                            <select class="form-select" data-control="select2" data-placeholder="Select an option"
                                    name="status" id="status">
                                <?php
                                $select_array = array('Active', 'NotActive')
                                ?>
                                <option></option>
                                @foreach($select_array as $value)
                                    <option value="{{ $value }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                     
                        </div>
                        <div class="row">
                           <!--begin::Input group-->
                           <div class="mb-10 fv-row col">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('payment.name')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_en')}}"</span>

                            </label>
                            <!--end::Label-->
                                  <!--begin::Input-->
                                  <input type="text" name="name[en]"
                                  class="form-control mb-2  @error('name[en]') is-invalid @enderror"
                                  placeholder="{{trans('payment.name')}}" value=""/>
                           <!--end::Input-->
                            @error('name_en')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--end::Input group-->
                        <div class="mb-10 fv-row col">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('payment.name')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_ar')}}"</span>

                            </label>
                            <!--end::Label-->
                                 <!--begin::Input-->
                                 <input type="text" name="name[ar]"
                                 class="form-control mb-2  @error('name[ar]') is-invalid @enderror"
                                 placeholder="{{trans('payment.name')}}" value=""/>
                          <!--end::Input-->
                            @error('name_ar')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->

                       
                        </div>

                    </div>
                    <!--end::Card header-->
                </div>
                <!--end::General options-->

                <div class="d-flex justify-content-end">
                    <!--begin::Button-->
               <!--     <button type="reset" class="btn btn-light me-5">{{trans('forms.cancel_btn')}}</button>
               -->    
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
    <!--end::Content container-->


@endsection
@section('js')

    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\finance\payment\StoreRequest', '#StorForm') !!}

    <script>

    $(document).ready(function() {
        function formatIcon(option) {
            if (!option.id) {
                return option.text;
            }
            var $option = $(
                '<span><i class="fa ' + $(option.element).data('icon') + '"></i> ' + option.text + '</span>'
            );
            return $option;
        }

        $('#icon').select2({
            templateResult: formatIcon,
            templateSelection: formatIcon,
            @if(app()->getLocale() =='ar')
            dir:'rtl',
            @else
            dir:'ltr',
            @endif
            escapeMarkup: function(m) { return m; }
        });
    });
</script>
@endsection
