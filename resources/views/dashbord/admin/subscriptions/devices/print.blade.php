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

        .mb10 {
            margin-bottom: 10px;
        }

        .padding10 {
            padding-top: 20px;
        }

        .headqr {
            margin-top: 7px;
            margin-bottom: 5px;
        }

        .hed-title {
            text-align: center;
            font-weight: 700;
            margin-bottom: 50px;
            font-size: 30px;
        }

        .border {
            border-left: 1px solid black;
            border-bottom: 1px solid black;
            text-align: center;
        }

        .border:last-child {
            border-left: 0px solid #fdfdfd33;
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
    <div class="container">

        <h3 class="detail hed-title"> {{trans('print.deviceQR_data')}}</h3>

        @if(isset($list_data)&&(!empty($list_data)))
            @foreach ($list_data->chunk(3) as $chunk)
                <div class="row "
                    >
                    @foreach($chunk as $one_data)
                        @php
                            $dataQR = $one_data->id;
                        @endphp
                        <div class="col-xs-4 no-padding padding10 border">
                            {{--                                                    <p>{{$one_data->name }} - {{$one_data->code}}</p>--}}
                            {!! \QrCode::size(150)->generate($dataQR) !!}
                            {{--                                            <img src="{{asset('assets/print/qrcode.png')}}">--}}
                            <h3 class="headqr">{{$one_data->name }}</h3>
                            <p> {{$one_data->code}}</p>
                        </div>
                    @endforeach
                </div>
            @endforeach
           
        @endif

                            </div>


    </div>






<script type="text/javascript">
    window.print();

    window.onfocus = function () {
        window.print();

        // window.close();
        window.onafterprint = function () {
            console.log("Printing completed...");
            // If window.close() is necessary, ensure the page was opened by script
            // window.close(); // Optional, use with caution
        };
    }
</script>
</body>

</html>
