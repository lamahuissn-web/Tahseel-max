<div class="row">
    <input type="hidden" name="id" id="id" value="">
    <div class="col-md-8">
        <label for="nationalityTitle" class="form-label">{{ trans('settings.title') }}</label>
        <input type="text" class="form-control" id="title_setting" name="title_setting" required>
        <span id="error_title" class="invalid-feedback d-block"></span>
    </div>
    <div id="save-btn" class="col-md-2" style="margin: 28px;display: block" >
        <button type="button" onclick="add_setting('{{ $type }}','{{$input_id}}')" class="btn btn-success">{{ trans('settings.save') }}</button>
    </div>

    <div id="update-btn" class="col-md-2" style="margin: 28px;display: none">
        <button type="button" onclick="update_setting('{{ $type }}','{{$input_id}}')" class="btn btn-success">{{ trans('settings.update') }}</button>
    </div>
</div>

<br>
<br>
@if($all_data->count())
    <div class="table-responsive">
        <table id="table1" class="table table-bordered">
            <thead>
            <tr class="fw-bold fs-6 text-gray-800">
                <th style="width: 5%">{{ trans('settings.m') }}</th>
                <th style="text-align: center">{{ trans('settings.name') }}</th>
                <th style="width: 20%; text-align: center">{{ trans('settings.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @php $x = 0; @endphp
            @foreach($all_data as $row)
                <tr>
                    <td style="text-align: center">{{ $x++ }}</td>
                    <td style="text-align: center">{{ $row->title }}</td>
                    <td style="text-align: center">
                        <a onclick="edit_setting({{$row->id}},'{{$input_id}}','{{ $row->title }}')" class="btn btn-sm btn-warning edit-btn" title="{{ trans('settings.edit') }}">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a onclick="delete_setting({{$row->id}},'{{ $type }}','{{$input_id}}')" class="btn btn-sm btn-danger" title="{{ trans('settings.delete') }}">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@else
    <p>{{ trans('settings.No data available') }}</p>
@endif

    <script type="text/javascript">
        var save_method; // For the save method string
        var table;
        var dt;

    </script>
    <script>
        "use strict";
        var KTDatatablesServerSide = function () {

            var initDatatable = function () {
                table = $("#table1").DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: false,
                    order: [[1, 'desc']],
                    stateSave: true,

                });
            };

            return {
                init: function () {
                    initDatatable();
                }
            };
        }();

        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });
    </script>

