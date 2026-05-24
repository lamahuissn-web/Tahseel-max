@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{ trans('sub.transportation') }}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}"
                        class="text-muted text-hover-primary">{{ trans('Toolbar.home') }}</a>
                </li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('Toolbar.subscriptions') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('sub.transportation') }}</li>
                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                <li class="breadcrumb-item text-muted">{{ trans('sub.add_new_transportation') }}</li>
            </ul>
        </div>


        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <div class="d-flex">
                <a class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4"
                    href="{{ route('admin.subscriptions.transportation.index') }}">
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
                    <h3 class="card-title"></i> {{ trans('sub.add_new_transportation') }}</h3>
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

                <form id="save_form" method="post" action="{{ route('admin.subscriptions.transportation.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.day') }}</label>
                                <input type="date" name="moving_day" id="moving_day" min="{{ date('Y-m-d') }}"
                                    class="form-control mb-2" value="{{ old('moving_day') }}" required autocomplete />
                            </div>

                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.trip_time') }}</label>
                                <input type="time" name="trip_time" id="trip_time" value="{{ old('trip_time') }}"
                                    class="form-control mb-2" placeholder="{{ trans('sub.trip_time') }}" />
                            </div>

                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.moving_time') }}</label>
                                <input type="time" name="moving_time" id="moving_time" value="{{ old('moving_time') }}"
                                    class="form-control mb-2" placeholder="{{ trans('sub.moving_time') }}" />
                            </div>




                        </div>

                        {{-- <div class="row">
                            <div class="col-md-4">
                                <label class="required form-label">{{ trans('sub.car_type') }}</label>
                                <select class="form-select" data-control="select2" data-placeholder="Select an option" name="car_type" id="car_type">
                                    <option value="">{{ trans('sub.select') }}</option>
                                    @foreach ($car_type as $row)
                                        <option value="{{ $row->id }}" {{ old('car_type') == $row->id  ? 'selected' : '' }}>{{ $row->title }}</option>
                                    @endforeach
                                </select>
                            </div>
--}}
                        <div class="col-md-4">
                            <label class="required form-label">{{ trans('sub.persons_number') }}</label>
                            <input type="number" name="persons_number" id="persons_number"
                                value="{{ old('persons_number') }}" class="form-control mb-2" />
                        </div>



                    </div>

                    <div class="col-md-12">
                        <div class="form-group text-end" style="margin-top: 27px;">
                            <button type="submit" name="btnSave" value="btnSave" id="btnSave"
                                class="btn btn-success btn-flat ">
                                <i class="bi bi-save"></i> {{ trans('sub.save') }}
                            </button>
                        </div>
                    </div>




                    {{--                    </div> --}}



                </form>

            </div>


        </div>
    </div>










@stop
@section('js')

    <script>
        $(document).ready(function() {
            function padZero(num) {
                return num.toString().padStart(2, '0');
            }

            function validateTimes() {
                var today = new Date().toISOString().split('T')[0];
                var now = new Date();
                var currentHour = now.getHours();
                var currentMinutes = now.getMinutes();
                var currentTime = padZero(currentHour) + ':' + padZero(currentMinutes);

                var selectedDay = $('#moving_day').val();
                var tripTime = $('#trip_time').val();
                var movingTime = $('#moving_time').val();
                var futureHour = padZero(currentHour + 1) + ':' + padZero(currentMinutes);
                var futureHour2 = padZero(currentHour + 2) + ':' + padZero(currentMinutes);
                if (selectedDay === today) {

                    $('#trip_time').attr('min', futureHour);
                    if (tripTime < currentTime) {
                        $('#trip_time').val(futureHour);
                        $('#moving_time').val(futureHour2);
                    } else {
                        $('#trip_time').removeAttr('min');
                        if (movingTime < tripTime) {
                            $('#moving_time').val('');
                        }
                    }
                } else {
                    $('#trip_time').removeAttr('min');
                    if (movingTime < tripTime) {
                        $('#moving_time').val('');
                    }

                }

            }

            // $('#moving_day, #trip_time,#moving_time').on('change', validateTimes);

            // validateTimes();
        });
    </script>




    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest(
        'App\Http\Requests\Admin\subscription\transportation\SaveTransportation_R',
        '#save_form',
    ) !!}
@endsection
