<div class="card">
    <!--begin::Body-->
    <div class="card-body p-lg-10 pb-lg-0">
        <!--begin::Table-->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="bg-primary text-white">
<<<<<<< HEAD
                    <tr>
                        <th>{{ trans('payment.attribute') }}</th>
                        <th>{{ trans('payment.value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ trans('payment.name') }}</td>
                        <td class="fs-6 fw-semibold text-dark">{{$one_data->name}}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('tax_setting.account_id') }}</td>
                        <td class="fs-6 fw-semibold text-dark">{{$one_data->account_data->name}}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('payment.status') }}</td>
                        <td class="fs-6 fw-semibold 
                            @if($one_data->status == 'active') text-success 
                            @elseif($one_data->status == 'notactive') text-danger 
                            @else text-dark 
                            @endif">
                            {{$one_data->status}}
                        </td>
                    </tr>
=======
                <tr>
                    <th>{{ trans('payment.attribute') }}</th>
                    <th>{{ trans('payment.value') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ trans('payment.name') }}</td>
                    <td class="fs-6 fw-semibold text-dark">{{$one_data->name}}</td>
                </tr>
                <tr>
                    <td>{{ trans('tax_setting.account_id') }}</td>
                    <td class="fs-6 fw-semibold text-dark">{{$one_data->account_data->name}}</td>
                </tr>
                <tr>
                    <td>{{ trans('payment.status') }}</td>
                    <td class="fs-6 fw-semibold
                            @if($one_data->status == 'active') text-success
                            @elseif($one_data->status == 'notactive') text-danger
                            @else text-dark
                            @endif">
                        {{$one_data->status}}
                    </td>
                </tr>
>>>>>>> bfe1015f4fee105d8bacb12c913adaf65c23fc49
                </tbody>
            </table>
        </div>
        <!--end::Table-->
    </div>
    <!--end::Body-->
</div>
