@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{trans('sub.members')}}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.Members.index') }}" class="text-muted text-hover-primary">{{trans('Toolbar.home')}}</a></li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('Toolbar.members')}}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('members.members')}}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{trans('members.members_attendance_reports')}}</li>
            </ul>
        </div>

    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="app-container container-xxxl">

        <div class="card card-flush">

            <div class="card-body pt-0">


                <table class="table align-middle table-row-dashed fs-6 gy-3"
                       id="table">
                    <thead>
                    <tr class="fw-semibold fs-6 text-gray-800">
                        <th>{{trans('event.ID')}}</th>
                        <th>{{trans('members.member_name')}}</th>
                        <th>{{trans('members.subscription')}}</th>
                        <th>{{trans('members.session_num')}}</th>
                        <th>{{trans('members.session_attendance')}}</th>
                        <th>{{trans('members.remain_session')}}</th>
                    </tr>
                    </thead>
                </table>

            </div>
        </div>

    </div>












@stop
@section('js')




    <script>
        // Class definition
        var KTDatatablesServerSide = function () {
            // Shared variables
            var table;
            var dt;
            var filterPayment;

            // Private functions
            var initDatatable = function () {
                dt = $('#table').DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4'f><'col-sm-12 col-md-4'B>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    ajax: "{{route('admin.subscriptions.Reports.MembersAttendanceReports.index')}}",
                    columns: [
                        { data: 'id', className: 'text-center no-export' },
                        { data: 'member_name', className: 'text-center' },
                        { data: 'subscription', className: 'text-center'},
                        { data: 'session_num', className: 'text-center' },
                        { data: 'session_attendance', className: 'text-center' },
                        { data: 'remain_session', className: 'text-center' },

                    ],
                    order: [[0, 'desc']],
                    columnDefs: [
                        {
                            "targets": [0,1,2,3],
                            "createdCell": function (td, cellData, rowData, row, col) {
                                $(td).css({
                                    'font-weight': '600',
                                    'text-align': 'center',

                                });
                            }
                        },
                    ],
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '{{trans('forms.ExportToExcel')}}',
                            exportOptions: {
                                columns: ':visible:not(.no-export)'  // Exclude columns with class 'no-export'
                            }
                        }
                    ]
                });

                table = dt.$;

                // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
                dt.on('draw', function () {
                    KTMenu.createInstances();
                });
            }
            // Delete customer
            var handleDeleteRows = function () {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                KTUtil.on(document.body, '[data-kt-table-delete="delete_row"]', 'click', function (e) {
                    e.preventDefault();
                    const parent = e.target.closest('tr');
                    var action = e.target.getAttribute('href');

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
                    }).then(function (result) {
                        if (result.value) {
                            Swal.fire({
                                imageUrl: 'https://media.tenor.com/C7KormPGIwQAAAAi/epic-loading.gif',
                                imageWidth: 200,
                                imageHeight: 200,
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 2000,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(function () {
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
                                                throw new Error('Network response was not ok');
                                            }
                                            return response.json(); // or response.text() or response.blob(), etc.
                                        })

                                        .then(data => {
                                            Swal.fire({
                                                text: "{{ trans('forms.Delete') }}",
                                                icon: "success",
                                                buttonsStyling: false,
                                                confirmButtonText: "{{ trans('forms.action_done') }}",
                                                customClass: {
                                                    confirmButton: "btn fw-bold btn-primary",
                                                }
                                            }).then(function () {
                                                // Assuming dt is your DataTables instance, redraw it after deletion
                                                dt.draw();
                                            });
                                        })
                                        .catch(error => {
                                            console.error('Error deleting:', error);
                                            Swal.fire({
                                                text: "{{ trans('forms.Delete') }}",
                                                icon: "success",
                                                buttonsStyling: false,
                                                confirmButtonText: "{{ trans('forms.action_done') }}",
                                                customClass: {
                                                    confirmButton: "btn fw-bold btn-primary",
                                                }
                                            }).then(function () {
                                                // Assuming dt is your DataTables instance, redraw it after deletion
                                                dt.draw();
                                            });
                                        });
                                }
                            });
                        } else if (result.dismiss === 'cancel') {
                            Swal.fire({
                                text: "{{ trans('forms.Delete') }}",
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
            };


            // Public methods
            return {
                init: function () {
                    initDatatable();
                    handleDeleteRows();
                }
            }
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });
    </script>

@endsection

