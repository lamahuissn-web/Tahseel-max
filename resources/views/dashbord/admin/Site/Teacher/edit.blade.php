@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('teacher.create')}}</h1>
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
                    {{trans('Toolbar.site')}}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.teacher.index') }}"
                       class="text-muted text-hover-primary"> {{trans('Toolbar.blog')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.blogCreate')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.teacher.index')}}"
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
              action="{{route('admin.teacher.update',$one_data->id)}}" method="post"
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
                            <h2>{{trans('teacher.image')}}</h2>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body text-center pt-0">
                        <!--begin::Image input-->
                        <!--begin::Image input placeholder-->
                        <style>.image-input-placeholder {
                                background-image: url('{{$one_data->Image}}');
                            }

                            [data-bs-theme="dark"] .image-input-placeholder {
                                background-image: url('{{$one_data->Image}}');
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
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                {{-- <div class="card-header">
                     <!--begin::Card title-->
                     <div class="card-title">
                         <h2>{{trans('teacher.time_data')}}</h2>
                     </div>
                     <!--end::Card title-->
                 </div>--}}
                <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body text-center pt-0">
                        <div class="row g-9 mb-7">
                            <!--begin::Col-->
                            <div class="">
                                <!--begin::Label-->
                                <label
                                    class="required fs-6 fw-semibold mb-2">{{trans('teacher.phone')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="tel"
                                       class="form-control form-control-solid @error('phone') is-invalid @enderror"
                                       value="{{old('phone',$one_data->phone)}}" name="phone"
                                       placeholder="" id="phone"/>
                                <!--end::Input-->
                                @error('phone')
                                <div
                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="">
                                <!--begin::Label-->
                                <label
                                    class="required fs-6 fw-semibold mb-2">{{trans('teacher.email')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="email"
                                       class="form-control form-control-solid @error('email') is-invalid @enderror"
                                       placeholder=""
                                       name="email" value="{{old('email',$one_data->email)}}">
                                <!--end::Input-->
                                @error('email')
                                <div
                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!--end::Col-->
                        </div>

                    </div>
                </div>

            </div>
            <!--end::Aside column-->
            <!--begin::Main column-->
            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <!--begin::General options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('teacher.mainData')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('teacher.name')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_en')}}"</span>

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="name_en"
                                   class="form-control mb-2  @error('name_en') is-invalid @enderror"
                                   placeholder="Product name"
                                   value="{{old('name_en',$one_data->name_en)}}"/>
                            <!--end::Input-->
                            @error('name_en')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('teacher.jop_title')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_en')}}"</span>

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="jop_title_en"
                                   class="form-control mb-2  @error('jop_title_en') is-invalid @enderror"
                                   placeholder="Product name"
                                   value="{{old('jop_title_en',$one_data->jop_title_en)}}"/>
                            <!--end::Input-->
                            @error('jop_title_en')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{trans('teacher.description')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_en')}}"</span>
                            </label>
                            <!--end::Label-->
                        {{--                                        <input type="hidden" id="description_en" name="description_en" value="{{old('description_en',$one_data->description_en)}}" >--}}
                        <!--begin::Editor-->
                            <textarea id="description_en" name="description_en"
                                      class="min-h-200px mb-2 @error('description_en') is-invalid @enderror">
                                            {{old('description_en',$one_data->description_en)}} </textarea>
                            <!--end::Editor-->
                            @error('description_en')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('teacher.name')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_ar')}}"</span>

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="name_ar"
                                   class="form-control mb-2  @error('name_ar') is-invalid @enderror"
                                   placeholder="Product name"
                                   value="{{old('name_ar',$one_data->name_ar)}}"/>
                            <!--end::Input-->
                            @error('name_ar')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('teacher.jop_title')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_ar')}}"</span>

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="jop_title_ar"
                                   class="form-control mb-2  @error('jop_title_ar') is-invalid @enderror"
                                   placeholder="Product name"
                                   value="{{old('jop_title_ar',$one_data->jop_title_ar)}}"/>
                            <!--end::Input-->
                            @error('jop_title_ar')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{trans('teacher.description')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_ar')}}"</span>
                            </label>
                            <!--end::Label-->
                        {{--                                        <input type="hidden" id="description_ar" name="description_ar" value="{{old('description_ar',$one_data->description_ar)}}" >--}}

                        <!--begin::Editor-->
                            <textarea id="description_ar" name="description_ar"
                                      class="min-h-200px mb-2 @error('description_ar') is-invalid @enderror">
                                            {{old('description_ar',$one_data->description_ar)}}

                                        </textarea>
                            <!--end::Editor-->
                            @error('description_ar')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->

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
    <!--begin::Vendors Javascript(used for this page only)-->
    {{--        <script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>--}}
    {{--        <script src="assets/plugins/custom/formrepeater/formrepeater.bundle.js"></script>--}}
    {{--    <script src="{{asset('assets/js/custom/apps/ecommerce/catalog/save-category.js')}}"></script>--}}

    <!--end::Vendors Javascript-->

    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Site\Teacher\UpdateRequest', '#StorForm') !!}
    <script>
        var KTAppBlogSave = function () {
            const initckeditor = () => {

                const elements_en = [
                    '#description_en'
                ];
                const elements_ar = [
                    '#description_ar'
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
                                    '|', 'link', 'insertTable', 'mediaEmbed', 'blockQuote',
                                    '|', 'bulletedList', 'numberedList', 'outdent', 'indent'
                                ]
                            }, heading: {
                                options: [
                                    {model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph'},
                                    {model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1'},
                                    {model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2'},
                                    {model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3'}
                                ]
                            }, language: 'en'
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
                                    '|', 'link', 'insertTable', 'mediaEmbed', 'blockQuote',
                                    '|', 'bulletedList', 'numberedList', 'outdent', 'indent'
                                ]
                            }, heading: {
                                options: [
                                    {model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph'},
                                    {model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1'},
                                    {model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2'},
                                    {model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3'}
                                ]
                            }, language: 'ar'
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
                init: function () {
                    // Init forms
                    initckeditor();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppBlogSave.init();
        });
    </script>
@endsection

