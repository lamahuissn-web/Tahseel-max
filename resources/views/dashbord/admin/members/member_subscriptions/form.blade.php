@php use Illuminate\Support\Facades\App; @endphp
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
                   href="{{route('admin.subscriptions.member-subscriptions.index')}}">
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

            @php
                if (empty($members_subscriptions))
                  {
                      $action=route('admin.subscriptions.member-subscriptions.store');
                      $member_id='';
                      $end_date='';
                      $start_date=date('Y-m-d');
                      $pay_method='';
                      $main_subscription_id='';
                      $main_discount='';
                      $package_price='';
                      $package_duration='';
                      $transportation='';
                      $transport_price='';
                      $transport_duration='';
                      $total_cost=0;
                      $disabled='';
                      $notes   ='';
                      $readonly='';
                      $discount_type='';
                      $free_days=0;


                   }
                   else{
                      $action=route('admin.subscriptions.add_additional_subscriptions',$members_subscriptions->id);
                      $member_id=$members_subscriptions->member_id;
                      $end_date=$members_subscriptions->end_date;
                      $start_date=$members_subscriptions->start_date;
                      $pay_method=$members_subscriptions->pay_method;
                      $main_subscription_id=$members_subscriptions->subscription_id;
                      $main_discount=$members_subscriptions->discount;
                      $package_price=$members_subscriptions->package_price;
                      $package_duration=$members_subscriptions->package_duration;
                      $transportation=$members_subscriptions->transport;
                      $transport_price=$members_subscriptions->transport_value;
                      $transport_duration=$members_subscriptions->transport_duration;
                      $total_cost=$members_subscriptions->total_cost;
                      $notes=$members_subscriptions->notes;
                      $discount_type=$members_subscriptions->discount_type;
                      $free_days=$members_subscriptions->free_days;
                      $readonly='readonly';
                      $disabled='disabled';

                   }
            @endphp


            <form id="StorForm" method="post" action="{{ $action }}" enctype="multipart/form-data">
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

                        <div class="col-md-3  mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.member_name')}}</label>
                            <select class="form-control " name="member_id" id="member_id"
                                    onchange="check_member_open_subscription(this.value)"
                                    data-control="select2" {{$disabled}} >
                                <option value="">{{trans('forms.select')}}</option>

                                @foreach($members as $key)
                                    <option
                                        value="{{$key->id}}"
                                        @if(old('member_id',$key->id)==$member_id) selected @endif> {{$key->member_name}}</option>
                                @endforeach

                            </select>
                            @error('member_id')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3  mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.main_subscription')}}</label>
                            <select onchange="get_sub_details_main(this.value)"
                                    class="form-control  subscription-select" data-control="select2" {{$disabled}}
                                    name="main_subscription_id" id="main_subscription_id">
                                <option value=" ">{{trans('forms.select')}}</option>
                                @foreach($main_subscriptions as $item)
                                    <option value="{{$item->id}}"
                                            @if(old('main_subscription_id',$item->id)==$main_subscription_id) selected @endif> {{$item->name}}</option>
                                @endforeach
                            </select>
                            @error('main_subscription_id')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2  mb-5">
                            <label
                                class="required fs-6 fw-semibold mb-2">{{trans('members.subscription_start_date')}}</label>
                            <input onchange="get_sub_details_main(this.value)"

                                   class="form-control datepicker " {{$readonly}}
                                   name="main_start_date" type="text"
                                   value="{{old('main_start_date',$start_date)}}" id="main_start_date"/>
                            @error('main_start_date')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>
                        <div class="col-md-2  mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.free_days')}}</label>
                            <input onchange="get_end_day(this.value)"
                                   class="form-control "
                                   name="free_days" type="number"
                                   value="{{old('free_days',$free_days)}}" id="free_days"/>
                            @error('free_days')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>
                        <div class="col-md-2 mb-5" id="end_date_dev" style="display: block">
                            <label
                                class="required fs-6 fw-semibold mb-2">{{trans('members.subscription_end_date')}}</label>
                            <input type="text" class="form-control " {{$readonly}} name="end_date"
                                   value="{{old('end_date',$end_date)}}" id="end_date" readonly/>
                            @error('end_date')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>


                    </div>


                    <div class="row mt-10">

                        <div class="col-md-2  mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.discount_type')}}</label>
                            <select  class="form-control" onchange="change_discount_type(this.value)" data-control="select2" name="discount_type" id="discount_type">
                                <?php $pay_method_arr = [1 => trans('members.percentage'), 2 => trans('members.value')] ?>
                                @foreach($pay_method_arr as $key=>$value)
                                    <option value="{{$key}}" @if(old('discount_type',$key)==$discount_type) selected @endif> {{$value}}</option>
                                @endforeach
                            </select>
                            @error('discount_type')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4  mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.discount')}}
                                (<span
                                    style="color: darkred" id="max_discount"></span>)</label>
                            <input onkeyup="checkMaxDiscount_main(this.value);get_main_cost()" class="form-control "
                                   name="main_discount" {{$readonly}}
                                   type="number" step="any" min="0"
                                   value="{{old('main_discount',$main_discount)}}" id="discount"/>
                            @error('discount')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden" name="main_discount_hidden" id="main_discount_hidden">
                        <input type="hidden" name="main_discount_value_hidden" id="main_discount_value_hidden">
                        <div class="col-md-2 mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.package_duration')}}</label>
                            <input class="form-control " {{$readonly}}
                            name="package_duration"
                                   type="text" step="any"
                                   value="{{old('package_duration',$package_duration)}}" id="package_duration"
                                   readonly/>
                            @error('package_duration')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2  mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.package_price')}}</label>
                            <input class="form-control "
                                   name="package_price" {{$readonly}}
                                   type="text" step="any"
                                   value="{{old('package_price',$package_price)}}" id="package_price" readonly/>
                            @error('package_price')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2  mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.transportation')}}</label>
                            <select onchange="transport_type(this.value)" {{$disabled}}
                            class="form-control" data-control="select2"
                                    name="transportation" id="transportation">
                                <?php $pay_method_arr = ['yes' => trans('members.subscribed'), 'no' => trans('members.not_subscribed')] ?>
                                <option value="">{{trans('forms.select')}}</option>
                                @foreach($pay_method_arr as $key=>$value)
                                    <option value="{{$key}}"
                                            @if(old('transportation',$key)==$transportation) selected @endif> {{$value}}</option>
                                @endforeach
                            </select>
                            @error('transportation')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row" style="margin-top: 10px">

                        <div class="col-md-3 mb-5">
                            <label
                                class="required fs-6 fw-semibold mb-2">{{trans('members.transport_duration')}}</label>
                            <input class="form-control " {{$readonly}}
                            name="transport_duration"
                                   type="text" step="any"
                                   value="{{old('transport_duration',$transport_duration)}}" id="transport_duration"
                                   readonly/>
                            @error('transport_duration')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3  mb-5">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('members.transport_price')}}</label>
                            <input class="form-control " {{$readonly}}
                            name="transport_price"
                                   type="text" step="any"
                                   value="{{old('transport_price',$transport_price)}}" id="transport_price" readonly/>
                            @error('transport_price')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="hidden" id="transport_duration_hidden" value="{{$transport_duration}}">
                        <input type="hidden" id="transport_price_hidden" value="{{$transport_price}}">


                        <div class="col-md-3  mb-5">
                            <label
                                class="required fs-6 fw-semibold mb-2">{{trans('members.pay_method')}}</label>
                            <select onchange="pay_type1(this)" data-control="select2" {{$disabled}}
                            class="form-control  pay-method-select"
                                    name="pay_method" id="pay_method">
                                <?php $pay_method_arr = ['cache' => trans('members.cache'), 'visa' => trans('members.visa'), 'bank' => trans('members.bank'), 'tabby' => trans('members.tabby')] ?>
                                <option value="">{{trans('forms.select')}}</option>
                                @foreach($pay_method_arr as $key=>$value)
                                    <option value="{{$key}}"
                                            @if((old('pay_method')==$pay_method)&&(!empty(old('pay_method')))) selected @endif> {{$value}}</option>
                                @endforeach
                            </select>

                            @error('pay_method')
                            <div
                                class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3  mb-5 transfer-image-dev" style="display: none">
                            <label
                                class="required fs-6 fw-semibold mb-2">{{trans('members.transfer_image')}}</label>
                            <input class="form-control " type="file"
                                   name="transfer_image" id="transfer_image" accept="image/*">
                        </div>

                    </div>



                    <div>
                        <input type="hidden" name="total_cost_main" id="total_cost_main">
                        <input type="hidden" name="total_cost_sub" id="total_cost_sub">
                        <div class="col-md-3  mb-5 ">
                            <label
                                class="required fs-6 fw-semibold mb-2">{{trans('members.total_cost')}}</label>
                            <input class="form-control " type="number" value="{{old('total_cost',$total_cost)}}"
                                   name="total_cost" id="total_cost" readonly>

                        </div>
                    </div>


                    <br>
                    <h3 class="card-title"></i> {{trans('sub.additional_subscriptions')}}</h3>
                    <hr>


                    <input type="hidden" name="process_num" value="{{$process_num+1}}">
                    <div id="kt_docs_repeater_advanced">
                        <!--begin::Form group-->
                        <div class="form-group">
                            <div data-repeater-list="kt_docs_repeater_advanced">
                                <div data-repeater-item>
                                    <div class="form-group row mb-5">
                                        <div class="row" style="margin-top: 10px">
                                            {{--                                            <input type="hidden" name="type" id="type" value="special"  onkeyup="get_subscription(this)">--}}
                                            <div class="col-md-2  mb-5">
                                                <label class="form-label">{{trans('members.category')}}</label>
                                                <select onchange="get_subscription(this)"
                                                        class="form-control  type-select"
                                                        name="type" id="type">
                                                    <option value=" ">{{trans('forms.select')}}</option>
                                                    @php $cat_arr=['special'=>trans('members.special_subscription')] @endphp
                                                    @foreach($cat_arr as $key=>$value)
                                                        <option value="{{$key}}"> {{$value}}</option>
                                                    @endforeach
                                                </select>
                                                @error('type')
                                                <div
                                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-2  mb-5">
                                                <label
                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.subscription')}}</label>
                                                <select onchange="get_sub_details(this)"
                                                        class="form-control  subscription-select"
                                                        name="subscription_id" id="subscription_id">
                                                    <option value=" ">{{trans('forms.select')}}</option>
                                                </select>
                                                @error('subscription_id')
                                                <div
                                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-2  mb-5">
                                                <label
                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.subscription_start_date')}}</label>
                                                <input type="text" onchange="check_start_date(this)"
                                                       class="form-control datepicker "
                                                       name="start_date"


                                                       value="{{date('Y-m-d')}}" min="{{date('Y-m-d')}}"
                                                       id="start_date"/>

                                                @error('start_date')
                                                <div
                                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                @enderror

                                            </div>
                                            <div class="col-md-2  mb-5" id="trainer_dev">
                                                <label
                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.trainers')}}</label>
                                                <select class="form-control "
                                                        name="trainer_id" id="trainer_id">
                                                    <option value=" ">{{trans('forms.select')}}</option>

                                                    @foreach($trainers as $key)
                                                        <option value="{{$key->id}}"> {{$key->user_name}}</option>
                                                    @endforeach

                                                </select>
                                                @error('trainer_id')
                                                <div
                                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-1 mb-5">
                                                <label
                                                    class="required fs-6 fw-semibold mb-2">{{trans('sub.duration')}}</label>
                                                <input type="number" readonly class="form-control " name="duration"
                                                       id="duration">

                                                @error('duration')
                                                <div
                                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                @enderror

                                            </div>
                                            <div class="col-md-1 mb-5">
                                                <label
                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.cost')}}</label>
                                                <input type="number" readonly class="form-control p-3 sub2cost "
                                                       name="cost"
                                                       id="cost">

                                                @error('cost')
                                                <div
                                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                @enderror

                                            </div>
                                            <div class="col-md-2  mb-5">
                                                <label class="required fs-6 fw-semibold mb-2">{{trans('members.sub_discount_type')}}</label>
                                                <select  class="form-control" onchange="change_sub_discount_type(this)"  data-control="select2" name="sub_discount_type" id="sub_discount_type">
                                                    <?php $pay_method_arr = [1 => trans('members.percentage'), 2 => trans('members.value')] ?>
                                                    @foreach($pay_method_arr as $key=>$value)
                                                        <option value="{{$key}}"> {{$value}}</option>
                                                    @endforeach
                                                </select>
                                                @error('sub_discount_type')
                                                <div
                                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3 mb-5">
                                                <label
                                                    class="required fs-6 fw-semibold mb-2">{{trans('members.discount')}}
                                                    (<span class="text-danger text-sm"
                                                           style="/*color: darkred*/" id="max_discount"></span>)</label>
                                                <input onkeyup="checkMaxDiscount(this);get_sub_cost(this)"
                                                       class="form-control "
                                                       name="discount" min="0"
                                                       type="number" step="any"
                                                       value="" id="discount"/>

                                                @error('discount')
                                                <div
                                                    class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <input type="hidden" id="max_sub_dicount" name="max_sub_dicount" value="">
                                            <input type="hidden" id="max_sub_dicount_value" name="max_sub_dicount_value" value="">


                                            <div class="col-md-1 d-flex align-items-center gap-2 gap-lg-3">
                                                <div class="d-flex">
                                                    <a href="javascript:" data-repeater-delete
                                                       class="btn btn-sm btn-icon btn-light-danger mt-3 mt-md-9 flex-shrink-0 ms-4">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
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

                        <div class="d-flex align-items-center gap-2 gap-lg-3">
                            <div class="d-flex">
                                <a data-repeater-create id="create-repeater-btn"
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
                                <span id="danger_msg" style="margin-right: 20px" class="text-danger"></span>

                            </div>


                        </div>


                        <!--end::Form group-->
                    </div>

                        <div class="row" style="margin-top: 10px">
                            <div class="col-md-12  mb-5 ">
                                <label class="required fs-6 fw-semibold mb-2">{{trans('members.notes')}}</label>
                                <textarea class="form-control " type="text" name="notes"
                                          id="notes">{{old('notes',$notes)}}</textarea>
                            </div>
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

@endsection
@section('js')

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Admin\subscription\member_subscriptions\SaveMemberSubscriptions', '#StorForm') !!}



    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>
    <script src="{{asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js')}}"></script>

    <script>
        function check_member_open_subscription(member_id) {
            $.ajax({
                url: '{{ route('admin.check_member_open_subscription') }}',
                type: 'get',
                data: {
                    member_id: member_id,
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            title: "{{ trans('members.member_open_subscription') }}",
                            icon: "warning",
                            iconHtml: "؟",
                            confirmButtonText: "{{ trans('forms.action_done') }}",
                        }).then(() => {
                            // Reset the select input after SweetAlert is closed
                            $('#member_id').val(null).trigger('change');
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }


    </script>


    <script>
        var KTAppBlogSave = function () {

            ClassicEditor
                .create(document.querySelector('#notes'))
                .then(editor => {
                    console.log(editor);
                })
                .catch(error => {
                    console.error(error);
                });
            // Init daterangepicker
            const initDaterangepicker = () => {

                $(".datepicker").daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoApply: true,
                    {{--minDate: "{{date('Y-m-d')}}",--}}
                    minYear: 2024,
                    locale: {
                        format: "YYYY-MM-DD"
                    },
                    maxYear: parseInt(moment().format("YYYY"), 12)
                });

            }

            // Public methods
            return {
                init: function () {
                    // Init forms
                    initDaterangepicker();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppBlogSave.init();

        });

    </script>
    <script>
        $(document).ready(function () {
            checkMainSubscription();
            $('#main_subscription_id').on('change', function () {
                checkMainSubscription();
            });


        });
        /* $(document).on('click', '.create-repeater-btn', function() {
             // Find the closest element containing the hidden input with name="type"
             var closestTypeInput = $(this).find('input[type="hidden"][name="type"]');

             // Trigger the change event on the found input
             closestTypeInput.trigger('keyup');

             // Call the get_subscription function with the closest input
             get_subscription(closestTypeInput);
         });*/


    </script>



    <script>
        function checkMaxDiscount_main(input) {
            var max_discount = parseFloat($('#main_discount_hidden').val());
            var max_discount_value = parseFloat($('#main_discount_value_hidden').val());
            var current_value = parseFloat(input);

            var discount_type=$('#discount_type').val();


            console.log('max_discount : ' + max_discount)
            console.log('current_value : ' + current_value)

            if (discount_type == 1){
                if (current_value > max_discount) {
                    Swal.fire({
                        title: "{{trans('members.max_discount_message')}}",
                        icon: "warning",
                        iconHtml: "؟",
                        confirmButtonText: "{{trans('forms.action_done')}}",
                    });
                    $('#discount').val(max_discount);


                    /* if (confirm('The discount value cannot exceed ' + max_discount + '. Do you want to set it to the maximum allowed?')) {
                         $('#discount').val(max_discount);
                     } else {
                         $('#discount').val(0);
                     }*/
                }
            }else{
                if (current_value > max_discount_value) {
                    Swal.fire({
                        title: "{{trans('members.max_discount_message')}}",
                        icon: "warning",
                        iconHtml: "؟",
                        confirmButtonText: "{{trans('forms.action_done')}}",
                    });
                    $('#discount').val(max_discount_value);


                    /* if (confirm('The discount value cannot exceed ' + max_discount + '. Do you want to set it to the maximum allowed?')) {
                         $('#discount').val(max_discount);
                     } else {
                         $('#discount').val(0);
                     }*/
                }
            }

        }
    </script>

    <script>
        function get_sub_details_main(id) {
            var type = 'main';
            var subscription_id = $('#main_subscription_id').val()
            var start_date = $('#main_start_date').val();
            var transportation = $('#transportation').val();
            var free_days = parseInt($('#free_days').val(), 10);

            if (subscription_id) {
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

                            var endDate = new Date(response.end_date);
                            endDate.setDate(endDate.getDate() + free_days);
                            var newEndDate = endDate.toISOString().split('T')[0];
                            $('#end_date').val(newEndDate);
                            // $('#end_date').val(response.end_date);
                            // $('#end_date').data('daterangepicker').setStartDate(response.end_date);

                            $('#package_duration').val(response.subscription.duration);
                            $('#package_price').val(response.subscription.price);
                            $('#main_discount_hidden').val(response.subscription.max_discount);
                            $('#main_discount_value_hidden').val((response.subscription.max_discount / 100) * response.subscription.price);


                            if (transportation == 'yes') {
                                $('#transport_duration').val(response.subscription.duration);
                                $('#transport_price').val(response.transport_price);

                            } else {
                                $('#transport_duration').val(0);
                                $('#transport_price').val(0);
                            }

                            $('#transport_duration_hidden').val(response.subscription.duration);
                            $('#transport_price_hidden').val(response.transport_price);
                            get_main_cost()
                        }

                        let discountValue = (response.subscription.max_discount / 100) * response.subscription.price;

                        $('#max_discount').text(response.subscription.max_discount + '% - {{trans('members.value')}}' + discountValue.toFixed(2));


                    },
                    error: function (xhr, status, error) {

                        console.error(error, '22');
                    }
                });
            }
        }
    </script>

    <script>
        function transport_type(type) {
            var duration = $('#transport_duration_hidden').val();
            var price = $('#transport_price_hidden').val();
            if (type == 'no') {
                $('#transport_duration').val(0);
                $('#transport_price').val(0);
                get_main_cost()

            } else {
                $('#transport_duration').val(duration);
                $('#transport_price').val(price);
                get_main_cost()
            }

        }
    </script>

    <script>
        function pay_type1(id) {
            console.log('id' + id.value);
            if (id.value == 'cache' || id.value == '') {
                $('.transfer-image-dev').hide();
            } else {
                $('.transfer-image-dev').show();
            }
        }
    </script>

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
            if (subscription_id) {
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
                        $repeaterItem.find('#max_sub_dicount').val(response.subscription.max_discount);

                        $repeaterItem.find('#cost').val(response.subscription.price);

                        var local = '{{App::getLocale()}}';
                        $repeaterItem.find('#sub_name').text(response.subscription.name[local]);
                        let discountValue = (response.subscription.max_discount / 100) * response.subscription.price;
                        $repeaterItem.find('#max_sub_dicount_value').val(discountValue);
                        //$('#max_discount').text(response.subscription.max_discount + '% - {{trans('members.value')}}' + discountValue.toFixed(2));

                        $repeaterItem.find('#max_discount').text(response.subscription.max_discount + '% - {{trans('members.value')}}' + discountValue.toFixed(2));
                        get_sub_cost(element)
                    },
                    error: function (xhr, status, error) {
                        console.error(error, '11');
                    }
                });
            }
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

            var discount_type = parseFloat($repeaterItem.find('#sub_discount_type').val());
            var maxDiscount = parseFloat($repeaterItem.find('#max_sub_dicount').val());
            var maxDiscount_value = parseFloat($repeaterItem.find('#max_sub_dicount_value').val());
            var $errorMessage = $repeaterItem.find('.error-message');

            console.log('discount' + discount);
            console.log('maxDiscount' + maxDiscount);

            if (isNaN(discount) || discount < 0) {
                $errorMessage.hide();
                return;
            }

           if (discount_type == 1)
           {
               if (discount > maxDiscount) {


                   Swal.fire({
                       title: "{{trans('members.max_discount_message')}}",
                       icon: "warning",
                       iconHtml: "؟",
                       confirmButtonText: "{{trans('forms.action_done')}}",
                   });
                   $repeaterItem.find('#discount').val(maxDiscount);

                   /*  if (confirm('The discount value cannot exceed ' + max_discount + '. Do you want to set it to the maximum allowed?')) {
                         $repeaterItem.find('#discount').val(maxDiscount);
                     } else {
                         $repeaterItem.find('#sub_duration').val(0);
                     }*/
               }
           }else {
               if (discount > maxDiscount_value) {


                   Swal.fire({
                       title: "{{trans('members.max_discount_message')}}",
                       icon: "warning",
                       iconHtml: "؟",
                       confirmButtonText: "{{trans('forms.action_done')}}",
                   });
                   $repeaterItem.find('#discount').val(maxDiscount_value);

                   /*  if (confirm('The discount value cannot exceed ' + max_discount + '. Do you want to set it to the maximum allowed?')) {
                         $repeaterItem.find('#discount').val(maxDiscount);
                     } else {
                         $repeaterItem.find('#sub_duration').val(0);
                     }*/
               }
           }




        }
    </script>

    <script>
        $('#kt_docs_repeater_advanced').repeater({
            initEmpty: true,

            defaultValues: {
                'text-input': 'foo'
            },

            show: function () {
                $(this).slideDown();

                checkMainSubscription();
                $(".datepicker").daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoApply: true,
                    {{--minDate: "{{date('Y-m-d')}}",--}}
                    minYear: 2024,
                    locale: {
                        format: "YYYY-MM-DD"
                    },
                    maxYear: parseInt(moment().format("YYYY"), 12)
                });
            },

            hide: function (deleteElement) {
                $(this).slideUp(deleteElement);
            },

            ready: function () {
                // Init select2
            }
        });
    </script>

    <script>
        function checkMainSubscription() {
            var mainSubscriptionId = $('#main_subscription_id').val();
            if (mainSubscriptionId && mainSubscriptionId !== ' ') {
                $('#create-repeater-btn').removeClass('disabled');
                $('#danger_msg').text('');
            } else {
                $('#create-repeater-btn').addClass('disabled');
                $('#danger_msg').text('{{trans('members.you_should_choose_main_subscription_first')}}');
            }
        }
    </script>
    <script>
        function check_start_date(element) {
            var $repeaterItem = $(element).closest('[data-repeater-item]');
            var start_date = $(element).val();
            var end_date = $('#end_date').val();
            /*   // Convert dates from 'd-m-Y' format to 'Y-m-d' for comparison
               var startDateParts = start_date.split('-');
               var endDateParts = end_date.split('-');

             // Create Date objects in the format 'YYYY-MM-DD'
               var startDateObj = new Date(startDateParts[2], startDateParts[1] - 1, startDateParts[0]); // Year, Month (0-based), Day
               var endDateObj = new Date(endDateParts[2], endDateParts[1] - 1, endDateParts[0]); // Year, Month (0-based), Day
   */
            // Convert start_date and end_date to Date objects
            var startDateObj = new Date(start_date);
            var endDateObj = new Date(end_date);

            console.log(start_date, end_date, startDateObj, endDateObj);

            // Check if start_date is greater than end_date
            if (startDateObj > endDateObj) {
                /* if (start_date > end_date) {*/
                Swal.fire({
                    text: "{{trans('members.start_date_should_be_less_than_end_date')}}?",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "{{trans('forms.ok')}}",
                    cancelButtonText: "{{trans('forms.action_no')}}",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(element).val(''); // Clear the start_date field
                    }
                });
            }
        }
    </script>

    <script>

        function get_main_cost() {
            var discount_type = $('#discount_type').val();
            var discount = parseFloat($('#discount').val()) || 0;
            var package_price = parseFloat($('#package_price').val()) || 0;
            var transport_price = parseFloat($('#transport_price').val()) || 0;

            console.log('discount_type++', discount_type);
            console.log('discount++', discount);
            console.log('package_price++', package_price);
            console.log('transport_price++', transport_price);

            if (discount_type == 1){
                var main_cost = (package_price + transport_price) * (1 - (discount / 100));
            }else{
                var main_cost = (package_price + transport_price - discount);
            }


            console.log('main_cost++', main_cost);

            $('#total_cost_main').val(main_cost.toFixed(2));
            get_total_cost()
            return main_cost;
        }

        function get_sub_cost(element) {
            var total_cost = 0;

            // Iterate over all repeater items

            $('[data-repeater-item]').each(function () {
                var $repeaterItem = $(this);
                var discount_type = $repeaterItem.find('#sub_discount_type').val();
                var discount = parseFloat($repeaterItem.find('#discount').val()) || 0;
                var package_price = parseFloat($repeaterItem.find('#cost').val()) || 0;
                if (discount_type ==1){
                    var sub_cost = package_price * (1 - (discount / 100));
                }else {
                    var sub_cost = package_price - discount;
                }

                total_cost += sub_cost;
            });
            $('#total_cost_sub').val(total_cost.toFixed(2));
            get_total_cost()
            return total_cost;
        }


        $(document).on('click', '[data-repeater-delete]', function () {
            var $repeaterItem = $(this).closest('[data-repeater-item]');
            var discount = parseFloat($repeaterItem.find('#discount').val()) || 0;
            var package_price = parseFloat($repeaterItem.find('#cost').val()) || 0;
            var sub_cost = package_price * (1 - (discount / 100));

            // Subtract the row's cost from the total
            var current_total = parseFloat($('#total_cost_sub').val()) || 0;
            var new_total = current_total - sub_cost;

            $('#total_cost_sub').val(new_total.toFixed(2));
            get_total_cost()
            // $('#total_cost').val(new_total.toFixed(2));
            // console.log('Row deleted. New total cost:', new_total.toFixed(2));
        });


        function get_total_cost() {
            var total_cost_sub = parseFloat($('#total_cost_sub').val()) || 0;
            var total_cost_main = parseFloat($('#total_cost_main').val()) || 0;

            var total_cost = total_cost_sub + total_cost_main;
            $('#total_cost').val(total_cost.toFixed(2));

        }
    </script>

    <script>
        function change_discount_type(value){
            console.log('sssssssss'+value)
            $('#discount').val('0');
             get_main_cost()
            // get_total_cost()

        }



        function change_sub_discount_type(input)
        {
            var $repeaterItem = $(input).closest('[data-repeater-item]');
            console.log('sssssssss'+$repeaterItem)
             $repeaterItem.find('#discount').val('0');
            get_sub_cost(input);
        }
    </script>

    <script>
        function get_end_day(number) {
            var type = 'main';
            var subscription_id = $('#main_subscription_id').val();
            var start_date = $('#main_start_date').val();
            var transportation = $('#transportation').val();
            var free_days = parseInt($('#free_days').val(), 10);

            if (subscription_id) {
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
                        if (type === 'main') {
                            var endDate = new Date(response.end_date);
                            endDate.setDate(endDate.getDate() + free_days);
                            var newEndDate = endDate.toISOString().split('T')[0];
                            $('#end_date').val(newEndDate);
                        }
                    }
                });
            }
        }
    </script>


@endsection
