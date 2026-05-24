@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('client.clients');
         $breadcrumbs = [
                  ['label' => trans('Toolbar.home'), 'link' => route('admin.company.index')],
                  ['label' => trans('Toolbar.companies'), 'link' => ''],
                  ['label' => trans('client.companies_table'), 'link' => '']
                  ];

          PageTitle($title, $breadcrumbs);
        @endphp


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ BackButton(route('admin.company.index'))}}

        </div>
    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="t_container">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                generateCardHeader('company.edit_company','admin.company.index',' ')
            @endphp


            <form action="{{ route('admin.company.update',$all_data->id) }}" method="post" enctype="multipart/form-data" id="store_form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">

                        <div class="col-md-4">
                            <label for="first_name" class="form-label">{{ trans('company.client') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="client_id" id="client_id">
                                    <option value="">{{trans('clients.select')}}</option>
                                    @foreach($clients as $item)
                                        <option value="{{$item->id}}" {{ old('client_id',$all_data->client_id) == $item->id ? 'selected' : '' }}>{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('client_id')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="first_name" class="form-label">{{ trans('company.company_code') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="company_code" id="company_code" value="{{$all_data->company_code}}" readonly>
                            </div>
                            @error('name')
                            <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="first_name" class="form-label">{{ trans('company.name') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="name" id="name" value="{{ old('name',$all_data->name) }}">
                            </div>
                            @error('name')
                            <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                            @enderror
                        </div>


                    </div>
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4">
                            <label for="address" class="form-label">{{ trans('clients.balance') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3"><i class="bi bi-house-door fs-2"></i></span>
                                <input type="text" class="form-control" name="balance" id="balance" value="{{ old('balance',$all_data->balance) }}">
                            </div>
                            @error('balance')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="last_name" class="form-label">{{ trans('company.phone') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('phone') !!}</span>
                                <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone',$all_data->phone) }}">
                            </div>
                            @error('phone')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">{{ trans('company.email') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('email') !!}</span>
                                <input type="text" class="form-control" name="email" id="email" value="{{ old('email',$all_data->email) }}">
                            </div>
                            @error('email')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="email" class="form-label">{{ trans('company.address1') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('address') !!}</span>
                                <input type="text" class="form-control" name="address1" id="address1" value="{{ old('address1',$all_data->address1) }}">
                            </div>
                            @error('address1')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        {{ trans('company.save') }}
                    </button>
                </div>
            </form>
        </div>

    </div>












@stop
@section('js')
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $("#governate").trigger("change");
            }, 300);
        });
    </script>
    <script>
        function get_area(id)
        {
            $.ajax({
                url: "{{ route('admin.get_area', ['id' => '__id__']) }}".replace('__id__', id),
                type: "get",
                dataType: "html",
                success: function (html) {
                    // console.log(html);
                    $('#city').html(html);
                    $('#city').val(<?= old('city')?> );
                },
            });
        }
    </script>

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

    {!! JsValidator::formRequest('App\Http\Requests\Admin\clients\SaveRequests', '#store_form1') !!}



@endsection

