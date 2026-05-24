@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{trans('members_subscription.invoice')}}
                {{$one_data[0]->process_num}}</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">
                    <a href="{{route('admin.dashboard')}}" class="text-muted text-hover-primary">{{trans('maindata.home')}}</a>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">{{trans('members_subscription.view_invoices')}}</li>
                <!--end::Item-->
            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--end::Page title-->

    </div>
    <!--end::Toolbar container-->
@endsection
@section('content')
    @php
        $mainData=getMainData();
        $member=optional($one_data[0]->member);
       /* if ($one_data->type=='main'){
            $subscriptions=optional($one_data->main_subscriptions);
        }elseif ($one_data->type=='special'){
            $subscriptions=optional($one_data->special_subscriptions);

        }
    $total=$subscriptions->price;
    if($one_data->discount > 0){
        $total=$subscriptions->price * ((100-$one_data->discount)/100);
    }
    if($one_data->transport){
        $total=$total+100;
    }*/


    @endphp
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!-- begin::Invoice 3-->
        <div class="card">
            <!-- begin::Body-->
            <div class="card-body py-20">
                <!-- begin::Wrapper-->
                <div class="mw-lg-950px mx-auto w-100">
                    <!-- begin::Header-->
                    <div class="d-flex justify-content-between flex-column flex-sm-row mb-19">
                        <h4 class="fw-bolder text-gray-800 fs-2qx pe-5 pb-7">{{trans('members_subscription.invoice')}}</h4>
                        <!--end::Logo-->
                        <div class="text-sm-end">
                            <!--begin::Logo-->
                            <a href="#" class="d-block mw-150px ms-sm-auto" style=" background-color: lightslategray;padding: 8px; border-radius: 25px;">
                                <img alt="Logo"
                                     src="{{asset((!empty($mainData->image)) ? $mainData->image : 'assets/media/logos/favicon.ico')}}"
                                     class="w-100"/>
                            </a>
                            <!--end::Logo-->
                            <!--begin::Text-->
                            <div class="text-sm-end fw-semibold fs-4 text-muted mt-7">
                                <div>{{(!empty($mainData->name)) ? $mainData->name : 'rashaketik'}}</div>
                                <div>{{(!empty($mainData->phone)) ? $mainData->phone : 'rashaketik'}}</div>
                            </div>
                            <!--end::Text-->
                        </div>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="pb-12">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column gap-7 gap-md-10">
                            <!--begin::Message-->
                            <div class="fw-bold fs-2">{{$member->member_name}}
                                <span class="fs-6">({{$member->email}})</span>,
                                <br/>
                                <span class="text-muted fs-5">{{trans('members_subscription.order_details')}}</span>
                            </div>
                            <!--begin::Message-->
                            <!--begin::Separator-->
                            <div class="separator"></div>
                            <!--begin::Separator-->
                            <!--begin::Order details-->
                            <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">{{trans('members_subscription.order_id')}}</span>
                                    <span class="fs-5">#{{$one_data[0]->process_num}}</span>
                                </div>
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">{{trans('members_subscription.order_date')}}</span>
                                    <span class="fs-5">{{formatDateDayDisplay($one_data[0]->created_at)}}</span>
                                </div>
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">{{trans('members_subscription.invoice_id')}}</span>
                                    <span class="fs-5">#{{$one_data[0]->process_num}}</span>
                                </div>

                            </div>

                            <div class="d-flex justify-content-between flex-column">
                                <!--begin::Table-->
                                <div class="table-responsive border-bottom mb-9">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                        <tr class="border-bottom fs-6 fw-bold text-muted">
                                            <th class="min-w-175px text-center pb-2">{{ trans('members_subscription.subscription') }}</th>
                                            <th class="min-w-70px text-center pb-2">{{ trans('members_subscription.startDate') }}</th>
                                            <th class="min-w-80px text-center pb-2">{{ trans('members_subscription.endDate') }}/{{ trans('members_subscription.secessionNum') }}</th>
                                            <th class="min-w-100px text-center pb-2">{{ trans('members_subscription.cost') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                        @foreach($one_data as $item)

                                               <?php
                                               $subscriptions=[];
                                               if($item->type == 'main')
                                               {
                                                   $subscriptions= $item->main_subscriptions;
                                               }  else{
                                                   $subscriptions= $item->special_subscriptions;
                                               }
                                               ?>

                                            <tr>


                                                <!--end::Start Date-->
                                                <!--begin::Subscription Name-->
                                                @if($item->type == 'main')
                                                    <td class="text-center"> {{ $item->main_subscriptions->name }}</td>
                                                @else
                                                    <td class="text-center"> {{ $item->special_subscriptions->name }}</td>
                                            @endif
                                                <td class="text-center">{{ formatDateDayDisplay($item->start_date) }}</td>
                                            <!--end::Subscription Name-->
                                                <!--begin::Cost-->
                                                <td class="text-center"> {{ $subscriptions->price * ((100 - $item->discount) / 100) }}</td>
                                                <!--end::Cost-->
                                            </tr>

                                            @if($item->transport)
                                                <tr>
                                                    <!--begin::Transport-->
                                                    <td class="text-center">{{ trans('members_subscription.transport') }}</td>
                                                    <!--end::Transport-->
                                                    <!--begin::Start Date-->
                                                    <td class="text-center">{{ formatDateDayDisplay($item->start_date) }}</td>
                                                    <!--end::Start Date-->
                                                    <!--begin::Quantity-->
                                                    <td class="text-center">-</td>
                                                    <!--end::Quantity-->
                                                    <!--begin::Cost-->
                                                    <td class="text-center">100</td>
                                                    <!--end::Cost-->
                                                </tr>
                                            @endif

                                            <!--begin::Subtotal-->
                                            <?php
                                            $subtotal = $subscriptions->price * ((100 - $item->discount) / 100);
                                            if ($item->transport) {
                                                $subtotal += 100;
                                            }
                                            ?>
                                            <tr>
                                                <td colspan="3" class="text-end">{{ trans('members_subscription.Subtotal') }}</td>
                                                <td class="text-end">{{ $subtotal }}</td>
                                            </tr>

                                            <!--begin::Discount-->
                                            @if($item->discount > 0)
                                                <tr>
                                                    <td colspan="3" class="text-end">{{ trans('members_subscription.discount') }}</td>
                                                    <td class="text-end">{{ $subscriptions->price * ($item->discount / 100) }}</td>
                                                </tr>
                                            @endif

                                            <!--begin::Shipping-->
                                            @if($item->transport)
                                                <tr>
                                                    <td colspan="3" class="text-end">{{ trans('members_subscription.transport') }}</td>
                                                    <td class="text-end">100</td>
                                                </tr>
                                            @endif

                                            <!--begin::Grand Total-->
                                            <?php
                                            $total = $subtotal; // Use subtotal for total calculation
                                            ?>
                                            <tr>
                                                <td colspan="3" class="fs-3 text-dark fw-bold text-end">{{ trans('members_subscription.total') }}</td>
                                                <td class="text-dark fs-3 fw-bolder text-end">{{ $total }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!--end::Table-->
                            </div>
                            <!--end:Order summary-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Body-->
                    <!-- begin::Footer-->
                    <div class="d-flex flex-stack flex-wrap mt-lg-20 pt-13">
                        <!-- begin::Actions-->
                        <div class="my-1 me-5">
                            <!-- begin::Pint-->
                            <button type="button" class="btn btn-success my-1 me-12" onclick="window.print();">
                                {{trans('members_subscription.print_invoice')}}
                            </button>
                            <!-- end::Pint-->

                        </div>
                        <!-- end::Actions-->

                    </div>
                    <!-- end::Footer-->
                </div>
                <!-- end::Wrapper-->
            </div>
            <!-- end::Body-->
        </div>
        <!-- end::Invoice 1-->
    </div>
    <!--end::Content container-->

@endsection
@section('js')
@endsection
