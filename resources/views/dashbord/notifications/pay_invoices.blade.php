@extends('dashbord.layouts.master')
@section('css')
    @notifyCss
@endsection
@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid" >
        <div class="row col-md-12">
            <div class="col-md-3">
                @include('dashbord.notifications.sidebar')
            </div>
            <div class="col-md-9">
                <div id="kt_app_content" class="app-content flex-column-fluid" >
                    <div id="kt_app_content_container" class="" style="padding-top: 20px" >
                        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
                            <div class="card-header">
                                <h3 class="card-title">{{ trans('notifications.invoices_proccess') }}</h3>

                            </div>


                            <div class="card-body">
                                <div class="table-responsive" >
                                    <table id="table1" class="table table-bordered">
                                        <thead>
                                            <tr class="fw-bold fs-6 text-gray-800">
                                                <th style="width: 5%">{{ trans('notifications.id') }}</th>
                                                <th class="text-center">{{ trans('notifications.invoice_number') }}</th>
                                                <th class="text-center">{{ trans('notifications.message') }}</th>
                                                <th class="text-center">{{ trans('notifications.amount') }}</th>
                                                <th class="text-center">{{ trans('notifications.client_name') }}</th>
                                                <th>{{ trans('notifications.status') }}</th>
                                                <th class="text-center">{{ trans('notifications.month_year') }}</th>
                                                <th style="width: 20%; text-align: center">{{ trans('notifications.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="modaldetails">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><?= trans('invoices.invoice_details') ?></h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1">&times;</i>
                    </div>

                </div>

                <div id="result_info">

                </div>

            </div>
        </div>
    </div>

@endsection
@section('js')


    "use strict";
    <script type="text/javascript">
        var save_method; // For the save method string
        var table;
        var dt;

    </script>


    <script>
        "use strict";
        var KTDatatablesServerSide = function () {

            var initDatatable = function () {
                table = $("#table1").DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    order: [[0, 'desc']],
                    stateSave: true,
                    language: {
                        url: "{{ asset('assets/Arabic.json') }}"
                    },
                    ajax: {
                        url: "{{ route('admin.get_ajax_invoices_notifications') }}",
                    },
                    columns: [
                        { data: 'id', name: 'id' },
                        { data: 'invoice_number', name: 'invoice_number', orderable: false, searchable: false },
                        { data: 'message', name: 'message' },
                        { data: 'amount', name: 'amount', orderable: false, searchable: false },
                        { data: 'client', name: 'client', orderable: false, searchable: false },
                        { data: 'status', name: 'status' },
                        { data: 'month_year', name: 'month_year', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],

                    columnDefs: [

                        {
                            "targets": [1],
                            "createdCell": function(td, cellData, rowData, row, col) {
                                $(td).css({
                                    'font-weight': '600',
                                    'text-align': 'center',
                                    'color': '#6610f2',

                                    'vertical-align': 'middle',
                                });
                            }
                        },
                    ],

                });
            };

            return {
                init: function () {
                    initDatatable();
                }
            };
        }();

        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });

        function invoice_details(url) {
            $.get(url, function(data) {
                $('#result_info').html(data);
                $('#modaldetails').modal('show');
            });
        }
    </script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\Setting\GeneralSettingsRequest', '#form') !!}
    {{--  {!! JsValidator::formRequest('App\Http\Requests\Admin\Cases\CaseSettings', '#add_setting_form') !!} --}}
@endsection
