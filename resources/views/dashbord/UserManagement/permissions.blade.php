@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{ trans('about.create') }}</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">

                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                        {{ trans('Toolbar.home') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{ trans('Toolbar.user') }}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{ trans('Toolbar.permissions') }}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal"
                    class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4">
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
                </button>
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
    <!-- Modal 1-->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.UserManagement.permission.store') }}" method="POST"
                id="kt_ecommerce_add_product_form" class="form d-flex flex-column flex-lg-row my-form"
                enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ trans('permission.Add') }}</h5>
                    </div>
                    <!--begin::Formmmmm-->

                    <div class="modal-body">
                        <div class="container-fluid">


                            <div class="row">
                                <label class="required form-label">{{ trans('permission.Name') }} (<span
                                        class="text-gray-600">{{ trans('forms.lable_en') }}</span>)</label>

                                <input type="text" name="title_en" class="form-control mb-2"
                                    placeholder="{{ trans('permission.Name') }}" value="" required autocomplete />
                            </div>
                            <div class="row">
                                <label class="required form-label">{{ trans('permission.Name') }}(<span
                                        class="text-gray-600">{{ trans('forms.lable_ar') }}</span>)</label>

                                <input type="text" name="title_ar" class="form-control mb-2"
                                    placeholder="{{ trans('permission.Name') }}" required autocomplete />
                            </div>
                            <div class="row">
                                <label class="required form-label">{{ trans('permission.value') }} </label>

                                <input type="text" name="name" class="form-control mb-2"
                                    placeholder="{{ trans('permission.value') }}" value="" required autocomplete />
                            </div>
                            <div class="row">
                                <label class="required form-label">{{ trans('permission.guard_name') }}</label>
                                <input type="text" name="guard_name" class="form-control mb-2"
                                    placeholder="{{ trans('permission.guard_name') }}" required autocomplete />
                            </div>
                            <!--end::Main column-->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ trans('permission.Save') }}</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ trans('permission.Close') }}</button>

                    </div>


                </div>
            </form>
        </div>

    </div>

    <div id="kt_app_content_container" class="app-container container-xxl">
        <!--begin::Category-->
        <div class="card card-flush">
            <!--begin::Card header-->
            <div class="card-header align-items-center py-3 gap-2 gap-md-1">
                <!--begin::Card title-->
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
                </div>
                <!--end::Card title-->
            </div>

            <div class="card-body pt-0">
                <!--begin::Table-->
                <table id="kt_datatable_zero_configuration" class="table align-middle table-row-dashed fs-6 gy-3">
                    <!--begin::Table head-->
                    <thead>

                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-250px">#</th>
                            <th class="min-w-250px">{{ trans('permission.title') }}</th>
                            <th class="min-w-250px">{{ trans('permission.Name') }}</th>
                            <th class="min-w-250px">{{ trans('permission.guard_name') }}</th>
                            <th class="text-end min-w-70px">{{ trans('permission.Actions') }}</th>
                        </tr>
                        <!--end::Table row-->
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody class="fw-semibold text-gray-600">
                        <!--begin::Table row-->
                        @php
                            $i = 1;
                        @endphp
                        @foreach ($permission as $x)
                            <tr>
                                <td>{{ $i++ }}</td>

                                <td>{{ $x->title }}</td>
                                <td>{{ $x->name }}</td>
                                <td>{{ $x->guard_name }}</td>


                                <!--begin::Action=-->
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-active-light-primary"
                                        data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-end">{{ trans('permission.Actions') }}
                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr072.svg-->
                                        <span class="svg-icon svg-icon-5 m-0">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421
                                                    8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583
                                                    5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835
                                                     12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642
                                                      8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584
                                                      16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467
                                                      13.0468 11.4343 12.7344Z" fill="currentColor" />
                                            </svg>
                                        </span>
                                        <!--end::Svg Icon-->
                                    </a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded
                                        menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                        data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="{{ route('admin.UserManagement.permission.edit', $x->id) }}"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal{{ $x->id }}"
                                                class="menu-link px-3">{{ trans('permission.Edit') }}</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="{{ route('admin.UserManagement.permission.delete', $x->id) }}"
                                                class="menu-link px-3"
                                                data-kt-ecommerce-category-filter="delete_row">{{ trans('permission.Delete') }}</a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                </td>
                                <!--end::Action=-->
                            </tr>
                        @endforeach

                    </tbody>

                </table>
                <!--end::Table-->

                @foreach ($permission as $x)
                    <!-- Modal 1-->
                    <div class="modal fade" id="exampleModal{{ $x->id }}" tabindex="-1"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('admin.UserManagement.permission.update', $x->id) }}" method="POST"
                                id="kt_ecommerce_add_product_form my-form" class="form d-flex flex-column flex-lg-row"
                                enctype="multipart/form-data">
                                @method('PUT')
                                {{ csrf_field() }}
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">
                                            {{ trans('permission.Update') }}</h5>
                                    </div>
                                    <!--begin::Formmmmm-->

                                    <div class="modal-body">
                                        <div class="container-fluid">

                                            <input type="hidden" name="id" value="{{ $x->id }}">
                                            @php
                                                $title = $x->getTranslations('title'); //return local lang
                                            @endphp
                                            <div class="row">
                                                <label class="required form-label">{{ trans('permission.Name') }}
                                                    (<span class="text-gray-600">{{ trans('forms.lable_en') }}</span>)
                                                </label>

                                                <input type="text" name="title_en" class="form-control mb-2"
                                                    placeholder="{{ trans('permission.Name') }}"
                                                    value="{{ $title['en'] }}" required autocomplete />
                                            </div>
                                            <div class="row">
                                                <label class="required form-label">{{ trans('permission.Name') }}
                                                    (<span
                                                        class="text-gray-600">{{ trans('forms.lable_ar') }}</span>)</label>

                                                <input type="text" name="title_ar" class="form-control mb-2"
                                                    placeholder="{{ trans('permission.Name') }}"
                                                    value="{{ $title['ar'] }}" required autocomplete />
                                            </div>
                                            <div class="row">
                                                <label class="required form-label">{{ trans('permission.value') }}
                                                </label>

                                                <input type="text" name="name" class="form-control mb-2"
                                                    placeholder="{{ trans('permission.value') }}"
                                                    value="{{ $x->name }}" required autocomplete />
                                            </div>
                                            <div class="row">
                                                <label
                                                    class="required form-label">{{ trans('permission.guard_name') }}</label>
                                                <input type="text" name="guard_name" class="form-control mb-2"
                                                    placeholder="" value="{{ $x->guard_name }}" required autocomplete />
                                            </div>

                                            <!--end::Button-->

                                            <!--end::Main column-->
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">
                                                <span class="indicator-label">{{ trans('permission.Save') }}</span>
                                                <span class="indicator-progress">Please wait...
                                                    <span
                                                        class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                            </button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                {{ trans('permission.Close') }}</button>

                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

            </div>
            <!--end::Card body-->
        </div>
        <!--end::Category-->
    </div>
    <!--end::Content container-->

@stop
@section('js')
    <script>
        /*
                    $("#kt_datatable_zero_configuration").DataTable();
            */

        $("#kt_datatable_zero_configuration").DataTable({
            "language": {
                "lengthMenu": "Show _MENU_",
            },
            "dom": "<'row'" +
                "<'col-sm-6 d-flex align-items-center justify-conten-start'l>" +
                "<'col-sm-6 d-flex align-items-center justify-content-end'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">"
        });
    </script>
    {{--  <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\EditeCityRequest', '.my-form') !!} --}}
@endsection
