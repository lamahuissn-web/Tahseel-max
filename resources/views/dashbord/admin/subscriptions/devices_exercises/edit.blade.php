@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('devices.edit')}}</h1>
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
                    {{trans('Toolbar.Editdevices_exercises')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.subscriptions.exercise_devices.index')}}"
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
        <form id="StorForm" class="form d-flex flex-column flex-lg-row "
              action="{{route('admin.subscriptions.exercise_devices.update',$one_data->id)}}" method="post"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PATCH"/>

        <input type="hidden" name="id" value="{{$one_data->id}}">
      <!--begin::Aside column-->
      <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
        <!--begin::Thumbnail settings-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{trans('devices.Data')}}</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body text-center pt-0">
                <style>.image-input-placeholder {
                    background-image: url('{{asset('assets/media/svg/files/blank-image.svg')}}');
                }

                [data-bs-theme="dark"] .image-input-placeholder {
                    background-image: url('{{asset('assets/media/svg/files/blank-image-dark.svg')}}');
                }</style>
                <div class="mb-7">
                    <!--begin::Image input-->
                    <div
                        class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                        data-kt-image-input="true">
                        <!--begin::Preview existing avatar-->
                        <div class="image-input-wrapper w-150px h-150px"></div>
                        <!--end::Preview existing avatar-->
                        <!--begin::Label-->
                        <label
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                            title="Change avatar">
                            <!--begin::Icon-->
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <!--end::Icon-->
                            <!--begin::Inputs-->
                            <input type="file" name="image" accept=".png, .jpg, .jpeg"/>
                            <input type="hidden" name="avatar_remove"/>
                            <!--end::Inputs-->
                        </label>
                        <!--end::Label-->
                        <!--begin::Cancel-->
                        <span
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                            title="Cancel avatar">
                                                    <i class="bi bi-x fs-2"></i>
                                                </span>
                        <!--end::Cancel-->
                        <!--begin::Remove-->
                        <span
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                            title="Remove avatar">
                                                    <i class="bi bi-x fs-2"></i>
                                                </span>
                        <!--end::Remove-->
                    </div>

                    <!--end::Image input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">Set the category thumbnail image. Only *.png, *.jpg
                        and
                        *.jpeg image files are accepted
                    </div>
                    <!--end::Description-->
                </div>

                @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Thumbnail settings-->

    </div>
    <!--end::Aside column-->
            <!--begin::Main column-->
            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <!--begin::General options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('devices_exercise.mainData')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="row">

                            <div class="mb-10 fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices_exercise.name')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="name"
                                       class="form-control mb-2  @error('name') is-invalid @enderror"
                                       placeholder="" value="{{old('name',$one_data->name)}}"/>
                                <!--end::Input-->
                                @error('name')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        <!-------------------------------------------->
                        <div class="mb-10 col  fv-row col">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('devices_exercise.device_code')}}</label>
                            <!--end::Label-->
                            <select class="form-select mb-2 @error('device_code') is-invalid @enderror"
                                    data-control="select2" data-hide-search="false"
                                    data-placeholder="Select an option"
                                    id="device_code">   
                                <option>- {{trans('forms.select')}} -</option>
                                @foreach($codes as $row)
                                <option value="{{ $row->id }}">{{ $row->code}}</option>
                             @endforeach
                            </select>
                            @error('device_code')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-------------------------------------------------------->
                        <div class="mb-10 fv-row col">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('devices_exercise.duration')}}

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="duration"
                                   class="form-control mb-2  @error('duration') is-invalid @enderror"
                                   placeholder="" value="{{old('duration',$one_data->duration)}}"/>
                            <!--end::Input-->
                            @error('duration')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                         </div>
                        </div>
                        <!-------------------------------------------->
                        <div class="row">

                            <div class="mb-10 fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices_exercise.numbers')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="number" name="numbers"
                                       class="form-control mb-2  @error('numbers') is-invalid @enderror"
                                       placeholder="" value="{{old('numbers',$one_data->numbers)}}"/>
                                <!--end::Input-->
                                @error('numbers')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-------------------------------------------->
                            <div class="mb-10 fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices_exercise.exercise_level')}}

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="exercise_level"
                                   class="form-control mb-2  @error('exercise_level') is-invalid @enderror"
                                   placeholder="" value="{{old('exercise_level',$one_data->exercise_level)}}"/>
                            <!--end::Input-->
                            @error('exercise_level')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                         </div>
                             <!-------------------------------------------->
                             <div class="mb-10 fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices_exercise.groups')}}
    
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="number" name="groups"
                                       class="form-control mb-2  @error('groups') is-invalid @enderror"
                                       placeholder="" value="{{old('groups',$one_data->groups)}}"/>
                                <!--end::Input-->
                                @error('groups')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                             </div>
            </div>
                <!-------------------------------------------->
                <div class="row">

                            <div class="mb-10 fv-row col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices_exercise.video_link')}}

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="link"
                                   class="form-control mb-2  @error('link') is-invalid @enderror"
                                   placeholder="" value="{{old('link',$one_data->link)}}"/>
                            <!--end::Input-->
                            @error('link')
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
    <!--end::Content container-->

@endsection
@section('js')


    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Subscriptions\Devices_exercises\UpdateRequest', '#StorForm') !!}

    <script>
        var KTAppBlogSave = function () {
            const initInputData = () => {

                $('[name="name"]').val('{{$one_data->name}}');
                $('[name="device_code"]').val({{$one_data->device_code}});
                $('[name="exercise_level"]').val({{$one_data->exercise_level}});
                $('[name="link"]').val('{{$one_data->link}}');
                $('[name="numbers"]').val({{$one_data->numbers}});
                $('[name="groups"]').val({{$one_data->groups}});
                $('[data-control="select2"]').trigger("change");


            }


            // Public methods
            return {
                init: function () {
                    // Init forms
                    initInputData();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppBlogSave.init();
        });
    </script>
@endsection

