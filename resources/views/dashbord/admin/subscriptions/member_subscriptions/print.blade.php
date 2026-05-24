<html>
<head>
    <title> فاتورة</title>
    <link href="https://fonts.googleapis.com/css?family=Almarai|Roboto&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{asset('assets/print/favicon.ico')}}" type="image/png">
    <link rel="stylesheet" href="{{asset('assets/print/bootstrap.css')}}">
    <style type="text/css">
        html {

            height: 100%; /* for the page to take full window height */
            box-sizing: border-box; /* to have the footer displayed at the bottom of the page without scrolling */
        }

        *,
        *:before,
        *:after {
            box-sizing: inherit; /* enable the "border-box effect" everywhere */
        }

        * {
            -webkit-print-color-adjust: exact !important; /* Chrome, Safari 6 – 15.3, Edge */
            color-adjust: exact !important; /* Firefox 48 – 96 */
            print-color-adjust: exact !important; /* Firefox 97+, Safari 15.4+ */
        }

        body {
            font-family: "Almarai", Roboto, sans-serif;

        }

        @media print {

            /*     #img-foot{
                     position: fixed;
                     bottom: 0;

                 }*/
            .report-container td, p {
                page-break-inside: avoid;
            }


            .table-bordered th, .table-bordered td {
                border: 1px solid #000 !important
            }


        }


        @page {
            size: 210mm 297mm;
            margin: 15px 10px 0px 10px;
        }


        .header-info, .header-space {
            height: 180px;
            margin-bottom: 10px;
        }

        .footer-info, .footer-space {
            height: 50px;
            padding: 15px
        }


        .footer-info {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #8a8d8a;
            color: #fff;
        }


        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
            /* font-family: 'STV Regular',serif !important; */
            /* font-weight: 600; */
            line-height: 1.1;
            color: inherit;
            font-family: "Almarai", Roboto, sans-serif;
        }

        h6 {
            font-size: 14px;
        }


        #bill {

            border-collapse: collapse;
            width: 100%;
        }

        #bill thead th {
            padding-top: 11px;
            padding-bottom: 11px;
            text-align: center;
            background-color: #dfe2df;
            color: #000000;
            font-size: 15px;
            font-weight: 600;
            direction: ltr;
        }

        #bill thead th {
            /*border: 1px solid #1a1919;*/
            padding: 14px;
            text-align: center;
            /* font-weight: 600; */
        }

        #bill td {
            border-bottom: 1px solid #1a1919;
            padding: 14px;
            text-align: center;
            font-size: 14px;
            background: #fff;
            direction: ltr;
        }

        .header-info {
            position: fixed;
            top: 0;
            width: 100%;
            border-bottom: 1px solid gray;
        }

        .headpar {
            font-size: 14px;
            font-weight: 600;
            line-height: 18px;
        }

        .align-items-center {
            align-items: center !important;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        .d-flex {
            display: flex !important;
        }

        .direction {
            direction: rtl;
        }

        .imglogo {
            width: 100%;
            /*background: #a879c6;*/
            border-radius: 10px;
            padding: 6px;
        }

        .bodypar {
            font-size: 14px;
            font-weight: 600;
            line-height: 27px;
            color: #9C27B0;
        }

        .bodypar span {
            color: #121212;
        }

        .detail {
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 20px;
        }

        .ullist li {
            margin-right: -20px;
            margin-bottom: 10px;
        }

        #bill1 {

            border-collapse: collapse;
            width: 100%;
        }

        #bill1 tfoot th {
            padding-top: 11px;
            padding-bottom: 11px;
            text-align: center;
            background-color: #dfe2df;
            color: #000000;
            font-size: 16px;
            font-weight: 600;
            direction: ltr;
        }

        #bill1 td {
            border: 0px solid #1a1919;
            padding: 10px;
            text-align: center;
            font-size: 18px;
            background: #fff;
            direction: ltr;
        }

    </style>
</head>
<body>

<div id="printdiv">
    @php
        $total=0;
        $final_total=0;
        $descount_total=0;
    @endphp
    <div class="first-part">
        <table class="report-container">
            <thead class="report-header">
            <tr>
                <th class="report-header-cell">
                    <div class="header-space">&nbsp;</div>
                </th>
            </tr>
            </thead>
            <tbody class="report-content">
            <tr>
                <td class="report-content-cell">
                    <div class="main">
                        <div class="bond-qabd">
                            <div class="container-fluid">
                                <div class="bond-body direction">
                                    <h3 class="detail"> {{trans('print.client_data')}}</h3>
                                    <div class="row pdd">
                                        <div class="col-xs-6">
                                            <p class="bodypar">{{trans('members.memner_name')}}
                                                <span>{{$one_data->member->member_name}} </span></p>
                                            <p class="bodypar"> {{trans('members.phone')}}
                                                :<span> {{$one_data->member->phone}}  </span></p>
                                            <p class="bodypar">{{trans('members.email')}}
                                                <span> {{$one_data->member->email}} </span></p>
                                        </div>
                                        <div class="col-xs-6">
                                            <table id="bill1" style="table-layout: fixed;">
                                                <tbody>
                                                <tr>
                                                    <th style="width: 165px">{{trans('print.refranc')}}</th>
                                                    <td> {{$one_data->process_num}}</td>
                                                </tr>

                                                {{-- <tr>
                                                     <th style="width: 165px"> نوع الفاتورة</th>
                                                     <td> فاتورة ضريبية مبسطة</td>
                                                 </tr>--}}
                                                <tr>
                                                    <th style="width: 165px">{{trans('print.subscription_date')}}</th>
                                                    <td> {{$one_data->start_date}}</td>
                                                </tr>

                                                {{--<tr>
                                                    <th style="width: 165px">تاريخ الاستحقاق</th>
                                                    <td> {{$one_data->start_date}}</td>
                                                </tr>--}}


                                                </tbody>

                                                <tfoot>
                                                <tr>
                                                    <th style="width: 165px">{{trans('print.wanted_value')}}</th>
                                                    <th id="final_total"> E</th>
                                                </tr>

                                                </tfoot>
                                            </table>

                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-xs-12 no-padding" style="margin-top: 2em;">
                                            <table id="bill" style="table-layout: fixed;margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th>{{trans('members_subscription.subscription')}}</th>
                                                    {{--                                                    <th>{{trans('members_subscription.type')}}</th>--}}
                                                    <th>{{trans('members_subscription.startDate')}}</th>
                                                    <th>{{trans('members_subscription.endDate')}}</th>
                                                    <th>{{trans('members_subscription.cost')}}</th>
                                                    {{--                                                    <th>{{trans('members_subscription.trainer')}}</th>--}}
                                                    <th>{{trans('members_subscription.discount')}}</th>
                                                    <th>{{trans('members_subscription.after_discount')}}</th>

                                                </tr>
                                                </thead>

                                                <tbody>
                                                <tr>
                                                    @if($one_data->type == 'main')

                                                        <td> {{$one_data->main_subscriptions->name}}</td>
                                                    @else
                                                        <td> {{$one_data->special_subscriptions->name}}</td>
                                                    @endif
                                                    <?php $type_arr = ['main' => trans('members.main'), 'special' => trans('members.special')] ?>
                                                    {{--                                                    <td> {{$type_arr[$one_data->type]}}</td>--}}
                                                    <td> {{$one_data->start_date}}</td>
                                                    <td> {{$one_data->end_date}}</td>
                                                    <td> {{$one_data->package_price}}
                                                        ({{trans('members_subscription.price_lable')}})
                                                    </td>
                                                    {{--                                                    <td> --</td>--}}
                                                    <td> {{$one_data->discount}}
                                                        @if($one_data->discount_type == 1 )
                                                            ({{trans('members_subscription.discount_lable')}})
                                                        @else
                                                        ({{trans('members_subscription.price_lable')}})
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($one_data->discount_type ==1)
                                                        {{((100-$one_data->discount)/100)*$one_data->package_price}}
                                                        @else
                                                            {{$one_data->package_price-$one_data->discount}}
                                                        @endif
                                                        ({{trans('members_subscription.price_lable')}})
                                                    </td>

                                                    @php

                                                        $total+=$one_data->package_price;
                                                        $descount_total+=$one_data->discount_type ==1 ? ($one_data->discount/100)*$one_data->package_price : ($one_data->discount);
                                                        $final_total+=$one_data->discount_type ==1 ? ($one_data->discount/100)*$one_data->package_price : ($one_data->package_price-$one_data->discount);
                                                    @endphp

                                                </tr>
                                                <tr>
                                                    <td colspan="1">{{trans('members.transportation')}} </td>
                                                    <?php $pay_method_arr = ['yes' => trans('members.subscribed'), 'no' => trans('members.not_subscribed')] ?>

{{--                                                    <td style=""> {{$pay_method_arr[$one_data->transport]}}</td>--}}
                                                    @if($one_data->transport=='yes')
                                                        <td> {{$one_data->start_date}}</td>
                                                        <td> {{$one_data->end_date}}</td>
                                                        <td style="">{{$one_data->transport_value}}
                                                            ({{trans('members_subscription.price_lable')}})
                                                        </td>

                                                        @php

                                                            $total+=$one_data->transport_value;
                                                            $final_total+=$one_data->transport_value;
                                                            $descount_total+=0;
                                                        @endphp
                                                    @else
                                                        <td colspan="4"></td>
                                                    @endif

                                                </tr>
                                                @foreach($one_data->additional_subscriptions as $item)
                                                    <tr>
                                                        @if($item->type == 'main')

                                                            <td> {{$item->main_subscriptions->name}}</td>
                                                        @else
                                                            <td> {{$item->special_subscriptions->name}}</td>
                                                        @endif
                                                        <?php $type_arr = ['main' => trans('members.main'), 'special' => trans('members.special')] ?>
                                                        {{--                                                        <td> {{$type_arr[$item->type]}}</td>--}}
                                                        <td> {{$item->start_date}}</td>
                                                        <td> {{$item->end_date}}</td>
                                                        <td> {{$item->cost}}
                                                            ({{trans('members_subscription.price_lable')}})
                                                        </td>
                                                        {{--                                                        <td> {{$item->trainer->user_name}}</td>--}}
                                                        <td> {{$item->discount}}
                                                            @if($item->discount_type == 1 )
                                                                ({{trans('members_subscription.discount_lable')}})
                                                            @else
                                                            ({{trans('members_subscription.price_lable')}})
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($item->discount_type ==1)
                                                            {{((100-$item->discount)/100)*$item->cost}}
                                                            @else
                                                                {{$item->cost-$item->discount}}
                                                            @endif
                                                            ({{trans('members_subscription.price_lable')}})
                                                        </td>
                                                        @php

                                                            $total+=$item->cost;
                                                            $final_total+=$item->discount_type ==1 ? ($item->discount/100)*$item->cost :($item->cost-$item->discount);
                                                            $descount_total+=$item->discount_type ==1 ? ($item->discount/100)*$item->cost :($item->discount);
                                                        @endphp
                                                    </tr>

                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                    <div class="row  d-flex align-items-center justify-content-between"
                                         style="margin-top:1em;">
                                        <div class="col-xs-4 no-padding">
                                            {!! $qrCode !!}
                                            {{--                                            <img src="{{asset('assets/print/qrcode.png')}}">--}}
                                        </div>

                                        <div class="col-xs-7 no-padding">
                                            <table id="bill1" style="table-layout: fixed;">
                                                <tbody>
                                                <tr>
                                                    <th style="width: 165px">{{trans('print.total')}}</th>
                                                    <td> {{$total}}</td>
                                                </tr>

                                                <tr>
                                                    <th style="width: 165px"> {{trans('print.descount')}}</th>
                                                    <td> {{$descount_total}}</td>

                                                </tr>


                                                </tbody>

                                                <tfoot>
                                                <tr>
                                                    <th style="width: 165px">{{trans('print.final_total')}}</th>
                                                {{--   <th> {{$final_total}}</th>--}}

                                                    <td> {{$total-$descount_total}}</td>
                                                </tr>

                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>


                                    <div class="row" style="margin-top: 1em;">
                                        <div class="col-xs-12 no-padding">
                                            <p>{!! $main_data->contract_terms !!}</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
            <tfoot class="report-footer">
            <tr>
                <td class="report-footer-cell">
                    <div class="footer-space">&nbsp;</div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>

    <div class="header-info direction d-flex align-items-center justify-content-between">
        <div class="col-xs-4">
            <img src="{{$main_data->image_print_url}}" class="imglogo">
        </div>

        <div class="col-xs-5 text-center">
            <p class="headpar"> {{trans('print.subscription_title')}} </p>
            <p class="headpar"> {{$main_data->name}} </p>
            <p class="headpar"> {{$main_data->address}} </p>
            <p class="headpar"> {{$main_data->phone}} </p>
            <p class="headpar"> {{trans('maindata.tax_number')}} : {{$main_data->tax_number}}</p>
            <p class="headpar"> {{trans('maindata.commercial_registration_number')}}
                : {{$main_data->commercial_registration_number}}</p>

        </div>
    </div>

</div>


<script type="text/javascript">
    document.getElementById('final_total').innerText = ' {{$total-$descount_total}}';
    window.print();

    window.onfocus = function () {
        window.close();
        window.onafterprint = function () {
            console.log("Printing completed...");
            // If window.close() is necessary, ensure the page was opened by script
            window.close();
        };
    }
</script>
<script>

    /*  document.addEventListener('DOMContentLoaded', function () {
          window.print();
          window.close();
      });*/

    /*setTimeout(function () {

        var divElements = document.getElementById("printdiv").innerHTML;
        var oldPage = document.body.innerHTML;

        document.body.innerHTML =
            "<html><head><title></title></head><body><div id='printdiv'>" +
            divElements + "</div></body>";

        $('#final_total').html('{{$final_total}}');
        // Print Page
        window.print();

        // After print finishes
        window.onafterprint = function () {
            console.log("Printing completed...");
            // If window.close() is necessary, ensure the page was opened by script
            // window.close(); // Optional, use with caution
        };
    }, 1000);*/

</script>
</body>

</html>
