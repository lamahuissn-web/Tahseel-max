@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{ trans('sub.sub_settings') }}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                        class="text-muted text-hover-primary">{{ trans('Toolbar.home') }}</a></li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('Toolbar.subscriptions') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('Toolbar.settings') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('sidebar.' . $type) }}</li>
            </ul>
        </div>


        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4" data-bs-toggle="modal"
                    data-bs-target="#exampleModal" onclick="add_setting()">
                    {{--                    <i class="bi bi-plus">{{trans('sub.add_new_subscription')}}</i> --}}
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                transform="rotate(-90 11.364 20.364)" fill="currentColor" />
                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="currentColor" />
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                </a>
            </div>
        </div>

    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="app-container container-xxl">
        <div class="card card-flush">
            <div class="card-header align-items-center py-3 gap-2 gap-md-1">
                <div class="card-title">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <h3 class="card-title"></i> </h3>
                </div>
            </div>

            <div class="card-body pt-0">
                <table id="kt_datatable_zero_configuration" class="table align-middle table-row-dashed fs-6 gy-3">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-50px" style="text-align: center">#</th>
                            <th class="min-w-250px" style="text-align: center">{{ trans('sub.title') }} (<span
                                    class="text-muted">{{ trans('forms.lable_ar') }}</span>)</th>
                            <th class="min-w-250px" style="text-align: center">{{ trans('sub.title') }} (<span
                                    class="text-muted">{{ trans('forms.lable_en') }}</span>)</th>
                            <th class="min-w-70px" style="text-align: center">{{ trans('sub.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    </tbody>
                </table>
            </div>


        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form action="{{ route('admin.subscriptions.settings.store') }}" method="POST" id="sub_setting_form"
                class="form d-flex flex-column flex-lg-row my-form" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ trans('sidebar.' . $type) }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="">&times;</i>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <input type="hidden" name="type" id="type" value="{{ $type }}">
                            <input type="hidden" name="row_id" id="row_id" value="">

                            <div class="row">
                                <div class="col-md-6">

                                    <label class="required form-label">{{ trans('sub.Name') }} (<span
                                            class="text-muted">{{ trans('forms.lable_en') }}</span>)</label>
                                    <input type="text" name="title_en" id="title_en" class="form-control mb-2"
                                        placeholder="{{ trans('roles.Name') }}" value="" required autocomplete />
                                </div>

                                <div class="col-md-6">
                                    <label class="required form-label">{{ trans('sub.Name') }}(<span
                                            class="text-muted">{{ trans('forms.lable_ar') }}</span>)</label>
                                    <input type="text" name="title_ar" id="title_ar" class="form-control mb-2"
                                        placeholder="{{ trans('roles.Name') }}" />
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ trans('sub.Save') }}</span>
                            <span class="indicator-progress">Please wait...<span
                                    class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ trans('sub.Close') }}</button>

                    </div>
                </div>
            </form>
        </div>

    </div>


@stop
@section('js')

    <script>
        // Class definition
        var KTDatatablesServerSide = function() {
            // Shared variables
            var table;
            var dt;
            var filterPayment;

            // Private functions
            var initDatatable = function() {
                dt = $('#kt_datatable_zero_configuration').DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    ajax: "{{ route('admin.subscriptions.get_ajax_settings', $type) }}",
                    columns: [{
                            data: 'id',
                            className: 'text-center'
                        },
                        {
                            data: 'title_ar',
                            className: 'text-center'
                        },
                        {
                            data: 'title_en',
                            className: 'text-center'
                        },
                        {
                            data: 'actions',
                            className: 'text-center'
                        },
                    ],
                    order: [
                        [0, 'desc']
                    ],
                    columnDefs: [{
                        "targets": [0, 1, 2, 3],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',

                            });
                        }
                    }, ],
                });

                table = dt.$;

                // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
                dt.on('draw', function() {
                    KTMenu.createInstances();
                });
            }
            // Delete customer
            var handleDeleteRows = function() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Select all delete buttons
                /*   const deleteButtons = document.querySelectorAll('[data-kt-table-delete="delete_row"]');

                   deleteButtons.forEach(d => {
                       // Delete button on click
                       d.addEventListener('click', function (e) {
                           e.preventDefault();*/
                KTUtil.on(document.body, '[data-kt-table-delete="delete_row"]', 'click', function(e) {
                    e.preventDefault();
                    // Select parent row
                    const parent = e.target.closest('tr');
                    var action = e.target.getAttribute('href'); // Use 'this' instead of 'e'
                    console.log('action', action)
                    // Get customer name
                    const customerName = parent.querySelectorAll('td')[1].innerText;
                    // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                    Swal.fire({
                        text: "{{ trans('forms.delete_quetion') }}?",
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "{{ trans('forms.delete_btn') }}",
                        cancelButtonText: "{{ trans('forms.action_no') }}",
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then(function(result) {
                        if (result.value) {
                            // Simulate delete request -- for demo purpose only
                            /*Swal.fire({
                                showConfirmButton: false,
                                imageUrl: 'https://media.tenor.com/C7KormPGIwQAAAAi/epic-loading.gif',
                                imageWidth: 200,
                                imageHeight: 200,
                                target: '#ConvertModalInfo',
                                imageAlt: '',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            });*/
                            Swal.fire({
                                imageUrl: 'https://media.tenor.com/C7KormPGIwQAAAAi/epic-loading.gif',
                                imageWidth: 200,
                                imageHeight: 200,
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 2000,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(function() {


                                if (action) {
                                    fetch(action, {
                                            method: 'DELETE', // or 'GET', 'POST', etc. depending on your server setup
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken,

                                                // Add any additional headers if needed
                                            },
                                            // You can add body if needed, e.g., JSON.stringify({ key: 'value' })
                                        })
                                        .then(response => {
                                            if (!response.ok) {
                                                throw new Error(
                                                    'Network response was not ok');
                                            }
                                            return response
                                        .json(); // or response.text() or response.blob(), etc.
                                        })
                                        .then(data => {
                                            // Handle the response data if needed
                                            Swal.fire({
                                                text: "{{ trans('forms.Delete') }}",
                                                icon: "success",
                                                buttonsStyling: false,
                                                confirmButtonText: "{{ trans('forms.action_done') }}",

                                                customClass: {
                                                    confirmButton: "btn fw-bold btn-primary",
                                                }
                                            }).then(function() {
                                                // delete row data from server and re-draw datatable
                                                dt.draw();
                                            });
                                        })
                                        .catch(error => {
                                            console.error(
                                                'There was a problem with the fetch operation:',
                                                error);
                                        });
                                }


                            });
                        } else if (result.dismiss === 'cancel') {
                            Swal.fire({
                                text: " {{ trans('forms.Delete') }}",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "{{ trans('forms.action_done') }}",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        }
                    });
                });

                /* })
                 });*/

            }
            // Public methods
            return {
                init: function() {
                    initDatatable();
                    handleDeleteRows();
                }
            }
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesServerSide.init();
        });
    </script>






    <script>
        function edit_setting(id) {
            $.ajax({
                url: "{{ route('admin.subscriptions.settings.edit', ['setting' => '__id__']) }}".replace('__id__',
                    id),
                type: "get",
                dataType: "json",
                success: function(data) {
                    var allData = data.all_data;
                    console.log(allData);
                    console.log(allData.title.en);
                    $('#row_id').val(allData.id);
                    $('#title_en').val(allData.title.en);
                    $('#title_ar').val(allData.title.ar);
                },
            });
        }
    </script>

    <script>
        function add_setting() {
            $('#row_id').val('');
            $('#title_en').val('');
            $('#title_ar').val('');
        }
    </script>



    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\subscription\settings\Save_R', '#sub_setting_form') !!}
@endsection
