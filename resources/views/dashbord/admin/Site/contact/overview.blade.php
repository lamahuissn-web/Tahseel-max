@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('viewdata.Add User')}}</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">
                    <a href="{{route('admin.dashboard')}}" class="text-muted text-hover-primary">{{trans('contactus.home')}}</a>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">{{trans('contactus.siteData')}}</li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">{{trans('contactus.contactus')}}</li>
                <!--end::Item-->
            </ul>
            <!--end::Breadcrumb-->
        </div>

    </div>
    <!--end::Toolbar container-->
@endsection

@section('content')



            <div id="kt_app_content_container" class="app-container container-xxxl">
                <!--begin::Category-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header align-items-center py-3 gap-2 gap-md-1">
                        <!--begin::Card title-->
                        <div class="card-title">

                        </div>
                        <!--end::Card title-->


                    </div>
                    <div class="card-body pt-0">
                        {{--{{ $dataTable->table() }}--}}
                        <table class="table align-middle table-row-dashed fs-6 gy-3"
                               id="data">
                            <thead>
                            <tr class="fw-semibold fs-6 text-gray-800">
                                <th>{{trans('contactus.ID')}}</th>
                                <th>{{trans('contactus.Name')}}</th>
                                <th>{{trans('contactus.Email')}}</th>
                                <th>{{trans('contactus.Phone')}}</th>
                                <th>{{trans('contactus.Subject')}}</th>
                                <th>{{trans('contactus.Action')}}</th>
                            </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>






    <!-- Modal --->
    <div class="modal fade" tabindex="-1" id="kt_modal_1">
        <div class="modal-dialog">
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

                <div class="modal-body">

                    <div class="card card-flush mb-10">
                        <!--begin::Card header-->
                        <div class="card-header pt-9">
                            <!--begin::Author-->
                            <div class="d-flex align-items-center">

                                <!--begin::Info-->
                                <div class="flex-grow-1">
                                    <!--begin::Name-->
                                    <a href="javascript:void(0)" class="text-gray-800 text-hover-primary fs-4 fw-bold"
                                       id="name_con">Brooklyn Simmons</a>
                                    <!--end::Name-->
                                    <!--begin::Date-->
                                    <span class="text-gray-400 fw-semibold d-block" id="email_con"></span>
                                    <span class="text-gray-400 fw-semibold d-block" id="phone_con"></span>
                                    <!--end::Date-->
                                </div>
                                <!--end::Info-->
                            </div>
                            <!--end::Author-->

                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Post content-->
                            <div id="subject_p" class="fs-6 fw-normal text-gray-700">You can either decide on your final
                                headline before outstanding you write the most of the rest of your creative post
                            </div>
                            <!--end::Post content-->
                        </div>
                        <!--end::Card body-->

                    </div>

                    {{--                    <p id="subject_p"></p>--}}
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
        var datatable                   //for the action

        $(document).ready(function () {
            datatable = $('#data').DataTable({
                processing: true,
                serverSide: true,
                dom: 'lfrtip',
                ajax: "{{route('admin.contact.index')}}",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'subject', name: 'subject', orderable: false},
                    {data: 'action', name: 'action', orderable: false},
                ],
                order: [[0, 'desc']]
            });
            datatable.on('draw', function () {
                KTMenu.createInstances();
            });
        });

        function getDetailes(id) {
            $.ajax({
                type: 'get',
                url: "{{url('admin/contact/show')}}",
                data: {id: id},
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

                    var one_data = resb.one_data;
                    console.log(one_data);
                    $('#subject_p').html(one_data.subject);
                    $('#name_con').html(one_data.name);
                    $('#email_con').html(one_data.email);
                    $('#phone_con').html(one_data.phone);


                }
            });

        }

    </script>

@endsection
