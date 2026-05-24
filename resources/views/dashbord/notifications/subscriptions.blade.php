@extends('dashbord.layouts.master')
@section('css')
    @notifyCss
@endsection
@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid" >
        <div class="row col-md-12">
            <div class="col-md-3">
                @include('dashbord.admin.settings.sidebar')
            </div>
            <div class="col-md-9">
                <div id="kt_app_content" class="app-content flex-column-fluid" >
                    <div id="kt_app_content_container" class="" style="padding-top: 20px" >
                        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
                            <div class="card-header">
                                <h3 class="card-title">{{ trans('settings.subscriptions') }}</h3>
                                <div class="card-toolbar">
                                    <div class="text-center">
                                        <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalsubscriptions" >
                                            <i class="bi bi-plus fs-1"></i> {{ trans('settings.add') }}
                                        </a>
                                    </div>
                                </div>
                            </div>


                            <div class="card-body">
                                <div class="table-responsive" >
                                    <table id="table1" class="table table-bordered">
                                        <thead>
                                            <tr class="fw-bold fs-6 text-gray-800">
                                                <th style="width: 5%">{{ trans('m') }}</th>
                                                <th style="text-align: center">{{ trans('settings.name') }}</th>
                                                <th style="text-align: center">{{ trans('settings.price') }}</th>
                                                <th style="text-align: center">{{ trans('settings.description') }}</th>
                                                <th style="width: 20%; text-align: center">{{ trans('settings.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" tabindex="-1" id="modalsubscriptions">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><?=trans('settings.add_subscription')?></h3>


                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1">&times;</i>
                    </div>

                </div>
                <form method="post" action="{{route('admin.add_subscription')}}" enctype="multipart/form-data" id="form">
                    @csrf
                    <input type="hidden" name="row_id" id="row_id" value="">
                    <div class="row col-md-12" style="margin: 10px">
                        <div class="col-md-11" >
                            <label for="name" class="form-label">{{trans('settings.name')}}</label>
                            <input type="text" class="form-control" name="name" id="name" value="" >
                        </div>
                    </div>
                    <div class="row col-md-12" style="margin: 10px">
                        <div class="col-md-11" >
                            <label for="price" class="form-label">{{trans('settings.price')}}</label>
                            <input type="number" class="form-control" name="price" id="price" value="" >
                        </div>
                    </div>
                    <div class="row col-md-12" style="margin: 10px">
                        <div class="col-md-11" >
                            <label for="number" class="form-label">{{trans('settings.description')}}</label>
                            <textarea class="form-control" name="description" id="description"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer" style="margin-top: 10px">
                        <button type="submit"  name="submit" value="add"  class="btn btn-primary">{{trans('settings.save')}} </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{trans('settings.cancel')}}</button>
                    </div>
                </form>


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
                        url: "{{ asset('assets/Arabic.json') }}" // Assuming the file is placed in the public directory
                    },
                    ajax: {
                        url: "{{ route('admin.get_ajax_subscriptions') }}",
                    },
                    columns: [
                        { data: 'id', className: 'text-center' },
                        { data: 'name', className: 'text-center' },
                        { data: 'price', className: 'text-center' },
                        { data: 'description', className: 'text-center' },
                        { data: 'action', className: 'text-center' },
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
                        {
                            "targets": [3,2],
                            "createdCell": function(td, cellData, rowData, row, col) {
                                $(td).css({
                                    'font-weight': '600',
                                    'text-align': 'center',
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


    <script>
        function edit_subscription(id)
        {
            $.ajax({
                url: "{{ route('admin.edit_subscription', ['id' => '__id__']) }}".replace('__id__', id),
                type: "get",
                dataType: "json",
                success: function (data) {
                    var allData = data.all_data;
                    // console.log(allData);
                    $('#row_id').val(allData.id);
                    $('#name').val(allData.name);
                    $('#price').val(allData.price);
                    $('#description').val(allData.description);

                },
            });
        }
    </script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\Setting\AddSubscriptionRequest', '#form') !!}
@endsection
