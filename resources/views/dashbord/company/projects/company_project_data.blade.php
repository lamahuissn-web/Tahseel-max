<div class="" style="margin-top: 30px">
    @if(isset($projects_data) || !empty($projects_data ) || $projects_data->isEmpty() )
        <table id="table" class="example table table-bordered responsive nowrap text-center" cellspacing="0"
               width="70%">
            <thead>
            <tr class="greentd" style="background-color: lightgrey" >
                <th>{{trans('company.hash') }}</th>
                <th>{{ trans('company.project_code') }}</th>
                <th>{{ trans('company.client') }}</th>
                <th>{{ trans('company.name') }}</th>
                <th>{{ trans('company.actions') }}</th>

            </tr>
            </thead>
            <tbody>
            @php
                $x = 1;
            @endphp
            @foreach ($projects_data as $project)

                <tr>
                    <td>{{ $x++ }}</td>
                    <td>{{ $project->project_code }}</td>
                    <td>{{ $project->client->name }}</td>
                    <td>{{ $project->project_name }}</td>


                    <td>
                        <div class="btn-group">
                            <a data-bs-toggle="modal" data-bs-target="#myModal" onclick="edit_project({{ $project->id }})" class="btn btn-sm btn-warning" title="{{ trans('company.edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('admin.company_delete_project', $project->id) }}" onclick="return confirm('Are You Sure To Delete?')" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
<div class="modal fade" tabindex="-1" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content" style="width: 800px !important;">
            <div class="modal-header">
                <h3 class="modal-title">Modal title</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1">&times;</i>
                </div>

            </div>

            <div class="modal-body" id="result_info">


            </div>

        </div>
    </div>
</div>

@section('js')
    <script>
        function edit_project(id)
        {
            $.ajax({
                url: "{{ route('admin.company_edit_project', ['id' => '__id__']) }}".replace('__id__', id),
                type: "get",
                dataType: "html",
                success: function (html) {
                    $('#result_info').html(html);
                },
            });
        }
    </script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\projects\CompanyClientRequest', '#') !!}

@endsection




