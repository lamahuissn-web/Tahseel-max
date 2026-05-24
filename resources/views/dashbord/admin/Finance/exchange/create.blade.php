@extends('dashbord.layouts.master')

@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('exchange.create')}}</h1>
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
                    {{trans('Toolbar.finance')}}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.finance.exchange.index') }}"
                       class="text-muted text-hover-primary"> {{trans('Toolbar.Exchange')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.CreateExchange')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.finance.exchange.index')}}"
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
              action="{{route('admin.finance.exchange.store')}}" method="post" enctype="multipart/form-data">
            @csrf

            <!--begin::Main column-->
            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <!--begin::General options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('exchange.mainData')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0 body_form">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row row">
                            <div class="col-md-4">
                                <label class="required form-label">{{trans('exchange.Type_of_payment')}}</label>
                                 <br>
                                <label>
                                <input type="radio" name="Exchange[type]" value="member" class="type-radio">{{trans('exchange.member')}}
                            </label>
                            <label>
                                <input type="radio" name="Exchange[type]" value="exchange" class="type-radio"> {{trans('exchange.exchange')}}
                            </label>
                        
                            <!-- الحقل الديناميكي -->
                            <div id="dynamic-select">
                                <!-- سيتم تحديث هذا الجزء بالـ AJAX -->
                            </div>
                            </div>
                            <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('exchange.date_at')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="Exchange[date_at]" id="kt_daterangepicker"
                                       class="form-control mb-2  @error('date_at') is-invalid @enderror"
                                       placeholder="" value="{{old('date_at')}}"/>
                                <!--end::Input-->
                                @error('Exchange[date_at]')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        {{--     <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('exchange.member')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="Exchange[member_id]" id="member_id"
                                        class="form-control @error('Exchange[member_id]') is-invalid @enderror"
                                        data-control="select2" data-dropdown-parent=".body_form"
                                        data-placeholder="{{trans('forms.Select')}}">

                                </select>
                                <!--end::Input-->
                                @error('Exchange[member_id]')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            {{--
                                                        <div class="col-md-4">
                                                            <!--begin::Label-->
                                                            <label class="required form-label">{{trans('exchange.accounts')}}

                                                            </label>
                                                            <!--end::Label-->
                                                            <!--begin::Input-->
                                                            <select name="Exchange[accounts_id]" id="accounts_id"
                                                                    class="form-control @error('Exchange[accounts_id]') is-invalid @enderror"
                                                                    data-control="select2" data-dropdown-parent=".body_form"
                                                                    data-placeholder="{{trans('forms.Select')}}">
                                                                --}}
                            {{-- @foreach ($accounts as $account)
                                                                     <option value="{{ $account->id }}">{{$account->code}}-{{ $account->name }}</option>
                                                                 @endforeach--}}{{--

                                                            </select>
                                                            <!--end::Input-->
                                                            @error('Exchange[account_id]')
                                                            <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                            --}}
                            <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('exchange.amount')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="number" name="Exchange[amount]" id=""
                                       min="0"
                                       class="form-control mb-2  @error('Exchange[amount]') is-invalid @enderror"
                                       placeholder="0"
                                       value="{{old('Exchange[amount]')}}"/>
                                <!--end::Input-->
                                @error('Exchange[amount]')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                           
                        </div>

                        <div class="mb-10 fv-row row">
                       {{--      <div class="col-md-3">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('exchange.payment')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="Exchange[payment_id]" id="payment_id"
                                        class="form-control @error('Exchange[payment_id]') is-invalid @enderror"
                                        data-control="select2" data-dropdown-parent=".body_form"
                                        data-placeholder="{{trans('forms.Select')}}">

                                </select>
                                <!--end::Input-->
                                @error('Exchange[payment_id]')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}

                            <div class="col-md-4 hidden"
                            id="select-member" style="display: none; >
                                <!--begin::Label-->
                                <label class="form-label">{{trans('exchange.member')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="member_id" id="member_id"
                                        class="form-control @error('member_id') is-invalid @enderror"
                                        data-control="select2" data-dropdown-parent=".body_form"
                                        data-placeholder="{{trans('forms.Select')}}">

                                </select>
                                <!--end::Input-->
                                @error('member_id')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> 
                            <div class="col-md-4 hidden"
                            id="select-expenditures" style="display: none;">
                                <!--begin::Label-->
                                <label class="form-label">{{trans('exchange.bnood')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="expenditures_id" id="expenditures_id"
                                        class="form-control @error('expenditures_id') is-invalid @enderror"
                                        data-control="select2" data-dropdown-parent=".body_form"
                                        data-placeholder="{{trans('forms.Select')}}">

                                </select>
                                <!--end::Input-->
                                @error('expenditures_id')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> 
                            <div class="col-8">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('exchange.notes')}}
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="Exchange[notes]"
                                       class="form-control mb-2  @error('notes') is-invalid @enderror"
                                       placeholder="{{trans('exchange.notes')}}" value="{{old('notes')}}"/>
                                <!--end::Input-->
                                @error('notes')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                         

                       


                    </div>
                    <!--end::Card header-->
                </div>
                <!--end::General options-->
                <!--begin::Meta options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('exchange.images')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="mb-10">
                            <!--begin::Label-->
                            <label class="form-label">{{trans('exchange.images')}}</label>
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

    <script src="{{asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js')}}"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\finance\Exchange\ExchangeRequest', '#StorForm') !!}
    <script>
        var KTAppaccountSave = function () {
            var initSelectpayment = function () {
                $('#payment_id').select2({
                    ajax: {
                        url: '{{ route('admin.finance.getPayment') }}',
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,// search term
                                page: params.page || 1
                            };
                        }, processResults: function (data, params) {
                            params.page = params.page || 1;
                            var mappedData = $.map(data.data, function (item) {
                                return {id: item.id, text: item.title, imageUrl: item.imageUrl};
                            });
                            return {
                                results: mappedData,
                                pagination: {
                                    more: (params.page * 10) < data.total
                                }

                            };
                        },
                        cache: true
                    },
                    placeholder: 'Select an option',
                    minimumInputLength: 0
                });

                $('#search-input').on('keyup', function () {
                    $('#select2-dropdown').empty().trigger('change');
                });
            }
            var initSelectAccount = function () {
                $('#accounts_id').select2({
                    ajax: {
                        url: '{{ route('admin.finance.getAaccount') }}',
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,// search term
                                page: params.page || 1
                            };
                        }, processResults: function (data, params) {
                            params.page = params.page || 1;
                            var mappedData = $.map(data.data, function (item) {
                                return {id: item.id, text: item.title, imageUrl: item.imageUrl};
                            });
                            return {
                                results: mappedData,
                                pagination: {
                                    more: (params.page * 10) < data.total
                                }

                            };
                        },
                        cache: true
                    },
                    placeholder: 'Select an option',
                    minimumInputLength: 0
                });

                $('#search-input').on('keyup', function () {
                    $('#select2-dropdown').empty().trigger('change');
                });
            }
            var initSelectMember = function () {
                $('#member_id').select2({
                    ajax: {
                        url: '{{ route('admin.Members.getMember') }}',
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,// search term
                                page: params.page || 1
                            };
                        }, processResults: function (data, params) {
                            params.page = params.page || 1;
                            var mappedData = $.map(data.data, function (item) {
                                return {id: item.id, text: item.name};
                            });
                            return {
                                results: mappedData,
                                pagination: {
                                    more: (params.page * 10) < data.total
                                }

                            };
                        },
                        cache: true
                    },
                    placeholder: 'Select an option',
                    minimumInputLength: 0
                });

                $('#search-input').on('keyup', function () {
                    $('#select2-dropdown').empty().trigger('change');
                });
            }
            var initSelectExpenditures = function () {
                $('#expenditures_id').select2({
                    ajax: {
                        url: '{{ route('admin.finance.getExpenditures') }}',
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,// search term
                                page: params.page || 1
                            };
                        }, processResults: function (data, params) {
                            params.page = params.page || 1;
                            var mappedData = $.map(data.data, function (item) {
                                return {
                            id: item.id,
                            text: item.text || item.name // عرض النص إذا كان موجودًا
                        };
                            });
                            return {
                                results: mappedData,
                                pagination: {
                                    more: (params.page * 10) < data.total
                                }

                            };
                        },
                        cache: true
                    },
                    placeholder: 'Select an option',
                    minimumInputLength: 0
                });
   
                $('#search-input').on('keyup', function () {
                    $('#select2-dropdown').empty().trigger('change');
                });
            }

              // Handle radio button change
              $('.type-radio').change(function () {
        let selectedType = $(this).val();

        if (selectedType === 'member') {
            $('#select-member').show();
            $('#select-expenditures').hide();
            initSelectMember(); // Initialize Select2 for members
        } else if (selectedType === 'exchange') {
            $('#select-expenditures').show();
            $('#select-member').hide();
            initSelectExpenditures(); // Initialize Select2 for exchanges
        }
   
});
            var initDaterangepicker = function () {
                $("#kt_daterangepicker").daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        minYear: 1901,
                        maxYear: parseInt(moment().format("YYYY"), 12),
                        autoApply: true,

                    }
                );
            }
            // Public methods
            return {
                init: function () {
                    // Init forms
                    initDaterangepicker();
                    initSelectMember();
                    initSelectpayment();
                    initSelectAccount();
                    initSelectExpenditures();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppaccountSave.init();
        });
    </script>



@endsection

