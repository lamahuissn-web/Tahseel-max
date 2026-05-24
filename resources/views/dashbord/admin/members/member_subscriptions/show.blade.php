<div class="d-flex justify-content-between flex-column">
    <!--begin::Table-->

    <div class="col-md-12">
        <table class="table table-bordered hl white-border" style="table-layout: fixed;">
            <tbody>
            <tr>
                <th style="">{{trans('members.member_name')}} </th>
                <td style=""> {{$all_data->member->member_name}}</td>

                <th style="">{{trans('members_subscription.process_nun')}}</th>
                <td style="">{{$all_data->process_num}}</td>

                <th style=" ">{{trans('members_subscription.process_date')}}</th>
                <td style="">{{$all_data->start_date}}</td>
            </tr>
            <tr>
                <th style=";">{{trans('members.main_subscription')}} </th>
                <td style=""> {{$all_data->main_subscriptions->name}}</td>

                <th style="">{{trans('members.start_date')}}</th>
                <td style="">{{$all_data->start_date}}</td>

                <th style=" ">{{trans('members.end_date')}}</th>
                <td style="">{{$all_data->end_date}}</td>
            </tr>

            <tr>
                <th style="">{{trans('members.discount')}} </th>
                <td style="">
                    {{$all_data->discount}}
                    @if($all_data->discount_type == 1 )
                        ({{trans('members_subscription.discount_lable')}})
                    @else
                    ({{trans('members_subscription.price_lable')}})
                    @endif
                </td>

                <th style="">{{trans('members.package_duration')}}</th>
                <td style="">{{$all_data->main_subscriptions->duration}}
                    ({{trans('members_subscription.duration_lable')}}
                    )
                </td>

                <th style=" ">{{trans('members.package_price')}}</th>
                <td style="">{{$all_data->main_subscriptions->price}}({{trans('members_subscription.price_lable')}}
                    )
                </td>
            </tr>

            <tr>
                <th style="">{{trans('members.transportation')}} </th>
                <?php $pay_method_arr = ['yes' => trans('members.subscribed'), 'no' => trans('members.not_subscribed')] ?>

                <td style=""> {{$pay_method_arr[$all_data->transport]}}</td>
                @if($all_data->transport=='yes')
                    <th style="">{{trans('members.transport_duration')}}</th>
                    <td style="">{{$all_data->main_subscriptions->duration}}
                        ({{trans('members_subscription.duration_lable')}}
                        )
                    </td>
                    <th style=" ">{{trans('members.transport_price')}}</th>
                    <td style="">{{$all_data->transport_value}} ({{trans('members_subscription.price_lable')}}
                        )
                    </td>
                @endif

            </tr>

            <tr>
                <th style="">{{trans('members.notes')}} </th>
                <td>{{ strip_tags($all_data->notes) }}</td>
            </tr>


            </tbody>
        </table>
    </div>


    <div class="table-responsive border-bottom mb-9 mt-20">
        <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
            <thead>
            <tr class="border-bottom fs-6 fw-bold ">
                <th class="min-w-175px text-center pb-2">{{trans('members_subscription.subscription')}}</th>
                <th class="min-w-175px text-center pb-2">{{trans('members_subscription.type')}}</th>
                <th class="min-w-70px text-center pb-2">{{trans('members_subscription.startDate')}}</th>
                <th class="min-w-80px text-center pb-2">{{trans('members_subscription.endDate')}}</th>
                <th class="min-w-100px text-center pb-2">{{trans('members_subscription.cost')}}</th>
                <th class="min-w-100px text-center pb-2">{{trans('members_subscription.trainer')}}</th>
                <th class="min-w-100px text-center pb-2">{{trans('members_subscription.discount')}}</th>
                <th class="min-w-100px text-center pb-2">{{trans('members_subscription.after_discount')}}</th>

            </tr>
            </thead>
            <tbody class="fw-semibold ">

            @foreach($all_data->additional_subscriptions as $item)
                <tr>
                    @if($item->type == 'main')

                        <td class="text-center"> {{$item->main_subscriptions->name}}</td>
                    @else
                        <td class="text-center"> {{$item->special_subscriptions->name}}</td>
                    @endif
                    <?php $type_arr = ['main' => trans('members.main'), 'special' => trans('members.special')] ?>
                    <td class="text-center"> {{$type_arr[$item->type]}}</td>
                    <td class="text-center"> {{$item->start_date}}</td>
                    <td class="text-center"> {{$item->end_date}}</td>
                    <td class="text-center"> {{$item->special_subscriptions->price}}
                        ({{trans('members_subscription.price_lable')}})
                    </td>
                    <td class="text-center"> {{$item->trainer->user_name}}</td>
                    <td class="text-center">
                        {{$item->discount}}
                        @if($item->discount_type == 1 )
                            ({{trans('members_subscription.discount_lable')}})
                        @else
                        ({{trans('members_subscription.price_lable')}})
                        @endif
                    </td>
                    <td class="text-center">
                        @if($item->discount_type == 1 )
                        {{((100-$item->discount)/100)*$item->special_subscriptions->price}}
                        @else
                            {{$item->special_subscriptions->price-$item->discount}}
                        @endif
                        ({{trans('members_subscription.price_lable')}}
                        )
                    </td>

                </tr>

            @endforeach
            </tbody>
        </table>
    </div>
    <!--end::Table-->
</div>
