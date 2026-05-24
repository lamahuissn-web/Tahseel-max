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
                    {{ trans('Toolbar.roles') }}
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
                            <th class="">#</th>
                            <th class="">{{ trans('roles.title') }}</th>
                            <th class="">{{ trans('roles.Name') }}</th>
                            <th class="">{{ trans('roles.guard_name') }}</th>
                            <th class=" ">{{ trans('roles.Actions') }}</th>
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
                        @foreach ($roles as $x)
                            <tr>
                                <td>{{ $i++ }}</td>

                                <td>{{ $x->title }}</td>
                                <td>{{ $x->name }}</td>
                                <td>{{ $x->guard_name }}</td>


                                <!--begin::Action=-->
                                <td class="">

                                    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                        <a data-bs-toggle="modal" data-bs-target="#editeModal"
                                            onclick="load_edite({{ $x->id }})" title="{{ trans('roles.Edit') }}"
                                            class="btn btn-sm btn-icon  btn-light-warning"><i class="fas fa-pencil"></i></a>
                                        <a href=" {{ route('admin.UserManagement.roles.delete', $x->id) }}"
                                            title="{{ trans('roles.Delete') }}"
                                            class="btn btn-sm btn-icon btn-light-danger"><i class="fas fa-trash"></i></a>

                                    </div>

                                </td>
                                <!--end::Action=-->
                            </tr>
                        @endforeach

                    </tbody>

                </table>
                <!--end::Table-->

                {{-- @foreach ($roles as $x)
                     <!-- Modal 1-->
                         <div class="modal fade" id="exampleModal{{$x->id}}" tabindex="-1"
                              aria-labelledby="exampleModalLabel" aria-hidden="true">
                             <div class="modal-dialog">
                                 <form action="{{route('admin.UserManagement.roles.update',$x->id)}}" method="POST"
                                       id="kt_ecommerce_add_product_form my-form"
                                       class="form d-flex flex-column flex-lg-row"
                                       enctype="multipart/form-data">
                                     @method('PUT')
                                     {{csrf_field()}}
                                     <div class="modal-content">
                                         <div class="modal-header">
                                             <h5 class="modal-title" id="exampleModalLabel">
                                                 {{trans('roles.Update')}}</h5>
                                         </div>
                                         <!--begin::Formmmmm-->

                                         <div class="modal-body">
                                             <div class="container-fluid">

                                                 <input type="hidden" name="id" value="{{$x->id}}">
                                                 @php
                                                     $title=$x->getTranslations('title'); //return local lang
                                                 @endphp
                                                 <div class="row">
                                                     <label
                                                         class="required form-label">{{trans('roles.Name')}}
                                                         (<span
                                                             class="text-gray-600">{{trans('forms.lable_en')}}</span>)</label>

                                                     <input type="text" name="title_en" class="form-control mb-2"
                                                            placeholder="{{trans('roles.Name')}}" value="{{$title['en']}}"
                                                            required autocomplete/>
                                                 </div>
                                                 <div class="row">
                                                     <label
                                                         class="required form-label">{{trans('roles.Name')}}
                                                         (<span
                                                             class="text-gray-600">{{trans('forms.lable_ar')}}</span>)</label>

                                                     <input type="text" name="title_ar" class="form-control mb-2"
                                                            placeholder="{{trans('roles.Name')}}" value="{{$title['ar']}}"
                                                            required autocomplete/>
                                                 </div>
                                                 <div class="row">
                                                     <label class="required form-label">{{trans('roles.value')}} </label>

                                                     <input type="text" name="name" class="form-control mb-2"
                                                            placeholder="{{trans('roles.value')}}" value="{{$x->name}}"
                                                            required autocomplete/>
                                                 </div>
                                                 <div class="row">
                                                     <label class="required form-label">{{trans('roles.guard_name')}}</label>
                                                     <input type="text" name="guard_name" class="form-control mb-2"
                                                            placeholder="" value="{{$x->guard_name}}" required autocomplete/>
                                                 </div>

                                                 <!--end::Button-->

                                                 <!--end::Main column-->
                                             </div>
                                             <div class="modal-footer">
                                                 <button type="submit" class="btn btn-primary">
                                                         <span
                                                             class="indicator-label">{{trans('roles.Save')}}</span>
                                                     <span class="indicator-progress">Please wait...
                                         <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                 </button>
                                                 <button type="button" class="btn btn-secondary"
                                                         data-bs-dismiss="modal">
                                                     {{trans('roles.Close')}}</button>

                                             </div>

                                         </div>
                                     </div>
                                 </form>
                             </div>
                         </div>
                     @endforeach --}}

            </div>
            <!--end::Card body-->
        </div>
        <!--end::Category-->
    </div>
    <!--end::Content container-->
    <div class="modal fade" id="editeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" id="load_div">


        </div>
    </div>
    <!-- Modal 1-->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form action="{{ route('admin.UserManagement.roles.store') }}" method="POST"
                id="kt_ecommerce_add_product_form" class="form d-flex flex-column flex-lg-row my-form"
                enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ trans('roles.Add') }}</h5>
                    </div>
                    <!--begin::Formmmmm-->

                    <div class="modal-body">
                        <div class="container-fluid">

                            <div class="row">
                                <div class="col">
                                    <label class="required form-label">{{ trans('roles.Name') }} (<span
                                            class="text-gray-600">{{ trans('forms.lable_en') }}</span>)</label>

                                    <input type="text" name="title_en" class="form-control mb-2"
                                        placeholder="{{ trans('roles.Name') }}" value="" required autocomplete />
                                </div>
                                <div class="col">
                                    <label class="required form-label">{{ trans('roles.Name') }}(<span
                                            class="text-gray-600">{{ trans('forms.lable_ar') }}</span>)</label>

                                    <input type="text" name="title_ar" class="form-control mb-2"
                                        placeholder="{{ trans('roles.Name') }}" required autocomplete />
                                </div>
                            </div>
                            {{--  <div class="row">
                                <div class="col">
                                    <label class="required form-label">{{trans('roles.value')}} </label>

                                    <input type="text" name="name" class="form-control mb-2"
                                           placeholder="{{trans('roles.value')}}" value="" required autocomplete/>
                                </div>
                                <div class="col">
                                    <label class="required form-label">{{trans('roles.guard_name')}}</label>
                                    <input type="text" name="guard_name" class="form-control mb-2"
                                           placeholder="{{trans('roles.guard_name')}}" required autocomplete/>
                                </div>
                            </div> --}}

                            <div class="fv-row mt-5">
                                <!--begin::Label-->
                                <label class="fs-5 fw-bold form-label mb-5">{{ trans('roles.permissions') }}</label>
                                <!--end::Label-->
                                @php
                                    $chunkedItems = $permissions->chunk(4);

                                @endphp
                                @foreach ($chunkedItems as $chunk)
                                    <div class="row mb-2">

                                        @foreach ($chunk as $item)
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="permissions[]" type="checkbox"
                                                        value="{{ $item->name }}" id="flexCheckDefault" />
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                        {{ $item->title }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                @endforeach
                            </div>

                            <!--end::Main column-->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ trans('roles.Save') }}</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ trans('roles.Close') }}</button>

                    </div>


                </div>
            </form>
        </div>

    </div>

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


        function load_edite(id) {

            $.ajax({
                type: 'get',
                url: '{{ route('admin.UserManagement.roles.load_edit') }}',
                data: {
                    id: id
                },
                beforeSend: function() {
                    const loadingEl = document.createElement("div");
                    document.getElementById('editeModal').prepend(loadingEl);
                    loadingEl.classList.add("page-loader");
                    loadingEl.classList.add("flex-column");
                    loadingEl.classList.add("bg-dark");
                    loadingEl.classList.add("bg-opacity-25");
                    loadingEl.innerHTML = `
        <span class="spinner-border text-primary" role="status"></span>
        <span class="text-gray-800 fs-6 fw-semibold mt-5">{{ trans('forms.Loading') }}</span>`;
                    // Show page loading
                    KTApp.showPageLoading();
                },

                success: function(resb) {
                    KTApp.hidePageLoading();
                    // loadingEl.remove();
                    $('#load_div').html(resb);

                }
            });

        }
    </script>
    {{--  <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
      {!! JsValidator::formRequest('App\Http\Requests\EditeCityRequest', '.my-form') !!} --}}
@endsection
