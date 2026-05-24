@extends('dashbord.layouts.master')
@section('toolbar')
      <!--begin::Toolbar container-->
      <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('task_management.create')}}</h1>
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
                     {{trans('Toolbar.subscriptions')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.Create_task_management')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.subscriptions.task_management.index')}}"
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



    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="t_container">
            <div class="card shadow-sm ">
                <div class="card-header">
                    <h3 class="card-title"></i> {{trans('sub.Task_management')}}</h3>
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



<form id="save_form" method="post" action="{{route('admin.subscriptions.task_management.update',$one_data->id)}}"
                      enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH"/>

                    <input type="hidden" name="id" value="{{$one_data->id}}">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-4">
                                <label class="required form-label">{{trans('sub.title')}} (<span
                                        class="text-muted">{{trans('forms.lable_en')}}</span>)</label>
                                <input type="text" name="title_en" id="title_en" class="form-control mb-2"
                                       placeholder="{{trans('sub.title')}}"
                                       value="" required autocomplete/>
                            </div>

                            <div class="col-md-4">
                                <label class="required form-label">{{trans('sub.title')}} (<span
                                        class="text-muted">{{trans('forms.lable_ar')}}</span>)</label>
                                <input type="text" name="title_ar" id="title_ar" class="form-control mb-2"
                                       placeholder="{{trans('sub.title')}}"
                                       value="" required autocomplete/>
                            </div>


                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.Type') }}</label>
                                <select class="form-select" data-control="select2" data-placeholder="Select an option"
                                        name="type" id="type">
                                        <?php
                                        $select_array = array('Maintenance', 'Complaint', 'Tasks for employees',
                                            'Reports','Requirements','Managers schedule' )
                                        ?>
                                        <option></option>
                                        @foreach($select_array as $key=>$value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">{{trans('sub.Date')}}</label>
                                <input type="date" name="date" id="date" class="form-control mb-2"  value="" required autocomplete/>
                            </div>
                            <div class="mb-10 fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('reqholiday.Employee ID')}}

                                </label>
                                <!--end::Label-->

                                <select name="emp_id" id="emp_id"
                                        class="form-select @error('emp_id') is-invalid @enderror"
                                        data-control="select2"
                                        data-allow-clear="true"
                                        data-placeholder="{{trans('maindata.Select')}}">

                                </select>

                                @error('emp_id')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror


                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.Status') }}</label>
                                <select class="form-select" data-control="select2" data-placeholder="Select an option"
                                        name="status" id="status">
                                        <?php
                                        $select_array = array( 'In Progress',  'Finished',  'Not Finished',
                                        )
                                        ?>
                                        <option></option>
                                        @foreach($select_array as $key=>$value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                </select>
                            </div>

                        </div>



                        <div class="row" style="margin-top: 10px">
                            <div class="col-md-12 col">
                                <label class="form-label required">{{trans('sub.details')}}(<span
                                        class="text-muted">{{trans('forms.lable_en')}}</span>)</label>
                                <textarea name="details_en" id="details_en"
                                          value=""
                                          class="form-control mb-2"
                                          ></textarea>
                            </div>

                            <div class="col-md-12 col">
                                <label class="form-label required">{{trans('sub.details')}}(<span
                                        class="text-muted">{{trans('forms.lable_ar')}}</span>)</label>
                                <textarea name="details_ar" id="details_ar"
                                          value=""
                                          class="form-control mb-2"
                                          ></textarea>
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
   {!! JsValidator::formRequest('App\Http\Requests\Subscriptions\Task_management\StoreRequest', '#StorForm') !!}

    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>

    <script>
        var KTAppBlogSave = function () {

            const initInputData = () => {

$('[name="title"]').val('{{$one_data->title}}');
$('[name="type"]').val('{{$one_data->type}}');
$('[name="date"]').val('{{$one_data->date}}');
$('[name="details"]').val('{{$one_data->details}}');
$('[name="emp_id"]').val({{$one_data->emp_id}});
$('[name="status"]').val('{{$one_data->status}}');
$('[data-control="select2"]').trigger("change");



            }
  // Init daterangepicker
  const initSelectEmplyee = () => {

                $('#emp_id').select2({
                    ajax: {
                        url: '{{ route('admin.hr.getEmployees') }}',
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,// search term
                                page: params.page || 1
                            };
                        }, processResults: function (data, params) {
                            params.page = params.page || 1;
                            var mappedData = $.map(data.data, function (item) {
                                return {id: item.id, text: item.name, imageUrl: item.imageUrl};
                            });
                            return {
                                results: mappedData,
                                pagination: {
                                    more: (params.page * 10) < data.total
                                }

                            };
                        },
                        cache: true
                    },
                    placeholder: 'Select an option',
                    minimumInputLength: 0
                });

                $('#search-input').on('keyup', function () {
                    $('#select2-dropdown').empty().trigger('change');
                });
            };
            // Init daterangepicker
            const initDaterangepicker = () => {

                $("#date").daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoApply: true,
                    minDate: "{{date('m/d/Y')}}",
                    minYear: 2024,
                    maxYear: parseInt(moment().format("YYYY"), 12)
                });



            }

            // Public methods
            return {
                init: function () {
                    // Init forms
                    initInputData();
                 //   initDaterangepicker();
                    initSelectEmplyee();

                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppBlogSave.init();
        });

    </script>

@endsection
