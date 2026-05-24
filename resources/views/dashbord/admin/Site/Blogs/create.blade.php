@extends('dashbord.layouts.master')

@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('blog.create')}}</h1>
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
                    <a href="{{ route('admin.blog.index') }}"
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
                <a href="{{route('admin.blog.index')}}"
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
              action="{{route('admin.blog.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <!--begin::Aside column-->
            <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                <!--begin::Thumbnail settings-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h2>{{trans('blog.main_image')}}</h2>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body text-center pt-0">
                        <!--begin::Image input-->
                        <!--begin::Image input placeholder-->
                        <style>.image-input-placeholder {
                                background-image: url('{{asset('assets/media/svg/files/blank-image.svg')}}');
                            }

                            [data-bs-theme="dark"] .image-input-placeholder {
                                background-image: url('{{asset('assets/media/svg/files/blank-image-dark.svg')}}');
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
                                    <input type="file" name="main_image" accept=".png, .jpg, .jpeg"/>
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

                        @error('main_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Thumbnail settings-->
                <div class="card card-flush py-4">
                    <div class="card-body pt-0">
                        <div class="row g-9 mb-7">
                            <!--begin::Col-->
                            <div class="">
                                <!--begin::Label-->
                                <label
                                    class="required fs-6 fw-semibold mb-2">{{trans('blog.date_at')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input
                                    class="form-control form-control-solid @error('date_at') is-invalid @enderror"
                                    value="{{old('date_at')}}" name="date_at"
                                    placeholder="Pick date rage" id="date_at"/>
                                <!--end::Input-->
                                @error('date_at')
                                <div
                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="">
                                <!--begin::Label-->
                                <label
                                    class="required fs-6 fw-semibold mb-2">{{trans('blog.publisher')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input
                                    class="form-control form-control-solid @error('publisher') is-invalid @enderror"
                                    placeholder=""
                                    name="publisher" value="{{old('publisher')}}">
                                <!--end::Input-->
                                @error('publisher')
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
                            <h2>{{trans('blog.mainData')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('blog.title')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_en')}}"</span>

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="title_en"
                                   class="form-control mb-2  @error('title_en') is-invalid @enderror"
                                   placeholder="Product name" value="{{old('title_en')}}"/>
                            <!--end::Input-->
                            @error('title_en')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{trans('blog.details')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_en')}}"</span>
                            </label>
                            <!--end::Label-->
                        {{--                                        <input type="hidden" id="details_en" name="details_en" >--}}
                        <!--begin::Editor-->
                            <textarea id="details_en" name="details_en"
                                      class="min-h-200px mb-2 @error('details_en') is-invalid @enderror"></textarea>
                            <!--end::Editor-->
                            @error('details_en')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('blog.title')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_ar')}}"</span>

                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="title_ar"
                                   class="form-control mb-2  @error('title_ar') is-invalid @enderror"
                                   placeholder="Product name" value="{{old('title_ar')}}"/>
                            <!--end::Input-->
                            @error('title_ar')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{trans('blog.details')}}
                                <span class="text-muted fs-7">"{{trans('forms.lable_ar')}}"</span>
                            </label>
                            <!--end::Label-->
                        {{--                                        <input type="hidden" id="details_ar" name="details_ar" >--}}

                        <!--begin::Editor-->
                            <textarea id="details_ar" name="details_ar"
                                      class="min-h-200px mb-2 @error('details_ar') is-invalid @enderror"></textarea>
                            <!--end::Editor-->
                            @error('details_ar')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->

                    </div>
                    <!--end::Card header-->
                </div>
                <!--end::General options-->
                <!--begin::Meta options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('blog.images')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="mb-10">
                            <!--begin::Label-->
                            <label class="form-label">{{trans('blog.images')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="file" multiple
                                   class="form-control mb-2  @error('images[]') is-invalid @enderror"
                                   name="images[]"
                                   accept=".png, .jpg, .jpeg" placeholder="Meta tag name"/>
                            <!--end::Input-->
                            @error('images[]')
                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Input group-->
                        <div class="previews"></div>

                    </div>
                    <!--end::Card header-->
                </div>
                <!--end::Meta options-->

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
    {{--        <script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>--}}
    {{--        <script src="assets/plugins/custom/formrepeater/formrepeater.bundle.js"></script>--}}
    {{--    <script src="{{asset('assets/js/custom/apps/ecommerce/catalog/save-category.js')}}"></script>--}}

    <!--end::Vendors Javascript-->

    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Site\Blog\StoreRequest', '#StorForm') !!}
    <script>
        var KTAppBlogSave = function () {
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

            // Init quill editor
            const initQuill = () => {
                // Define all elements for quill editor
                const elements = [
                    '#div_details_en',
                    '#div_details_ar'
                ];
                const textarea_elements = [
                    '#details_en',
                    '#details_ar'
                ];
                /* var toolbarOptions = [
                     ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                     ['blockquote', 'code-block'],

                     [{ 'header': 1 }, { 'header': 2 }],               // custom button values
                     [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                     [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
                     [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                     [{ 'direction': 'rtl' }],                         // text direction

                     [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
                     [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

                     [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                     [{ 'font': [] }],
                     [{ 'align': [] }],

                     ['clean']                                         // remove formatting button
                 ];*/
                var toolbarOptions = [
                    [{
                        header: [1, 2, 3, 4, 5, 6, false]
                    }],
                    [{'direction': 'rtl'}],
                    ['bold', 'italic', 'underline'],
                    ['image', 'clean']
                ]
                // Loop all elements
                elements.forEach((element, index) => {
                    // Get quill element
                    let quill = document.querySelector(element);

                    // Break if element not found
                    if (!quill) {
                        return;
                    }

                    // Init quill --- more info: https://quilljs.com/docs/quickstart/
                    quill = new Quill(element, {
                        modules: {
                            toolbar: toolbarOptions
                        },
                        placeholder: ' ',
                        theme: 'snow' // or 'bubble'
                    });

                    quill.on('text-change', function (delta) {
                        $(textarea_elements[index]).val(quill.root.innerHTML);
                    });
                });

            }


            // Init daterangepicker
            const initDaterangepicker = () => {

                $("#date_at").daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        minYear: 2000,
                        maxYear: parseInt(moment().format("YYYY"), 12)
                    }
                );
            }
            // Init Dropzone
            const initDropzone = () => {

                Dropzone.options.uploadForm = { // The camelized version of the ID of the form element

                    // The configuration we've talked about above
                    autoProcessQueue: false,
                    uploadMultiple: true,
                    parallelUploads: 100,
                    maxFiles: 100,

                    // The setting up of the dropzone
                    init: function () {
                        var myDropzone = this;

                        // First change the button to actually tell Dropzone to process the queue.
                        this.element.querySelector("button[type=submit]").addEventListener("click", function (e) {
                            // Make sure that the form isn't actually being sent.
                            e.preventDefault();
                            e.stopPropagation();
                            myDropzone.processQueue();
                        });

                        // Listen to the sendingmultiple event. In this case, it's the sendingmultiple event instead
                        // of the sending event because uploadMultiple is set to true.
                        this.on("sendingmultiple", function () {
                            // Gets triggered when the form is actually being sent.
                            // Hide the success button or the complete form.
                        });
                        this.on("successmultiple", function (files, response) {
                            // Gets triggered when the files have successfully been sent.
                            // Redirect user or notify of success.
                        });
                        this.on("errormultiple", function (files, response) {
                            // Gets triggered when there was an error sending the files.
                            // Maybe show form again, and notify user of error
                        });
                    }

                }
            }


            // Public methods
            return {
                init: function () {
                    // Init forms
                    initckeditor();
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

