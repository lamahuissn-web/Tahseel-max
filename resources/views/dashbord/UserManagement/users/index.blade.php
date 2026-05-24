@extends('dashbord.layouts.master')

@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                Add User</h1>
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
                    <a href="{{ route('admin.UserManagement.users.index') }}" class="text-muted text-hover-primary">{{trans('Toolbar.users')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.UserManagement.users.create') }}" class="text-muted text-hover-primary">{{trans('users.create')}}</a>
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.UserManagement.users.create')}}"
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

        </div>
        <!--end::Actions-->
    </div>
    <!--end::Toolbar container-->
@endsection
@section('content')
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!--begin::Category-->
        <div class="card card-flush">
            <div class="card-body pt-0">
                {{--{{ $dataTable->table() }}--}}
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-3" id="datatable-crud">
                    <thead>
                    <tr class="fw-semibold fs-6 text-gray-800">
                        <th>Id</th>
                        <th>User Name</th>
{{--                        <th>Email</th>--}}
                        <th>Phone</th>
{{--                        <th>Address</th>--}}
                        <th>role</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')

    <script>
        $(document).ready(function () {
            var table = $('#datatable-crud').DataTable({
                processing: true,
                serverSide: true,
                dom: 'lfrtip',
                ajax: "{{route('admin.UserManagement.users.index')}}",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'userCard', name: 'userCard', orderable: false},
                    // {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    // {data: 'address', name: 'address'},
                    {data: 'role', name: 'role', orderable: false},
                    {data: 'action', name: 'action', orderable: false},
                ],
                order: [[0, 'desc']]
            });
        });
    </script>

@endsection
