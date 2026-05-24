<div class="card">
    <!--begin::Body-->
    <div class="card-body p-lg-10 pb-lg-0">
        <!--begin::Table-->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>{{ trans('tax_setting.attribute') }}</th>
                        <th>{{ trans('tax_setting.value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ trans('tax_setting.name') }}</td>
                        <td class="fs-6 fw-semibold text-dark">{{$one_data->name}}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('tax_setting.account_id') }}</td>
                        <td class="fs-6 fw-semibold text-dark">{{$one_data->account_data->name}}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('tax_setting.type') }}</td>
                        <td class="fs-6 fw-semibold 
                        @if($one_data->type == 'with') text-success 
                        @elseif($one_data->type == 'without') text-danger 
                        @else text-dark 
                        @endif">
                        {{$one_data->type}}
                    </td>
                    </tr>
                    <tr>
                        <td>{{ trans('tax_setting.percentage') }}</td>
                        <td class="fs-6 fw-semibold text-dark">{{$one_data->percentage}}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('tax_setting.status') }}</td>
                        <td class="fs-6 fw-semibold 
                            @if($one_data->status == 'active') text-success 
                            @elseif($one_data->status == 'notactive') text-danger 
                            @else text-dark 
                            @endif">
                            {{$one_data->status}}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--end::Table-->
    </div>
    <!--end::Body-->
</div>


