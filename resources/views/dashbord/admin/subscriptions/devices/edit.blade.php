@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('devices.create')}}</h1>
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
                    {{trans('Toolbar.devicesEdit')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.subscriptions.devices.index')}}"
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
              action="{{route('admin.subscriptions.devices.update',$one_data->id)}}" method="post" enctype="multipart/form-data">

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
                            <h2>{{trans('devices.image')}}</h2>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body text-center pt-0">
                        <!--begin::Image input-->
                        <!--begin::Image input placeholder-->
                        <style>.image-input-placeholder {
                            background-image: url('{{$one_data->image_url}}');
                        }

                        [data-bs-theme="dark"] .image-input-placeholder {
                            background-image: url('{{$one_data->image_url}}');
                        }</style>
                        <!--end::Image input placeholder-->
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
                            <h2>{{trans('devices.mainData')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <div class="row">
                            <div class="mb-10 col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices.name')}}
                                    (<span
                                    class="text-muted">{{trans('forms.lable_en')}}</span>)
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <?php  $name = $one_data->getTranslations('name');
                                  $description = $one_data->getTranslations('description');
                                ?>
                                
                                <input type="text" name="name_en"
                                       class="form-control mb-2  @error('name_en') is-invalid @enderror"
                                       placeholder="" value="{{old('name_en',$name['en'])}}"/>
                                <!--end::Input-->
                                @error('name_en')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-10 col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices.name')}}
                                    (<span
                                    class="text-muted">{{trans('forms.lable_ar')}}</span>)
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="name_ar"
                                       class="form-control mb-2  @error('name_ar') is-invalid @enderror"
                                       placeholder="" value="{{old('name_ar',$name['ar'])}}"/>
                                <!--end::Input-->
                                @error('name_ar')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                         </div>

                            <!--end::Input group-->
                            <!--begin::Input group-->
                        <div class="row">
                            <div class="mb-10 col">
                                      <!--begin::Label-->
                                      <label class="required form-label">{{trans('devices.code')}}

                                      </label>
                                      <!--end::Label-->
                                      <!--begin::Input-->
                                      <input type="number" name="code"
                                             class="form-control mb-2  @error('code') is-invalid @enderror"
                                             placeholder="" value="{{old('code',$one_data->code)}}"/>
                                      <!--end::Input-->
                                      @error('code')
                                      <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                      @enderror
                            </div>
                            <div class="mb-10 col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices.exercise_type')}}</label>
                                <!--end::Label-->
                                <select class="form-select mb-2 @error('exercise_type') is-invalid @enderror"
                                        data-control="select2" data-hide-search="false"
                                        data-placeholder="Select an option"
                                        id="exercise_type" name="exercise_type">
                                    <option>- {{trans('forms.select')}} -</option>
                                    @foreach($exercise as $row)
                                        <option value="{{ $row->id }}">{{ $row->title }}</option>
                                    @endforeach
                                </select>
                                @error('exercise_type')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->



    <div class="row">
                            <div class="mb-10 col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices.description')}}
                                    <span class="text-muted fs-7">"{{trans('forms.lable_en')}}"</span>

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <textarea name="description_en"
                                       class="form-control mb-2  @error('description_en') is-invalid @enderror"
                                       placeholder="" value="">{{old('description_en',$description['en'])}}</textarea>
                                <!--end::Input-->
                                @error('description_en')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-10 col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('devices.description')}}
                                    <span class="text-muted fs-7">"{{trans('forms.lable_ar')}}"</span>

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <textarea name="description_ar"
                                       class="form-control mb-2  @error('description_ar') is-invalid @enderror"
                                       placeholder="" value="">{{old('description_ar',$description['ar'])}}</textarea>
                                <!--end::Input-->
                                @error('description_ar')
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

    {!! JsValidator::formRequest('App\Http\Requests\Subscriptions\Devices\UpdateRequest', '#StorForm') !!}

   <script>
        var KTAppBlogSave = function () {


                const initInputData = () => {
$('[name="name"]').val('{{$one_data->name}}');
$('[name="code"]').val({{$one_data->code}});
$('[name="exercise_type"]').val({{$one_data->exercise_type}});
$('[name="description"]').val('{{$one_data->description}}');

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

