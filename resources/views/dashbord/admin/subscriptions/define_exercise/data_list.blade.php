@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{ trans('sub.create') }}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                        class="text-muted text-hover-primary">{{ trans('Toolbar.home') }}</a></li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('Toolbar.subscriptions') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('Toolbar.settings') }}</li>
                <li class="breadcrumb-item text-muted">{{ trans('sub.define_exercise') }}</li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <i class="bi bi-plus">{{ trans('sub.add_define_exercise') }}</i>
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
                    <h3 class="card-title"> </h3>
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
                            <th class="min-w-250px" style="text-align: center">{{ trans('sub.exercise_type') }}</th>
                            <th class="min-w-250px" style="text-align: center">{{ trans('sub.exercise_level') }}</th>
                            <th class="min-w-250px" style="text-align: center">{{ trans('sub.trainer') }}</th>
                            <th class="min-w-70px" style="text-align: center">{{ trans('sub.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    </tbody>
                </table>
            </div>


        </div>
    </div>




@stop
@section('js')




    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\subscription\settings\Save_R', '#sub_setting_form') !!}
@endsection
