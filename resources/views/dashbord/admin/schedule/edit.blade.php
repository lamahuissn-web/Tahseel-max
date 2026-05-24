@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{ trans('schedule.trainers') }}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                        class="text-muted text-hover-primary">{{ trans('Toolbar.home') }}</a>
                </li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('schedule.schedule') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('schedule.Update') }}</li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4" href="{{ route('admin.schedule.index') }}">
                    {{--                    <i class="bi bi-arrow-clockwise ">{{trans('sub.back')}}</i> --}}
                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/arrows/arr054.svg-->
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                </a>
            </div>
        </div>
    </div>

@endsection
@section('content')



    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="t_container">
            <div class="card shadow-sm ">
                <div class="card-header">
                    <h3 class="card-title"></i> {{ trans('schedule.Add_new') }}</h3>

                </div>

                <form id="save_form" method="post" action="{{ route('admin.schedule.update', $one_data->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <label class="required form-label">{{ trans('schedule.trainer_name') }}</label>
                                <select class="form-select" data-control="select2" data-placeholder="Select an option"
                                    name="trainer_id" id="trainer_id">
                                    <option value="">{{ trans('schedule.select') }}</option>
                                    @foreach ($trainers as $key)
                                        <option value="{{ $key->id }}"
                                            {{ old('trainer_id', $one_data->trainer_id) == $key->id ? 'selected' : '' }}>
                                            {{ $key->user_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="required form-label">{{ trans('schedule.classes') }}</label>
                                <select class="form-select" data-control="select2" data-placeholder="Select an option"
                                    name="class_id" id="class_id">
                                    <option value="">{{ trans('sub.select') }}</option>

                                    @foreach ($exercise as $key)
                                        <option value="{{ $key->id }}"
                                            {{ old('class_id', $one_data->class_id) == $key->id ? 'selected' : '' }}>
                                            {{ $key->title }}</option>
                                    @endforeach

                                </select>
                            </div>

                        </div>
                        <!----------------------------------------------------------------->

                        <div class="row" style="margin-top: 10px">
                            <div class="col">
                                <label class="required fs-6 fw-semibold mb-2">{{ trans('schedule.date') }}</label>
                                <input class="form-control form-control-solid @error('date') is-invalid @enderror"
                                    value="{{ old('date', $one_data->date) }}" name="date" placeholder="Pick date rage"
                                    id="date" />
                                @error('date')
                                    <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col">
                                <label class="required fs-6 fw-semibold mb-2">{{ trans('schedule.time') }}</label>
                                <input type="time"
                                    class="form-control form-control-solid @error('time') is-invalid @enderror"
                                    value="{{ old('time', $one_data->time) }}" name="time" placeholder="Pick date rage"
                                    id="time" />
                                <!--end::Input-->
                                @error('time')
                                    <div class="fv-plugins-message-container invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <!----------------------------------------------------------------->

                        <div class="d-flex justify-content-end" style="margin-top: 20px">
                            <button type="submit" id="" class="btn btn-primary">
                                <span class="indicator-label">{{ trans('forms.save_btn') }}</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>


                    </div>


                </form>

            </div>


        </div>
    </div>










@stop
@section('js')




    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\schedule\scheduleRequest', '#save_form') !!}

    <script>
        var KTAppBlogSave = function() {
            !!!!!!
        }
        }
        }
        const initDaterangepicker = () => {

            $("#date").daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minYear: 2000,
                maxYear: parseInt(moment().format("YYYY"), 12),
                locale: {
                    format: "YYYY-MM-DD"
                }
            });
        }

        // Public methods
        return {
            init: function() {
                // Init forms
                initDaterangepicker();

            }
        };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppBlogSave.init();
        });
    </script>


@endsection
