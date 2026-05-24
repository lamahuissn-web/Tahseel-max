@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('masrofat.masrofat');
         $breadcrumbs = [
                  ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                  ['label' => trans('Toolbar.masrofat'), 'link' => route('admin.masrofat.index')],
                  ['label' => trans('masrofat.masrofat_table'), 'link' => '']
                  ];

          PageTitle($title, $breadcrumbs);
        @endphp


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ BackButton(route('admin.masrofat.index'))}}

        </div>
    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="t_container">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                generateCardHeader('masrofat.add_masrofat','admin.masrofat.index',' ')
            @endphp


            <form action="{{ route('admin.masrofat.store') }}" method="post" enctype="multipart/form-data" id="store_form">
                @csrf
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">

                        <div class="col-md-4">
                            <label for="first_name" class="form-label">{{ trans('masrofat.employee') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="emp_id" id="emp_id">
                                    <option value="">{{trans('masrofat.select')}}</option>
                                    @foreach($employees as $item)
                                        <option value="{{$item->id}}" {{ old('emp_id') == $item->id ? 'selected' : '' }}>{{$item->first_name . ' ' . $item->last_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('emp_id')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="first_name" class="form-label">{{ trans('masrofat.band') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="band_id" id="band_id">
                                    <option value="">{{trans('masrofat.select')}}</option>
                                    @foreach($bands as $item)
                                        <option value="{{$item->id}}" {{ old('band_id') == $item->id ? 'selected' : '' }}>{{$item->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('band_id')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="value" class="form-label">{{ trans('masrofat.value') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="number" class="form-control" name="value" id="value" value="{{ old('value') }}">
                            </div>
                            @error('value')
                            <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-12" style="margin-top: 10px">
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ trans('masrofat.notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" >{{ old('notes') }}</textarea>
                            @error('notes')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        {{ trans('masrofat.save') }}
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

    {{-- {!! JsValidator::formRequest('App\Http\Requests\Admin\masrofat\SaveRequests', '#store_form1') !!} --}}



@endsection

