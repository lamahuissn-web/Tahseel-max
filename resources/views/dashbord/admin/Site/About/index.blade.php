@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('about.create')}}</h1>
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
                    {{trans('Toolbar.site')}}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.blog')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.about.create')}}"
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

        <div class="card card-flush">

            <div class="card-body pt-0">


                <table class="table align-middle table-row-dashed fs-6 gy-3"
                       id="data">
                    <thead>
                    <tr class="fw-semibold fs-6 text-gray-800">
                        <th>{{trans('about.ID')}}</th>
                        <th>{{trans('about.address')}}</th>
                        <th>{{trans('about.sub_address')}}</th>
                        <th>{{trans('about.Action')}}</th>
                    </tr>
                    </thead>
                </table>

            </div>
        </div>

    </div>

    <!-- Modal --->
    <div class="modal fade" tabindex="-1" id="kt_modal_1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">{{trans('contactus.details')}}</h3>

                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                         aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body" id="load_div">


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
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
                dt = $('#data').DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    ajax: "{{route('admin.about.index')}}",
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'address', name: 'address'},
                        {data: 'sub_address', name: 'sub_address'},
                        {data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'desc']],

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

                // Select all delete buttons
                /*   const deleteButtons = document.querySelectorAll('[data-kt-table-delete="delete_row"]');

                   deleteButtons.forEach(d => {
                       // Delete button on click
                       d.addEventListener('click', function (e) {
                           e.preventDefault();*/
                KTUtil.on(document.body, '[data-kt-table-delete="delete_row"]', 'click', function (e) {
                    e.preventDefault();
                    // Select parent row
                    const parent = e.target.closest('tr');
                    var action = e.target.getAttribute('href'); // Use 'this' instead of 'e'
                    console.log('action', action)
                    // Get customer name
                    const customerName = parent.querySelectorAll('td')[1].innerText;
                    // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                    Swal.fire({
                        text: "{{trans('forms.delete_quetion')}}?",
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "{{trans('forms.delete_btn')}}",
                        cancelButtonText: "{{trans('forms.action_no')}}",
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then(function (result) {
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
                                            // Handle the response data if needed
                                            Swal.fire({
                                                text: "{{trans('forms.Delete')}}",
                                                icon: "success",
                                                buttonsStyling: false,
                                                confirmButtonText: "{{trans('forms.action_done')}}",

                                                customClass: {
                                                    confirmButton: "btn fw-bold btn-primary",
                                                }
                                            }).then(function () {
                                                // delete row data from server and re-draw datatable
                                                dt.draw();
                                            });
                                        })
                                        .catch(error => {
                                            console.error('There was a problem with the fetch operation:', error);
                                        });
                                }


                            });
                        } else if (result.dismiss === 'cancel') {
                            Swal.fire({
                                text: " {{trans('forms.Delete')}}",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "{{trans('forms.action_done')}}",
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


            var handleDetailsRows = function () {

                KTUtil.on(document.body, '[data-kt-table-details="details_row"]', 'click', function (e) {
                    var id = e.target.getAttribute('data-id');
                    var action = e.target.getAttribute('data-url');
                    $.ajax({
                        type: 'get',
                        url: action,
                        beforeSend: function () {
                            const loadingEl = document.createElement("div");
                            document.getElementById('kt_modal_1').prepend(loadingEl);
                            loadingEl.classList.add("page-loader");
                            loadingEl.classList.add("flex-column");
                            loadingEl.classList.add("bg-dark");
                            loadingEl.classList.add("bg-opacity-25");
                            loadingEl.innerHTML = `
        <span class="spinner-border text-primary" role="status"></span>
        <span class="text-gray-800 fs-6 fw-semibold mt-5">{{trans('forms.Loading')}}</span>`;
                            // Show page loading
                            KTApp.showPageLoading();
                        },

                        success: function (resb) {
                            KTApp.hidePageLoading();
                            // loadingEl.remove();
                            $('#load_div').html(resb);

                        }
                    });
                });
            }


            // Public methods
            return {
                init: function () {
                    initDatatable();
                    handleDeleteRows();
                    handleDetailsRows();
                }
            }
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });
    </script>
@endsection
