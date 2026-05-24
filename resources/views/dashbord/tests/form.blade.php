@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('tests.tests');
         $breadcrumbs = [
                  ['label' => trans('Toolbar.home'), 'link' => route('admin.test.index')],
                  ['label' => trans('Toolbar.tests'), 'link' => ''],
                  ['label' => trans('tests.tests_table'), 'link' => '']
                  ];

          PageTitle($title, $breadcrumbs);
        @endphp


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ BackButton(route('admin.test.index'))}}

        </div>
    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="t_container">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                generateCardHeader('tests.add_test','admin.test.index',' ')
            @endphp


            <form action="{{ route('admin.test.store') }}" method="post" enctype="multipart/form-data" id="store_form">
                @csrf
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">

                        <div class="col-md-4">
                            <label for="test_code" class="form-label">{{ trans('tests.test_code') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="test_code" id="test_code" value="{{ $test_code }}" readonly>
                            </div>
                            @error('test_code')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="client_id" class="form-label">{{ trans('tests.client') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="client_id" id="client_id">
                                    <option value="">{{trans('tests.select')}}</option>
                                    @foreach($clients as $item)
                                        <option value="{{$item->id}}" {{ old('client_id') == $item->id ? 'selected' : '' }}>{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('client_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="company_id" class="form-label">{{ trans('tests.company') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="company_id" id="company_id">
                                    <option value="">{{trans('tests.select')}}</option>
                                    @foreach($companies as $item)
                                        <option value="{{$item->id}}" {{ old('company_id') == $item->id ? 'selected' : '' }}>{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('company_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4">
                            <label for="project_id" class="form-label">{{ trans('tests.project') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="project_id" id="project_id">
                                    <option value="">{{trans('tests.select')}}</option>
                                    @foreach($projects as $item)
                                        <option value="{{$item->id}}" {{ old('project_id') == $item->id ? 'selected' : '' }}>{{$item->project_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('project_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="talab_number" class="form-label">{{ trans('tests.talab_number') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="number" class="form-control" name="talab_number" id="talab_number" value="{{ old('talab_number') }}">
                            </div>
                            @error('talab_number')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="talab_title" class="form-label">{{ trans('tests.talab_title') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="talab_title" id="talab_title" value="{{ old('talab_title') }}">
                            </div>
                            @error('talab_title')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4">
                            <label for="talab_image" class="form-label">{{ trans('tests.talab_image') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('image') !!}</span>
                                <input type="file" class="form-control" name="talab_image" id="talab_image">
                            </div>
                            @error('talab_image')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="talab_date" class="form-label">{{ trans('tests.talab_date') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('date') !!}</span>
                                <input type="date" class="form-control" name="talab_date" id="talab_date" value="{{ old('talab_date') }}">
                            </div>
                            @error('talab_date')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="talab_end_date" class="form-label">{{ trans('tests.talab_end_date') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('date') !!}</span>
                                <input type="date" class="form-control" name="talab_end_date" id="talab_end_date" value="{{ old('talab_end_date') }}">
                            </div>
                            @error('talab_end_date')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>



                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        {{ trans('tests.save') }}
                    </button>
                </div>
            </form>
        </div>

    </div>


@stop
@section('js')
    <script>
        function showSuccessMessage(message) {
            $('#success_message').text(message).removeClass('d-none').show();
            setTimeout(function() {
                $('#success_message').fadeOut().addClass('d-none');
            }, 8000);
        }
    </script>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>


@endsection

