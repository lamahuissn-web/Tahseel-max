<div class="d-flex justify-content-between flex-column">
    <!--begin::Table-->

    <div class="col-md-12">
        <table class="table table-bordered hl white-border" style="table-layout: fixed;">
            <tbody>
            <tr>
                <th class="min-w-50px text-center pb-2"  >{{trans('members.member_name')}} </th>
                <td class="min-w-100px text-center pb-2" > {{$all_data[0]->member->member_name}}</td>
                <th class="min-w-50px text-center pb-2"  >{{trans('members_subscription.process_nun')}}</th>
                <td class="min-w-50px text-center pb-2" >{{$all_data[0]->process_num}}</td>
                <th class="min-w-50px text-center pb-2"  >{{trans('members_subscription.process_date')}}</th>
                <td class="min-w-50px text-center pb-2" >{{$all_data[0]->start_date}}</td>
                <th class="min-w-50px text-center pb-2">{{trans('members_subscription.payment_method')}}</th>
                <?php $pay_method_arr = ['cache' => trans('members.cache'), 'visa' => trans('members.visa'), 'bank' => trans('members.bank')] ?>
                <td class="text-center"> {{$pay_method_arr[$all_data[0]->pay_method]}}</td>      </tr>

            </tbody>
        </table>
    </div>



    <div class="table-responsive border-bottom mb-9" style="margin-top: 20px">
        <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
            <thead>
            <tr class="border-bottom fs-6 fw-bold text-muted">
                <th class="min-w-175px text-center pb-2">{{trans('members_subscription.subscription')}}</th>
                <th class="min-w-175px text-center pb-2">{{trans('members_subscription.type')}}</th>
                <th class="min-w-70px text-center pb-2">{{trans('members_subscription.startDate')}}</th>
                <th class="min-w-80px text-center pb-2">{{trans('members_subscription.endDate')}}</th>
                <th class="min-w-100px text-center pb-2">{{trans('members_subscription.cost')}}</th>
                <th class="min-w-100px text-center pb-2">{{trans('members_subscription.transportation')}}</th>
                <th class="min-w-100px text-center pb-2">{{trans('members_subscription.transport_value')}}</th>
                 </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">

             @foreach($all_data as $item)
                 <tr>
                     @if($item->type == 'main')

                         <td class="text-center"> {{$item->main_subscriptions->name}}</td>
                     @else
                         <td class="text-center"> {{$item->special_subscriptions->name}}</td>
                     @endif
                     <?php $type_arr=['main'=>trans('members.main'),'special'=>trans('members.special')] ?>
                     <td class="text-center"> {{$type_arr[$item->type]}}</td>
                     <td class="text-center"> {{$item->start_date}}</td>
                     <td class="text-center"> {{$item->end_date}}</td>
                     <td class="text-center"> {{$item->end_date}}</td>
                         <?php $type_arr=['yes'=>trans('members.yes'),'no'=>trans('members.no')] ?>

                         <td class="text-center"> {{$type_arr[$item->transport]}}</td>
                     <td class="text-center"> {{$item->transport_value}}</td>


                 </tr>

             @endforeach
            </tbody>
        </table>
    </div>
    <!--end::Table-->
</div>
