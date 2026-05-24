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
                                <h3 class="card-title">{{ trans('notifications.new_clients') }}</h3>
                                {{-- <div class="card-toolbar">
                                    <div class="text-center">
                                        <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSarfBands" >
                                            <i class="bi bi-plus fs-1"></i> {{ trans('settings.add') }}
                                        </a>
                                    </div>
                                </div> --}}
                            </div>


                            <div class="card-body">
                                <div class="table-responsive" >
                                    <table id="table1" class="table table-bordered">
                                        <thead>
                                            <tr class="fw-bold fs-6 text-gray-800">
                                                <th style="width: 5%">{{ trans('m') }}</th>
                                                <th class="text-center">{{ trans('notifications.message') }}</th>
                                                <th class="text-center">{{ trans('notifications.client_name') }}</th>
                                                <th class="text-center">{{ trans('notifications.start_date') }}</th>
                                                <th class="text-center">{{ trans('notifications.created_at') }}</th>
                                                <th class="text-center">{{ trans('notifications.status') }}</th>
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
                        url: "{{ route('admin.get_ajax_notifications') }}",
                    },
                    columns: [
                        { data: 'id', name: 'id' },
                        { data: 'message', name: 'message' },
                        { data: 'client_name', name: 'client_name', orderable: false, searchable: false },
                        { data: 'start_date', name: 'start_date' },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],

                    columnDefs: [

                        {
                            "targets": [1, 2, 3, 4, 5],
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
    </script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\Setting\GeneralSettingsRequest', '#form') !!}
    {{--  {!! JsValidator::formRequest('App\Http\Requests\Admin\Cases\CaseSettings', '#add_setting_form') !!} --}}
@endsection
