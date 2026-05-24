@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('project.projects');
         $breadcrumbs = [
                  ['label' => trans('Toolbar.home'), 'link' => route('admin.company.index')],
                  ['label' => trans('Toolbar.projects'), 'link' => ''],
                  ['label' => trans('project.projects_table'), 'link' => '']
                  ];

          PageTitle($title, $breadcrumbs);
        @endphp


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ BackButton(route('admin.project.index'))}}

        </div>
    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="t_container">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                generateCardHeader('project.add_project','admin.project.index',' ')
            @endphp


            <form action="{{ route('admin.project.store') }}" method="post" enctype="multipart/form-data" id="store_form">
                @csrf
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-3">
                            <label for="first_name" class="form-label">{{ trans('project.project_code') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="project_code" id="project_code" value="{{$project_code}}" readonly>
                            </div>
                            @error('name')
                            <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="first_name" class="form-label">{{ trans('project.client') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" onchange="get_company(this.value)" name="client_id" id="client_id">
                                    <option value="">{{trans('clients.select')}}</option>
                                    @foreach($clients as $item)
                                        <option value="{{$item->id}}" {{ old('client_id') == $item->id ? 'selected' : '' }}>{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('client_id')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-3">
                            <label for="first_name" class="form-label">{{ trans('project.company') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="company_id" id="company_id">
                                    <option value="">{{trans('clients.select')}}</option>

                                </select>
                            </div>
                            @error('company_id')
                            <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="last_name" class="form-label">{{ trans('project.project_name') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="project_name" id="project_name" value="{{ old('project_name') }}">
                            </div>
                            @error('project_name')
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
                $("#client_id").trigger("change");
            }, 300);
        });
    </script>
    <script>
        function get_company(id)
        {
            $.ajax({
                url: "{{ route('admin.get_company', ['id' => '__id__']) }}".replace('__id__', id),
                type: "get",
                dataType: "html",
                success: function (html) {
                    // console.log(html);
                    $('#company_id').html(html);
                    $('#company_id').val(<?= old('company_id')?> );
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



@endsection

