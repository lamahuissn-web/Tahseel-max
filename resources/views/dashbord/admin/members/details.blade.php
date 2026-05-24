@extends('dashbord.layouts.master')
@section('toolbar')

    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{ trans('members.members') }}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                        class="text-muted text-hover-primary">{{ trans('Toolbar.home') }}</a>
                </li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('Toolbar.members') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('members.members') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('members.members_table') }}</li>
            </ul>
        </div>


        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a href="{{ route('admin.Members.index') }}" class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z"
                                fill="currentColor" />
                        </svg>
                    </span>

                </a>
            </div>


        </div>
    </div>

@endsection
@section('content')

    <div class="d-flex flex-column flex-column-fluid">

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Layout-->
                <div class="d-flex flex-column flex-xl-row">
                    <div class="col-md-4">
                        @include('dashbord.admin.members.side_card')
                    </div>
                    <div class="col-md-8">
                        <div class="col-md-12">
                            @include('dashbord.admin.members.nav_taps')
                        </div>

                        <div class="col-md-12">
                            @yield('member_content')
                        </div>

                        <!--end::Content-->

                    </div>

                    <!--begin::Content-->


                </div>
                <!--end::Layout-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>

@stop

@section('js')

    @yield('js2')

    <script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\Members\StoreRequest', '#save_form') !!}





    <script>
        var KTAppBlogSave = function() {
            const initckeditor = () => {

                const elements_en = [
                    '#contract_bnod'
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
                            },
                            heading: {
                                options: [{
                                        model: 'paragraph',
                                        title: 'Paragraph',
                                        class: 'ck-heading_paragraph'
                                    },
                                    {
                                        model: 'heading1',
                                        view: 'h1',
                                        title: 'Heading 1',
                                        class: 'ck-heading_heading1'
                                    },
                                    {
                                        model: 'heading2',
                                        view: 'h2',
                                        title: 'Heading 2',
                                        class: 'ck-heading_heading2'
                                    },
                                    {
                                        model: 'heading3',
                                        view: 'h3',
                                        title: 'Heading 3',
                                        class: 'ck-heading_heading3'
                                    }
                                ]
                            },
                            language: 'en'
                        })
                        .then(editor => {
                            console.log(editor);
                        })
                        .catch(error => {
                            console.error(error);
                        });


                });
                // Loop all elements


            }

            // Init quill editor



            // Init daterangepicker
            const initDaterangepicker = () => {

                $("#birth_date").daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    minYear: 2000,
                    maxYear: parseInt(moment().format("YYYY"), 12)
                });
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
                    init: function() {
                        var myDropzone = this;

                        // First change the button to actually tell Dropzone to process the queue.
                        this.element.querySelector("button[type=submit]").addEventListener("click",
                            function(e) {
                                // Make sure that the form isn't actually being sent.
                                e.preventDefault();
                                e.stopPropagation();
                                myDropzone.processQueue();
                            });

                        // Listen to the sendingmultiple event. In this case, it's the sendingmultiple event instead
                        // of the sending event because uploadMultiple is set to true.
                        this.on("sendingmultiple", function() {
                            // Gets triggered when the form is actually being sent.
                            // Hide the success button or the complete form.
                        });
                        this.on("successmultiple", function(files, response) {
                            // Gets triggered when the files have successfully been sent.
                            // Redirect user or notify of success.
                        });
                        this.on("errormultiple", function(files, response) {
                            // Gets triggered when there was an error sending the files.
                            // Maybe show form again, and notify user of error
                        });
                    }

                }
            }


            // Public methods
            return {
                init: function() {
                    initDaterangepicker();
                    initckeditor();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppBlogSave.init();
        });
    </script>

@endsection
