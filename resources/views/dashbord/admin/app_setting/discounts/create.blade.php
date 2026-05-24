@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{trans('discounts.App_Settings')}}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                                                          class="text-muted text-hover-primary">{{trans('Toolbar.home')}}</a>
                </li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('discounts.App_settings')}}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('discounts.add_discounts')}}</li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4"
                   href="{{route('admin.app_setting.Discount.index')}}">
                {{--                    <i class="bi bi-arrow-clockwise ">{{trans('sub.back')}}</i>--}}
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
        </div>
    </div>

@endsection
@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="t_container">
            <div class="card shadow-sm ">
                <div class="card-header">
                    <h3 class="card-title"></i> {{trans('discounts.add_new')}}</h3>

                </div>

                <form id="save_form" method="post" action="{{ route('admin.app_setting.Discount.store') }}"
                      enctype="multipart/form-data">
                    @csrf
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
                        <div class="row">
                           
                            <div class="col">
                                <label class="required form-label">{{trans('discounts.name')}}
                                    (<span
                                    class="text-muted">{{trans('forms.lable_en')}}</span>)</label>
                               
                                <input type="text" name="name_en" id="name_en" class="form-control mb-2"
                                       placeholder="{{trans('discounts.name')}}" value="" required
                                       autocomplete/>
                            </div>
                            <div class="col">
                                <label class="required form-label">{{trans('discounts.name')}}
                                    (<span
                                    class="text-muted">{{trans('forms.lable_ar')}}</span>)</label>
                                </label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control mb-2"
                                       placeholder="{{trans('discounts.name')}}" value="" required
                                       autocomplete/>
                            </div>
                            <div class="col">
                                <label class="form-label">{{trans('discounts.code')}} </label>
                                <input type="number" name="code" id="code" class="form-control mb-2"
                                       placeholder=""
                                       value="{{old('code')}}" required autocomplete/>
                            </div>
                        </div>
<div class="row">
    <div class="col">
        <label class="required form-label">{{ trans('discounts.type') }}</label>
        <select class="form-select" data-control="select2" data-placeholder="Select an option"
                name="type" id="type">
                <option value="">{{ trans('discounts.select') }}</option>
                <?php
                $select_array = array('percentage','amount')
                ?>
                @foreach($select_array as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
        </select>
    </div>
    <div class="col">
        <label
            class="required fs-6 fw-semibold mb-2">{{trans('discounts.start_date')}}</label>
        <input
            class="form-control form-control-solid @error('start_date') is-invalid @enderror"
            value="" name="start_date"
            placeholder="Pick date rage" id="start_date"/>
        @error('start_date')
        <div
            class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col">
        <label
            class="required fs-6 fw-semibold mb-2">{{trans('discounts.end_date')}}</label>
        <input
            class="form-control form-control-solid @error('end_date') is-invalid @enderror"
            value="" name="end_date"
            placeholder="Pick date rage" id="end_date"/>
        @error('end_date')
        <div
            class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
 

</div>


<div class="row">
 
    <div class="col">
        <label class="form-label">{{trans('discounts.percentage')}} </label>
        <input type="number" name="percentage" id="percentage" class="form-control mb-2"
               min=0 max=100 placeholder="0%"
               value="{{old('percentage')}}" required autocomplete/>
    </div>
  
    <div class="col">
        <label class="form-label">{{trans('discounts.amount')}}</label>
        <input type="number" name="amount" id="amount" class="form-control mb-2"
              placeholder=""
               value="{{old('amount')}}" required autocomplete/>
    </div>
    <div class="col">
        <label class="form-label">{{trans('discounts.max_limit')}}</label>
        <input type="number" name="max_limit" id="max_limit" class="form-control mb-2"
                placeholder=""
               value="{{old('max_limit')}}" required autocomplete/>
    </div>
   
</div>
                        
                            <div class="d-flex justify-content-end" style="margin-top: 20px">
                                <button type="submit" id="" class="btn btn-primary">
                                    <span class="indicator-label">{{trans('forms.save_btn')}}</span>
                                    <span class="indicator-progress">Please wait...
							<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
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
 
 <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>

    <script>
        var KTAppBlogSave = function () {
            

            const initDaterangepicker = () => {

$("#start_date,#end_date").daterangepicker({
singleDatePicker: true,
showDropdowns: true,
minYear: 2024,
maxYear: parseInt(moment().format("YYYY"), 12)
}
);
}
                 
            return {
                init: function () {
                 
                    initDaterangepicker();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppBlogSave.init();
        });

    </script>


@endsection
