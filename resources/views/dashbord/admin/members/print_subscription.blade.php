<head>

    <style type="text/css">
        .main-body {

            background-position: 100% 100%;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            -webkit-print-color-adjust: exact !important;
            height: 295mm;
        }
        .print_forma {
            padding: 80px 0 50px 0;
            /*border:1px solid #73b300;*/
        }
        /*.piece-box {
            !*margin-bottom: 12px;*!
            display: inline-block;
            width: 100%;
        }*/
        .piece-heading {
            background-color: #9bbb59;
            display: inline-block;
            float: right;
            width: 100%;
        }
        /*.piece-body {
            width: 100%;
            float: right;
        }*/
        .bordered-bottom {
            border-bottom: 4px solid #9bbb59;
        }
        .piece-footer {
            display: inline-block;
            float: right;
            width: 100%;
            border-top: 1px solid #73b300;
        }
        .piece-heading h5 {
            margin: 4px 0;
        }
        .piece-box table {
            margin-bottom: 0;
            font-size: 17px;
        }
        .piece-box table th,
        .piece-box table td {
        }
        .piece-box .table > thead > tr > th, .piece-box .table > tbody > tr > th,
        .piece-box .table > tfoot > tr > th, .piece-box .table > thead > tr > td,
        .piece-box .table > tbody > tr > td, .piece-box .table > tfoot > tr > td {
            text-align: center;
        }
        h6 {
            font-size: 16px;
            margin-bottom: 3px;
            margin-top: 3px;
        }
        .print_forma table th {
            text-align: right;
        }
        .print_forma table tr th {
            vertical-align: middle;
        }
        .no-padding {
            padding: 0;
        }
        .main-title {
            display: table;
            text-align: center;
            position: relative;
            height: 120px;
            width: 100%;
        }
        .main-title h4 {
            display: table-cell;
            vertical-align: bottom;
            text-align: center;
            width: 100%;
        }
        .print_forma hr {
            border-top: 1px solid #73b300;
            margin-top: 7px;
            margin-bottom: 7px;
        }
        .no-border {
            border: 0 !important;
        }
        .gray_background {
            background-color: #eee;
        }
        @media print {
            .table-bordered.double > thead > tr > th, .table-bordered.double > tbody > tr > th,
            .table-bordered.double > tfoot > tr > th, .table-bordered.double > thead > tr > td,
            .table-bordered.double > tbody > tr > td, .table-bordered.double > tfoot > tr > td {
                border: 2px solid #000 !important;
            }
            .table-bordered.white-border th, .table-bordered.white-border td {
                border: 1px solid #fff !important
            }
        }
        @page {
            size: 210mm 297mm  ;
            margin: 0;
        }
        .open_green {
            background-color: #e6eed5;
        }
        .closed_green {
            background-color: #cdddac;
        }
        .table-bordered.double {
            border: 5px double #000;
        }
        .table-bordered.white-border {
            margin-bottom: 3px;
        }
        .table-bordered.table-asnaf > thead > tr > th,
        .table-bordered.table-asnaf > thead > tr > td,
        .table-bordered.table-asnaf > tbody > tr > th,
        .table-bordered.table-asnaf > tbody > tr > td,
        .table-bordered.table-asnaf > tfoot > tr > th,
        .table-bordered.table-asnaf > tfoot > tr > td {
            border: 1px solid #000 !important;
            background: #fff !important;
            border-radius: 0px !important;
            font-size: 17px !important;
            color: black;
        }
        .table-bordered > thead > tr > th, .table-bordered > tbody > tr > th,
        .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td,
        .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > td {
            border: 2px solid #000;
        }
        .table-bordered.white-border > tbody > tr > th, .table-bordered.white-border > tbody > tr > td {
            border: 1px solid #eee !important;
            background: #fff;
            border-radius: 0px !important;
            font-size: 17px !important;
            color: black;
        }
        .under-line {
            border-top: 1px solid #abc572;
            padding-left: 0;
            padding-right: 0;
        }
        span.valu {
            padding-right: 10px;
            font-weight: 600;
            font-family: sans-serif;
        }
        .under-line .col-xs-3,
        .under-line .col-xs-4,
        .under-line .col-xs-6,
        .under-line .col-xs-8 {
            border-left: 1px solid #abc572;
        }
        .bond-header {
            height: 100px;
            margin-bottom: 30px;
        }
        .bond-header .right-img img,
        .bond-header .left-img img {
            width: 100%;
            height: 100px;
        }
        .main-bond-title {
            display: table;
            height: 100px;
            text-align: center;
            width: 100%;
        }
        .main-bond-title h3 {
            display: table-cell;
            vertical-align: bottom;
            color: #d89529;
        }
        .main-bond-title h3 span {
            border-bottom: 2px solid #006a3a;
        }
        .green-border span {
            /*
            border: 6px double #000;
            padding: 8px 25px;
            border-radius: 10px;
            box-shadow: 2px 2px 5px 2px #000;*/
            text-decoration: underline;
        }
        .table-bordered > tbody > tr td.rosasy-bg {
            background-color: #eee;
            border: 1px solid #fff;
        }
        .hl {
            font-family: 'hl';
        }
        .footer-info {
            position: absolute;
            width: 100%;
            bottom: 70px;
        }
        .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
            padding: 4px;
            line-height: 1.42857143;
            vertical-align: top;
            border-top: 1px solid #ddd;
        }
        h1, .h1, h2, .h2, h3, .h3 {
            margin-top: 6px;
            margin-bottom: 6px;
        }
        .bold{
            font-weight: bold !important;
        }
        .img-signature{margin-top: -15px; height: 65px; overflow: hidden;}
        .img-signature1{margin-top: -6px; height: 65px; overflow: hidden;}

        h6 p {
            line-height:  22px !important;
        }

    </style>
    <link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css"/>

    <link href="{{asset('assets/css/bootstrap-arabic-theme.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('assets/css/bootstrap-arabic.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">
        window.onload = function() {
            window.print();
        };
    </script>

</head>


<body id="printdiv">
<section class="main-body">
    <div class="print_forma  col-xs-12 ">
        <div class="piece-box no-padding"  >
            <div class="piece-body">
                <div class="col-xs-12 no-padding" style="    margin-top: 40px;">
                    <div class="col-xs-4 text-center">
                    </div>
                    <div class="col-xs-4 text-center">
                        <h6 class="green-border bold"><span >{{trans('members.print_members_subscription')}} </span></h6>
                    </div>
                    <div class="col-xs-4 text-center">
                    </div>
                </div>

                <div class="col-xs-12">
                    <table class="table table-bordered hl white-border" style="table-layout: fixed;">
                        <tbody>
                        <tr>
                            <td style="width: 45px" class="rosasy-bg">{{trans('members.member_name')}} </td>
                            <td style="width: 100px"> {{$all_data->member->member_name}}</td>
                            <td style="width: 45px" class="rosasy-bg">{{trans('members.subscription_name')}}</td>
                            <td style="width: 100px">{{$all_data->main_subscriptions->name}}</td>

                            <td style="width: 45px" class="rosasy-bg">{{trans('members.start_date')}}</td>
                            <td style="width: 100px">{{$all_data->start_date}}</td>
                        </tr>

                        </tbody>
                    </table>
                </div>

                <div class="col-xs-12">
                    <table class="table table-bordered hl white-border" style="table-layout: fixed;">
                        <tbody>
                        <tr>
                            <td style="width: 45px" class="rosasy-bg">{{trans('members.added_date')}} </td>
                            <td style="width: 100px"> {{$all_data->added_date}}</td>
                            <td style="width: 45px" class="rosasy-bg">{{trans('members.transportation')}}</td>
                            <td style="width: 100px">{{$all_data->transportation->car_type_setting->title}}</td>

                            <td style="width: 45px" class="rosasy-bg">{{trans('members.price')}}</td>
                            @if($all_data->type=='main')
                            <td style="width: 100px">{{$all_data->main_subscriptions->price}}</td>
                            @elseif($all_data->type=='special')
                                <td style="width: 100px">{{$all_data->special_subscriptions->price}}</td>
                                @endif
                        </tr>

                        </tbody>
                    </table>
                </div>





            </div>
        </div>
    </div>
</section>
</body>
</html>

