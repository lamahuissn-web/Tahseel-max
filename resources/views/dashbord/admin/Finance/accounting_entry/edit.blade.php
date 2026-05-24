@extends('dashbord.layouts.master')

@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('accounting-entries.edit')}}</h1>
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
                    <a href="{{ route('admin.finance.accounting-entries.index') }}"
                       class="text-muted text-hover-primary"> {{trans('Toolbar.accounting-entries')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.accountEdit')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.finance.accounting-entries.index')}}"
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
              action="{{route('admin.finance.accounting-entries.update', $one_data->id)}}" method="post"
              enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="accounting_entry[id]" value="{{$one_data->id}}">

            <!--begin::Main column-->
            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <!--begin::General options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{trans('accounting_entry.mainData')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row row">
                            <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('accounting_entry.date_at')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="accounting_entry[date_at]" id="kt_daterangepicker"
                                       class="form-control mb-2  @error('date_at') is-invalid @enderror"
                                       placeholder="{{trans('accounting_entry.name')}}"
                                       value="{{old('date_at', $one_data->date_at)}}"/>
                                <!--end::Input-->
                                @error('date_at')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('accounting_entry.type')}}

                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="accounting_entry[type]"
                                        class="form-control mb-2  @error('type') is-invalid @enderror"
                                        data-control="select2" data-placeholder="{{trans('forms.Select')}}">
                                    <option value="0"> {{trans('accounting_entry.type')}}</option>
                                    @php
                                        $type_arr=['daily'=>trans('accounting_entry.daily'),'open'=>trans('accounting_entry.open'),'subscription'=>trans('accounting_entry.subscription')]
                                    @endphp
                                    @foreach($type_arr as $key=>$value)
                                        <option value="{{$key}}"
                                                @if($one_data->type == $key) selected @endif>{{$value}}</option>
                                    @endforeach
                                </select>
                                <!--end::Input-->
                                @error('type')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-10 fv-row row">
                            <div class="col">
                                <!--begin::Label-->
                                <label class="required form-label">{{trans('accounting_entry.notes')}}
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="accounting_entry[notes]"
                                       class="form-control mb-2  @error('notes') is-invalid @enderror"
                                       placeholder="{{trans('accounting_entry.notes')}}"
                                       value="{{old('notes', $one_data->notes)}}"/>
                                <!--end::Input-->
                                @error('notes')
                                <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <!-- Repeater Table -->
                        <table class="table table-bordered table-striped repeater">

                            <thead>
                            <tr>
                                <th>{{trans('accounting_entry.Account')}}</th>
                                <th>{{trans('accounting_entry.Amount')}}</th>
                                <th>{{trans('accounting_entry.line_Type')}}</th>
                                <th>{{trans('accounting_entry.line_notes')}}</th>
                                <th>{{trans('accounting_entry.line_Action')}}</th>
                            </tr>
                            </thead>
                            <tbody data-repeater-list="lines">
                            @foreach($one_data->lines as $line)

                                <tr data-repeater-item>
                                    <input type="hidden" name="id" value=""/> <!-- Add this hidden input -->

                                    <td>
                                        <select name="account_id" class="form-control accounts"
                                                data-placeholder="{{trans('forms.Select')}}">
                                            <option value="{{$line->account_id }}">{{ $line->account->name }}</option>

                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="amount" class="form-control amount"
                                               value="{{ $line->amount }}" onchange="updateTotal()">
                                    </td>
                                    <td>
                                        <select name="type" class="form-control select2 type" onchange="updateTotal()"
                                                data-control1="select2" data-placeholder="{{trans('forms.Select')}}">
                                            <option value="debtor"
                                                    @if($line->type == 'debtor') selected @endif>{{trans('accounting_entry.Debtor')}}</option>
                                            <option value="creditor"
                                                    @if($line->type == 'creditor') selected @endif>{{trans('accounting_entry.Creditor')}}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="notes" class="form-control" value="{{ $line->notes }}">
                                    </td>
                                    <td>
                                        <button type="button" data-repeater-delete
                                                class="btn btn-icon-danger btn-text-danger"><i
                                                class="fa fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>
                                    <button type="button" data-repeater-create
                                            class="btn btn-primary">{{trans('accounting_entry.add_new_row')}}</button>
                                </th>
                                <th><input type="number" class="form-control " readonly name="total" id="total-field">
                                </th>
                            </tr>
                            </tfoot>
                        </table>


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

@endsection
@section('js')
    <!--begin::Vendors Javascript(used for this page only)-->



    <script src="{{asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js')}}"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\finance\accounting_entry\AccountingEntryRequest', '#StorForm') !!}
    <script>
        var KTAppaccountSave = function () {

            var initRepeater = function () {
                $('.repeater').repeater({
                    initEmpty: false,
                    defaultValues: {
                        'account_id': '',
                        'amount': '',
                        'type': 'debtor'
                    },
                    show: function () {
                        $(this).slideDown();
                    },
                    hide: function (deleteElement) {
                        if (confirm("Are you sure you want to delete this row?")) {
                            $(this).slideUp(deleteElement);
                        }
                    }
                });

            }
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
                    // initRepeater();
                    initDaterangepicker();
                    // updateTotal();
                    // initSelectAccount();
                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppaccountSave.init();
        });
    </script>

    <script>
        function updateTotal() {
            let debtorsTotal = 0;
            let creditorsTotal = 0;

            // Iterate through each row
            $('tbody[data-repeater-list="lines"] tr').each(function () {
                let amount = parseFloat($(this).find('.amount').val()) || 0;
                let type = $(this).find('.type').val();
                console.log('amount', amount, 'type', type)

                if (type === 'debtor') {
                    debtorsTotal += amount;
                } else if (type === 'creditor') {
                    creditorsTotal += amount;
                }
            });
            console.log('creditorsTotal', creditorsTotal, 'debtorsTotal', debtorsTotal)
            // Calculate the total and set it in the hidden input field
            let total = debtorsTotal - creditorsTotal;
            $('#total-field').val(total);
        }

        function initSelectAccount_old() {
            $('.accounts').each(function () {
                var $selectElement = $(this);
                var accountId = $selectElement.data('account_id'); // Get the data-account_id value

                // Initialize select2 for the specific element
                $selectElement.select2({
                    ajax: {
                        url: '{{ route('admin.finance.getAaccount') }}',
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term, // Search term
                                page: params.page || 1,
                                selectedId: accountId // Include data-account_id in the request
                            };
                        },
                        processResults: function (data, params) {
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
            });

            // Optional: Handle keyup on a specific search input if necessary
            $('#search-input').on('keyup', function () {
                $('#select2-dropdown').empty().trigger('change');
            });
        }


        function initSelectAccount() {
            $('.accounts').select2({
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


        $(document).ready(function () {
            $('.repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'lines[account_id]': '',
                    'lines[amount]': '',
                    'lines[type]': ''
                },
                show: function () {
                    $(this).slideDown();
                    // $(this).find('.accounts').select2('destroy');
                    updateTotal(); // Recalculate totals
                    initSelectAccount();

                },
                hide: function (deleteElement) {
                    /* if (confirm("Are you sure you want to delete this row?")) {
                         $(this).slideUp(deleteElement);
                     }*/

                    Swal.fire({
                        title: "{{trans('forms.delete_quetion')}}",
                        showDenyButton: true,
                        showCancelButton: false,
                        confirmButtonText: "{{trans('forms.action_yes')}}",
                        denyButtonText: `{{trans('forms.action_no')}}`
                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {
                            $(this).slideUp(deleteElement);
                            updateTotal(); // Recalculate totals after removing

                        } else if (result.isDenied) {
                        }
                    });

                },
                ready: function () {
                    initSelectAccount();
                    updateTotal();

                }
            });
        });
    </script>
@endsection

