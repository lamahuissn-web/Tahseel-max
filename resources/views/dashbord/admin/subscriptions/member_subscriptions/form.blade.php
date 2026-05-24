@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{trans('sub.main_subscriptions')}}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                                                          class="text-muted text-hover-primary">{{trans('Toolbar.home')}}</a>
                </li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('Toolbar.subscriptions')}}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('sub.member_subscriptions')}}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('sub.add_new_subscription')}}</li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4"
                   href="{{route('admin.subscriptions.member_subscriptions.index')}}">
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



        <div id="kt_app_content_container" class="t_container">
            <div class="card shadow-sm ">
                <div class="card-header">
                    <h3 class="card-title"></i> {{trans('sub.add_new_subscription')}}</h3>

                </div>

                <form id="save_form" method="post" action="{{ route('admin.subscriptions.member_subscriptions.store') }}"
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

                            <div class="row" style="margin-top: 20px">

                                <div class="col-md-4  mb-5">
                                    <label class="required fs-6 fw-semibold mb-2">{{trans('members.member_name')}}</label>
                                    <select class="form-control " data-control="select2"
                                            name="member_id" id="member_id">
                                        <option value=" ">{{trans('forms.select')}}</option>

                                        @foreach($members as $key)
                                            <option value="{{$key->id}}" {{old('member_id',$key->id)}}> {{$key->member_name}}</option>
                                        @endforeach

                                    </select>
                                </div>
                                <div class="col-md-3  mb-5">
                                    <label
                                        class="required fs-6 fw-semibold mb-2">{{trans('members.pay_method')}}</label>
                                    <select onchange="pay_type(this)"
                                            class="form-control form-control-solid pay-method-select"
                                            name="pay_method" id="pay_method">
                                        <?php $pay_method_arr = ['cache' => trans('members.cache'), 'visa' => trans('members.visa'), 'bank' => trans('members.bank')] ?>
                                        <option value=" ">{{trans('forms.select')}}</option>
                                        @foreach($pay_method_arr as $key=>$value)
                                            <option value="{{$key}}"> {{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3  mb-5 transfer-image-dev" style="display: none">
                                    <label
                                        class="required fs-6 fw-semibold mb-2">{{trans('members.transfer_image')}}</label>
                                    <input class="form-control form-control-solid" type="file"
                                           name="transfer_image" id="transfer_image" accept="image/*">
                                </div>




                            </div>


                            <br>
                            <hr>
                            <input type="hidden" name="process_num" value="{{$process_num+1}}">
                            <div id="kt_docs_repeater_advanced">
                                <!--begin::Form group-->
                                <div class="form-group">
                                    <div data-repeater-list="kt_docs_repeater_advanced">
                                        <div data-repeater-item>
                                            <div class="row ">
                                                <div class="col-md-9">
                                                    <div class="form-group row mb-5">
                                                        <div class="row" style="margin-top: 10px">
                                                            <div class="col-md-3  mb-5">
                                                                <label class="form-label">{{trans('members.category')}}</label>
                                                                <select onchange="get_subscription(this)"
                                                                        class="form-control form-control-solid type-select"
                                                                        name="type" id="type">
                                                                    <option value=" ">{{trans('forms.select')}}</option>
                                                                    @php $cat_arr=['main'=>trans('members.main_subscription'),'special'=>trans('members.special_subscription')] @endphp
                                                                    @foreach($cat_arr as $key=>$value)
                                                                        <option value="{{$key}}"> {{$value}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3  mb-5">
                                                                <label
                                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.subscription')}}</label>
                                                                <select onchange="get_sub_details(this)"
                                                                        class="form-control form-control-solid subscription-select"
                                                                        name="subscription_id" id="subscription_id">
                                                                    <option value=" ">{{trans('forms.select')}}</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3  mb-5">
                                                                <label
                                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.subscription_start_date')}}</label>
                                                                <input onchange="get_sub_details(this)"
                                                                       class="form-control form-control-solid"
                                                                       name="start_date"
                                                                       type="date"
                                                                       value="{{date('Y-m-d')}}" min="{{date('Y-m-d')}}" id="start_date"/>

                                                            </div>
                                                            <div class="col-md-3  mb-5" id="end_date_dev" style="display: block">
                                                                <label
                                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.subscription_end_date')}}</label>
                                                                <input class="form-control form-control-solid"
                                                                       name="end_date"
                                                                       type="date"
                                                                       value="" id="end_date" readonly/>

                                                            </div>
                                                            <div class="col-md-3  mb-5">
                                                                <label class="custom-label fs-6 fw-semibold mb-2">{{ trans('members.transportation') }}</label>

                                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                                    <input class="form-check-input" name="transportation" type="checkbox" value="yes" id="flexSwitchDefault"/>
                                                                    <label class="form-check-label" for="flexSwitchDefault">
                                                                        {{trans('members.transportation_sub')}}
                                                                    </label>
                                                                </div>

                                                            </div>

                                                            <div class="col-md-3  mb-5" id="trainer_dev" style="display: none">
                                                                <label
                                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.trainers')}}</label>
                                                                <select class="form-control form-control-solid"
                                                                        name="trainer_id" id="trainer_id">
                                                                    <option value=" ">{{trans('forms.select')}}</option>

                                                                    @foreach($trainers as $key)
                                                                        <option value="{{$key->id}}"> {{$key->user_name}}</option>
                                                                    @endforeach

                                                                </select>
                                                            </div>

                                                            <div class="col-md-4  mb-5">
                                                                <label
                                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.discount')}}
                                                                    (<span
                                                                        style="color: darkred" id="max_discount"></span>)</label>
                                                                <input onkeyup="checkMaxDiscount(this)" class="form-control form-control-solid"
                                                                       name="discount"
                                                                       type="number" step="any"
                                                                       value="" id="discount"/>
                                                            </div>
                                                            <input type="hidden" id="max_sub_dicount" value="">



                                                            <input type="hidden" name="duration" id="duration">
                                                        </div>





                                                        <div  class="d-flex align-items-center gap-2 gap-lg-3">
                                                            <div class="d-flex">
                                                            <a href="javascript:;" data-repeater-delete
                                                               class="btn btn-sm btn-icon btn-light-danger mt-3 mt-md-9 flex-shrink-0 ms-4">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                        </div>


                                                    </div>
                                                </div>

                                                <div class="col-md-3  mb-5">
                                                   <div class="row">
                                                       <div class="flex-column flex-lg-row-auto ">
                                                           <!--begin::Card-->
                                                           <div class="card mb-5 mb-xl-8 border border-primary">
                                                               <!--begin::Card body-->
                                                               <div class="card-body pt-15">
                                                                   <!--begin::Summary-->
                                                                   <div class="d-flex flex-center flex-column mb-5">
                                                                     <span id="sub_name" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">

                                                                     </span>
                                                                       <!--end::Name-->
                                                                       <!--begin::Info-->
                                                                   </div>
                                                                   <!--end::Details toggle-->
                                                                   <div class="separator separator-dashed my-3"></div>
                                                                   <!--begin::Details content-->
                                                                   <div id="kt_customer_view_details" class="collapse show">
                                                                       <div class="py-5 fs-6">
                                                                           <div class="d-flex align-items-center mt-5">
                                                                               <div class="fw-bold">{{ trans('sub.duration') }}:</div>
                                                                               <span id="sub_duration" class="text-gray-600 ms-2"></span>
                                                                           </div>
                                                                           <div class="d-flex align-items-center mt-5">
                                                                               <div class="fw-bold">{{ trans('sub.price') }}:</div>
                                                                               <span id="sub_price" class="text-gray-600 ms-2"></span>
                                                                           </div>
                                                                           <div class="d-flex align-items-center mt-5">
                                                                               <div class="fw-bold">{{ trans('sub.max_discount') }} (%):</div>
                                                                               <span id="sub_discount" class="text-gray-600 ms-2"></span>
                                                                           </div>
                                                                       </div>
                                                                   </div>

                                                                   <!--end::Details content-->
                                                               </div>
                                                               <!--end::Card body-->
                                                           </div>

                                                           <!--end::Card-->

                                                       </div>

                                                   </div>

                                                </div>

                                            </div>

                                            <br>
                                            <hr>
                                        </div>

                                    </div>
                                </div>
                                <!--end::Form group-->
                                <!--begin::Form group-->

                                <div  class="d-flex align-items-center gap-2 gap-lg-3">
                                    <div class="d-flex">
                                        <a data-repeater-create
                                           class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4">
                                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                                            <span class="svg-icon svg-icon-2">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
														<rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                                                              rx="1" transform="rotate(-90 11.364 20.364)"
                                                              fill="currentColor"/>
														<rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                                              fill="currentColor"/>
                                                    </svg>
					                           </span>

                                        </a>
                                    </div>


                                </div>




                                <!--end::Form group-->
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


        </div>











@stop
@section('js')




    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\subscription\member_subscriptions\SaveMemberSubscriptions', '#save_form') !!}
    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>
    <script src="{{asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js')}}"></script>
    <script>
        var KTAppBlogSave = function () {
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
                    initTagify();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppBlogSave.init();
        });

    </script>


  {{--  <script>
        function get_subscription(type,subscription_id) {
            console.log('subscription_id'+subscription_id);
            $.ajax({
                url: '{{route('admin.get-subscription')}}',
                type: 'get',
                data: {
                    type: type,
                },
                success: function (response) {
                    $('#subscription_id').empty();
                    $('#subscription_id').append('<option>{{ trans('forms.select') }}</option>');
                    var currentLocale = '{{ app()->getLocale() }}';
                    response.forEach(function (subscription) {
                        var name = subscription.name[currentLocale]; // Access the translation for the current locale
                        $('#subscription_id').append('<option value="' + subscription.id + '">' + name + '</option>');
                        if(subscription_id !=' ')
                        {
                            $('#subscription_id2').append('<option value="' + subscription.id + '">' + name + '</option>');
                            $('#subscription_id').val(subscription_id);
                        }

                    });

                    if (type == 'special')
                    {
                        $('#trainer_dev').show()
                        $('#end_date_dev').hide()
                    }else {
                        $('#trainer_dev').hide()
                        $('#end_date_dev').show()
                    }
                },
                error: function (xhr, status, error) {
                    // Handle any errors here
                    console.error(error);
                }
            });
        }

    </script>

    <script>
        function get_sub_details(id)
        {
            var type=$('#type').val();
            var subscription_id=$('#subscription_id').val();
            var start_date=$('#start_date').val();
            $.ajax({
                url: '{{route('admin.get-subscription-details')}}',
                type: 'get',
                data: {
                    type: type,
                    id: subscription_id,
                    start_date: start_date,
                },
                success: function (response) {

                    console.log(response.subscription.max_discount);
                    if(type == 'main')
                    {
                        $('#end_date').val(response.end_date);
                    }

                    $('#max_discount').text('{{ trans('members.max_discount') }}'+' ' +response.subscription.max_discount+ ' % ');


                },
                error: function (xhr, status, error) {

                    console.error(error);
                }
            });
        }
    </script>

    <script>
        function pay_type(id)
        {
            if (id == 'bank')
            {
                $('#transfer_image_dev').show();
            }else{
                $('#transfer_image_dev').hide();
            }
        }
    </script>--}}

    <script>
        function get_subscription(element) {
            var $repeaterItem = $(element).closest('[data-repeater-item]');
            var type = $(element).val();
            var $subscriptionSelect = $repeaterItem.find('.subscription-select');
            var subscription_id = ' '; // Placeholder, adjust as needed

            console.log('subscription_id' + subscription_id);
            $.ajax({
                url: '{{route('admin.get-subscription')}}',
                type: 'get',
                data: {
                    type: type,
                },
                success: function (response) {
                    $subscriptionSelect.empty();
                    $subscriptionSelect.append('<option>{{ trans('forms.select') }}</option>');
                    var currentLocale = '{{ app()->getLocale() }}';
                    response.forEach(function (subscription) {
                        var name = subscription.name[currentLocale]; // Access the translation for the current locale
                        $subscriptionSelect.append('<option value="' + subscription.id + '">' + name + '</option>');
                        if (subscription_id != ' ') {
                            $subscriptionSelect.append('<option value="' + subscription.id + '">' + name + '</option>');
                            $subscriptionSelect.val(subscription_id);
                        }
                    });

                    if (type == 'special') {
                        $repeaterItem.find('#trainer_dev').show();
                        $repeaterItem.find('#end_date_dev').hide();
                    } else {
                        $repeaterItem.find('#trainer_dev').hide();
                        $repeaterItem.find('#end_date_dev').show();
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function get_sub_details(element) {
            var $repeaterItem = $(element).closest('[data-repeater-item]');
            var type = $repeaterItem.find('#type').val();
            var subscription_id = $repeaterItem.find('#subscription_id').val();
            var start_date = $repeaterItem.find('#start_date').val(); // Assuming there's a global start_date element
            $.ajax({
                url: '{{route('admin.get-subscription-details')}}',
                type: 'get',
                data: {
                    type: type,
                    id: subscription_id,
                    start_date: start_date,
                },
                success: function (response) {
                    console.log(response.subscription.max_discount);
                    if (type == 'main') {
                        $repeaterItem.find('#end_date').val(response.end_date);
                    }
                    $repeaterItem.find('#duration').val(response.subscription.duration);
                    $repeaterItem.find('#sub_duration').text(response.subscription.duration);
                    $repeaterItem.find('#sub_price').text(response.subscription.price);
                    $repeaterItem.find('#sub_discount').text(response.subscription.max_discount);
                    $repeaterItem.find('#max_sub_dicount').text(response.subscription.max_discount);
                    var local= '{{\Illuminate\Support\Facades\App::getLocale()}}';
                    $repeaterItem.find('#sub_name').text(response.subscription.name[local]);
                    $repeaterItem.find('#max_discount').text('{{ trans('members.max_discount') }}' + ' ' + response.subscription.max_discount + ' % ');
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }


        function pay_type(element) {
            var $repeaterItem = $(element).closest('[data-repeater-item]');
            var id = $(element).val();
            var $transferImageDev = $repeaterItem.find('.transfer-image-dev');

            if (id === 'bank') {
                $transferImageDev.show();
            } else {
                $transferImageDev.hide();
            }
        }


    </script>

    <script>
        function checkMaxDiscount(input) {
            var $repeaterItem = $(input).closest('[data-repeater-item]');
            var discount = parseFloat(input.value);
            var maxDiscount = parseFloat($repeaterItem.find('#max_discount').val());
            var $errorMessage = $repeaterItem.find('.error-message');

            if (isNaN(discount) || discount < 0) {
                $errorMessage.hide();
                return;
            }

            if (discount > maxDiscount) {
                $errorMessage.text(`Discount cannot be greater than ${maxDiscount}.`).show();
                input.value = ''; // Clear the input field
                $(input).css('border-color', 'red');
            } else {
                $errorMessage.hide();
                $(input).css('border-color', '');
            }
        }
    </script>

    <script>
        $('#kt_docs_repeater_advanced').repeater({
            initEmpty: false,

            defaultValues: {
                'text-input': 'foo'
            },

            show: function () {
                $(this).slideDown();


            },

            hide: function (deleteElement) {
                $(this).slideUp(deleteElement);
            },

            ready: function () {
                // Init select2


            }
        });
    </script>

@endsection
