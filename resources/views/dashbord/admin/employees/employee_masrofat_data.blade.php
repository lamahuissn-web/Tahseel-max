<div class="" style="margin-top: 30px">
    @if(isset($masrofat_data) && !empty($masrofat_data))
        <table id="table" class="example table table-bordered responsive nowrap text-center" cellspacing="0"
               width="70%">
            <thead>
            <tr class="greentd" style="background-color: lightgrey" >
                <th>{{trans('employees.hash') }}</th>
                <th>{{ trans('employees.band') }}</th>
                <th>{{ trans('employees.value') }}</th>
                <th>{{ trans('employees.notes') }}</th>
                <th>{{ trans('employees.created_by') }}</th>
                <th>{{ trans('employees.created_at') }}</th>
                <th>{{ trans('employees.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @php
                $x = 1;
            @endphp
            @foreach ($masrofat_data as $masrof)
                <tr>
                    <td>{{ $x++ }}</td>
                    <td>{{ $masrof->sarf_band->title }}</td>
                    <td>{{ $masrof->value }}</td>
                    <td>{{ $masrof->notes }}</td>
                    <td>{{ $masrof->user->name }}</td>
                    <td class="fnt_center_black">{{ \Illuminate\Support\Carbon::parse($masrof->created_at)->format('Y-m-d') }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('admin.employee_delete_masrofat', $masrof->id) }}" onclick="return confirm('Are You Sure To Delete?')" class="btn btn-sm btn-danger">
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





