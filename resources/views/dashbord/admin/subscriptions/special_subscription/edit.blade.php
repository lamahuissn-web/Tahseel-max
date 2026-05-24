@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{ trans('sub.main_subscriptions') }}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                        class="text-muted text-hover-primary">{{ trans('Toolbar.home') }}</a>
                </li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('Toolbar.subscriptions') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('sub.special_subscriptions') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('sub.edit_subscription') }}</li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4"
                    href="{{ route('admin.subscriptions.special_subscriptions.index') }}">
                    {{--                    <i class="bi bi-arrow-clockwise ">{{trans('sub.back')}}</i> --}}
                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/arrows/arr054.svg-->
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z"
                                fill="currentColor" />
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
                    <h3 class="card-title"></i> {{ trans('sub.add_new_subscription') }}</h3>

                </div>

                <form id="save_form" method="post"
                    action="{{ route('admin.subscriptions.special_subscriptions.update', $one_data->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
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

                        <?php $name = $one_data->getTranslations('name');
                        //dd($name);
                        ?>
                        <div class="row">
                            {{-- <div class="col-md-4">
                                <label class="required form-label">{{trans('sub.trainer')}}</label>
                                <select class="form-select" data-control="select2" data-placeholder="Select an option"
                                        name="trainer_id" id="trainer_id">
                                    <option value="">{{trans('sub.select')}}</option>
                                    @foreach ($trainers as $key)
                                        <option
                                            value="{{$key->id}}" {{ old('trainer_id',$one_data->trainer_id) == $key->id ? 'selected' : '' }}>{{$key->user_name}}</option>
                                    @endforeach

                                </select>
                            </div> --}}

                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.exercise_type') }}</label>
                                <select class="form-select" data-control="select2" data-placeholder="Select an option"
                                    name="exercise_type" id="exercise_type">
                                    <option value="">{{ trans('sub.select') }}</option>
                                    @foreach ($exercise_type as $key)
                                        <option value="{{ $key->id }}"
                                            {{ old('exercise_type', $one_data->exercise_type) == $key->id ? 'selected' : '' }}>
                                            {{ $key->title }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.sub_name') }} (<span
                                        class="text-muted">{{ trans('forms.lable_en') }}</span>)</label>
                                <input type="text" name="title_en" id="title_en" class="form-control mb-2"
                                    placeholder="{{ trans('sub.sub_name') }}"
                                    value="{{ old('title_en', optional($name)['en']) }}" required autocomplete />
                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.sub_name') }}(<span
                                        class="text-muted">{{ trans('forms.lable_ar') }}</span>)</label>
                                <input type="text" name="title_ar" id="title_ar"
                                    value="{{ old('title_ar', optional($name)['ar']) }}" class="form-control mb-2"
                                    placeholder="{{ trans('sub.sub_name') }}" />
                            </div>





                        </div>

                        <div class="row" style="margin-top: 10px">


                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.price') }}</label>
                                <input type="number" step="any" name="price" id="price"
                                    value="{{ old('price', $one_data->price) }}" class="form-control mb-2" />
                            </div>

                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.session_numbers') }}(<span
                                        class="text-muted">{{ trans('sub.count') }}</span>)</label>
                                <input type="number" name="duration" id="duration"
                                    value="{{ old('duration', $one_data->price) }}" class="form-control mb-2"
                                    placeholder="{{ trans('sub.duration') }}" />
                            </div>

                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.max_discount') }}(<span
                                        class="text-muted">%</span>)</label>
                                <input type="number" step="any" name="max_discount" id="max_discount"
                                    value="{{ old('title_ar', $one_data->max_discount) }}" class="form-control mb-2"
                                    placeholder="{{ trans('sub.max_discount') }}" />
                            </div>

                        </div>


                        <div class="col-md-12">
                            <div class="form-group text-end" style="margin-top: 27px;">
                                <button type="submit" name="btnSave" value="btnSave" id="btnSave"
                                    class="btn btn-success btn-flat ">
                                    <i class="bi bi-save"></i> {{ trans('sub.save') }}
                                </button>
                            </div>
                        </div>

                    </div>


                </form>

            </div>


        </div>
    </div>










@stop
@section('js')




    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest(
        'App\Http\Requests\Admin\subscription\special_subscriptions\SaveSpecialSubsacription_R',
        '#save_form',
    ) !!}
    <script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script>

    <script>
        var KTAppBlogSave = function() {
            const initTagify = () => {
                // The DOM elements you wish to replace with Tagify
                var input1 = document.querySelector("#details_tag_ar");
                var input2 = document.querySelector("#details_tag_en");

                // Initialize Tagify components on the above inputs
                new Tagify(input1);
                new Tagify(input2);

            };
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
                            language: 'ar'
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
                init: function() {
                    // Init forms
                    initckeditor();
                    initTagify();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppBlogSave.init();
        });
    </script>


@endsection
