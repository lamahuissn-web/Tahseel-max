<div class="d-flex justify-content-between flex-column">
    <!--begin::Table-->

    <div class="col-md-12">
        <table class="table table-bordered hl white-border" style="table-layout: fixed;">
            <tbody>
            <tr>
                <th style="">{{trans('accounting_entry.num')}} </th>
                <td style=""> {{$all_data->num}}</td>


                <th style=" ">{{trans('accounting_entry.date_at')}}</th>
                <td style="">{{$all_data->date_at}}</td>
                <th style="">{{trans('accounting_entry.create_by')}}</th>
                <td style="">{{$all_data->user->name}}</td>

            </tr>
            <tr>
                <th style=";">{{trans('accounting_entry.type')}} </th>
                <td style=""> {{$all_data->type}}</td>
                <th style=";">{{trans('accounting_entry.valueoflines')}} </th>
                <td style=""> {{$all_data->valueoflines()}}</td>

                <th style="">{{trans('accounting_entry.notes')}}</th>
                <td colspan="" style="">{{$all_data->notes}}</td>

            </tr>

            </tbody>
        </table>
    </div>

    <div class="table-responsive border-bottom mb-9 mt-20">
        <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
            <thead>
            <tr class="border-bottom fs-6 fw-bold ">
                <th class="min-w-175px text-center pb-2">{{trans('accounting_entry.account')}}</th>
                <th class="min-w-175px text-center pb-2">{{trans('accounting_entry.type')}}</th>
                <th class="min-w-70px text-center pb-2">{{trans('accounting_entry.amount')}}</th>
                <th class="min-w-80px text-center pb-2">{{trans('accounting_entry.notes')}}</th>

            </tr>
            </thead>
            <tbody class="fw-semibold ">

            @foreach($all_data->lines as $item)
                <tr>

                    <td class="text-center"> {{optional($item->account)->name}}</td>

                        <?php $type_arr = ['creditor' => trans('accounting_entry.Creditor'), 'debtor' => trans('accounting_entry.Debtor')] ?>
                    <td class="text-center"> {{$type_arr[$item->type]}}</td>
                    <td class="text-center"> {{$item->amount}}</td>
                    <td class="text-center"> {{$item->notes}}</td>

                </tr>

            @endforeach
            </tbody>
        </table>
    </div>
    <!--end::Table-->
</div>
