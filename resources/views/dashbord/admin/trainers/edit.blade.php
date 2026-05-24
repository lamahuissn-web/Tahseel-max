@extends('dashbord.layouts.master')
@section('css')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.7.0/build/css/intlTelInput.css">
@endsection

@section('toolbar')

    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{trans('trainers.trainers')}}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                                                          class="text-muted text-hover-primary">{{trans('Toolbar.home')}}</a>
                </li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('trainers.trainers')}}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('trainers.trainers')}}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('trainers.edit_trainers')}}</li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4"
                   href="{{route('admin.Trainers.index')}}">
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
        <form id="save_form" class="form d-flex flex-column flex-lg-row "
              action="{{ route('admin.Trainers.update',$one_data->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('trainers.trainer_image')}}</h2>
                        </div>
                    </div>

                    <div class="card-body text-center pt-0">

                        @php
                            if (!empty($one_data->user_image)) {
                              $image_path = \Illuminate\Support\Facades\Storage::disk('images')->url($one_data->user_image);
                              $imageurl = asset((\Illuminate\Support\Facades\Storage::disk('images')->exists($one_data->user_image)) ? $image_path : 'assets/images/blank.png');
                             // dd($imageurl);
                              } else {
                                 $imageurl = asset('assets/images/blank.png');
                              }
                        @endphp
                        <style>.image-input-placeholder {
                                background-image: url('{{$imageurl}}');
                            }

                            [data-bs-theme="dark"] .image-input-placeholder {
                                background-image: url('{{$imageurl}}');
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
                                    <input type="file" name="user_image" accept=".png, .jpg, .jpeg"/>
                                    <input type="hidden" name="user_image"/>
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

                        @error('member_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>



            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('trainers.add_new')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">

                        <div class="row">

                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('trainers.trainer_name') }}</label>
                                <select class="form-select" onchange="get_trainer_details(this.value)" data-control="select2" data-placeholder="Select an option"
                                        name="emp_id" id="emp_id">
                                    <option value="">{{ trans('sub.select') }}</option>
                                    @foreach($employees as $key)
                                        <option
                                            value="{{ $key->id }}" {{ old('emp_id',$one_data->emp_id) == $key->id ? 'selected' : '' }}>{{ $key->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="required form-label">{{trans('trainers.user_name')}}</label>
                                <input type="text" name="user_name" id="user_name" class="form-control mb-2"
                                       placeholder="{{trans('trainers.user_name')}}" value="{{old('user_name',$one_data->user_name)}}" required
                                       autocomplete/>
                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">{{trans('trainers.password')}}(<span
                                        class="text-muted">{{trans('forms.lable_ar')}}</span>)</label>
                                <input type="text" name="password" id="password" value="{{old('password',$one_data->real_password)}}"
                                       class="form-control mb-2" placeholder="{{trans('trainers.password')}}"/>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px">
                            <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('trainers.email')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="email" id="email"
                                       class="form-control mb-2  @error('email') is-invalid @enderror"
                                       placeholder="{{trans('trainers.email')}}" value="{{old('email')}}"/>
                                <!--end::Input-->
                                @error('email')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <input type="hidden" name="phone_full" value="{{old('phone_full',$one_data->phone_full)}}">
                                <input type="hidden" name="country_code" value="{{old('country_code',$one_data->country_code)}}">
                                <label class="required form-label">{{trans('trainers.phone')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="phone" id="phone"
                                       class="form-control mb-2  @error('phone') is-invalid @enderror"
                                       placeholder="{{trans('trainers.phone')}}" value="{{old('phone',$one_data->phone_full)}}"/>
                                <!--end::Input-->
                                @error('phone')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="fv-plugins-message-container invalid-feedback" id="output"></div>

                            </div>

                            <div class="col-md-4">
                                <label class="required form-label">{{trans('trainers.training_percentage')}}(<span
                                        class="text-muted">{{trans('forms.training_percentage')}}</span>)</label>
                                <input type="number" step="any" name="training_percentage" id="training_percentage" value="{{old('training_percentage',$one_data->training_percentage)}}"
                                       class="form-control mb-2" placeholder="{{trans('trainers.training_percentage')}}"/>
                            </div>

                            <div class="col-md-4">
                                <label class="required form-label">{{trans('trainers.job')}}</label>
                                <input type="text" step="any" name="job" id="job" value="{{old('job',$one_data->job)}}"
                                       class="form-control mb-2" placeholder="{{trans('trainers.job')}}"/>
                            </div>


                        </div>








                    </div>
                    <!--end::Card header-->
                </div>

                <div class="d-flex justify-content-end">
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


@stop
@section('js')


    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
    {{--    {!! JsValidator::formRequest('App\Http\Requests\Admin\Members\StoreRequest', '#save_form') !!}--}}
    <script>
        var KTAppBlogSave = function () {
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


            }

            // Init quill editor


            // Init daterangepicker
            const initDaterangepicker = () => {

                $("#birth_date").daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        minYear: 2000,
                        maxYear: parseInt(moment().format("YYYY"), 12)
                    }
                );
            }


            // Public methods
            return {
                init: function () {
                    initDaterangepicker();
                    initckeditor();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppBlogSave.init();
        });
    </script>



    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.7.0/build/js/intlTelInput.min.js"></script>
    {{--  @if(app()->getLocale() =='ar')
                i18n: ar,
                @else
                i18n: en,
                @endif
                --}}
    <script>
        const input = document.querySelector("#phone");
        const output = document.querySelector("#output");

        const iti = window.intlTelInput(input, {
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.7.0/build/js/utils.js",
            separateDialCode: true,
            initialCountry: "sa",
            nationalMode: true,
            hiddenInput: function (telInputName) {
                return {
                    phone: "phone_full",
                    country: "country_code"
                };
            }
        });
        const handleChange = () => {
            let text;
            if (input.value) {
                if (iti.isValidNumber()) {
                    text = '';
                    // text =  "Valid number! Full international format: " + iti.getNumber();
                    $(input).addClass('is-valid')
                    $(input).removeClass('is-invalid')

                } else {
                    text = "Invalid number - please try again";
                    $(input).addClass('is-invalid')
                    $(input).removeClass('is-valid')

                }

                /*text = iti.isValidNumber()
                    ? "Valid number! Full international format: " + iti.getNumber()
                    : "Invalid number - please try again";*/
            } else {
                text = "Please enter a valid number below";
                $(input).addClass('is-invalid')
                $(input).removeClass('is-valid')
            }
            const textNode = document.createTextNode(text);
            output.innerHTML = "";
            output.appendChild(textNode);
        };

        // listen to "keyup", but also "change" to update when the user selects a country
        input.addEventListener('change', handleChange);
        input.addEventListener('keyup', handleChange);
        /* intlTelInput(input, {
             hiddenInput: function(telInputName) {
                 return {
                     phone: "phone_full",
                     country: "country_code"
                 };
             }
         });*/
    </script>


    <script>
        function  get_trainer_details(id)
        {
            $.ajax({
                url: '{{route('admin.get_trainer_details')}}',
                type: 'get',
                data: {
                    id: id,
                },
                success: function (response) {
                    console.log('sss'+response.record.email);
                    $('#email').val(response.record.email);
                    $('#phone').val(response.record.phone);


                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }
    </script>

@endsection
