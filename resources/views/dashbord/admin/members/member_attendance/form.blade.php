@extends('dashbord.layouts.master')
@section('css')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.7.0/build/css/intlTelInput.css">
@endsection

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
                <li class="breadcrumb-item text-muted">{{ trans('members.members_attendance') }}</li>
            </ul>
        </div>


        <div class="d-flex align-items-center gap-2 gap-lg-3">
            {{--            <div class="d-flex"> --}}
            {{--                <a href="{{route('admin.Members.index')}}" --}}
            {{--                   class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4"> --}}
            {{--                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg--> --}}
            {{--                    <span class="svg-icon svg-icon-2"> --}}
            {{--                                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none" --}}
            {{--                                        xmlns="http://www.w3.org/2000/svg"> --}}
            {{--                                       <path --}}
            {{--                                           d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z" --}}
            {{--                                           fill="currentColor"/> --}}
            {{--                                   </svg> --}}
            {{--                    </span> --}}

            {{--                </a> --}}
            {{--            </div> --}}


        </div>
    </div>

@endsection
@section('content')



    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="t_container">
            <div class="card shadow-sm ">
                <div class="card-header">
                    <h3 class="card-title"></i> {{ trans('members.members_attendance') }}</h3>

                </div>

                <form id="save_form" method="post" action="{{ route('admin.subscriptions.MembersAttendance.store') }}"
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
                            <div class="col-md-4  mb-5">
                                <label class="required fs-6 fw-semibold mb-2">{{ trans('members.member_name') }}</label>
                                <select class="form-control " onchange="get_subscription()" data-control="select2"
                                    name="member_id" id="member_id">
                                    <option value=" ">{{ trans('forms.select') }}</option>

                                    @foreach ($members as $key)
                                        <option value="{{ $key->id }}" {{ old('member_id', $key->id) }}>
                                            {{ $key->member_name }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-4  mb-5">
                                <label class="required fs-6 fw-semibold mb-2">{{ trans('members.date') }}</label>
                                <input name="date" id="date" onchange="get_subscription()" type="date"
                                    class="form-control " value="{{ old('date', date('Y-m-d')) }}">
                            </div>







                        </div>


                        <div id="loader" style="display:none; text-align:center;">
                            <span class="spinner-border text-primary"></span>
                            <p>{{ trans('members.loading') }}</p>
                        </div>

                        <br>
                        <div id="member_subscription">

                        </div>




                    </div>




                </form>

            </div>


        </div>
    </div>





@stop
@section('js')


    <script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\Members\SaveMemberAttendance', '#save_form') !!}
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


    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.7.0/build/js/intlTelInput.min.js"></script>
    {{--  @if (app()->getLocale() == 'ar')
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
            hiddenInput: function(telInputName) {
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
        function get_subscription() {

            var date = $('#date').val();
            var id = $('#member_id').val();
            console.log('id' + id);
            $('#loader').show();
            $.ajax({
                url: '{{ route('admin.subscriptions.get_member_subscription') }}',
                type: 'get',
                data: {
                    id: id,
                    date: date,
                },
                success: function(data) {
                    $('#loader').hide();
                    $('#member_subscription').html(data);
                },
                error: function(xhr, status, error) {
                    $('#loader').hide();
                    console.error(error);
                }
            });
        }
    </script>


@endsection
