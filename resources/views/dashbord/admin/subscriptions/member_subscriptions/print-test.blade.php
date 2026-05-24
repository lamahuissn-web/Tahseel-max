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
            font-size: 15px;
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
            font-size: 16px;
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
            font-size: 17px;
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
            font-size: 15px;
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
            background: #a879c6;
            border-radius: 10px;
            padding: 6px;
        }

        .bodypar {
            font-size: 16px;
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
                                    <h3 class="detail"> تفاصيل بيانات العميل</h3>
                                    <div class="row pdd">
                                        <div class="col-xs-6">
                                            <p class="bodypar"> اسم العميل :- <span> منيرة سعود دعيج العتيبى </span></p>
                                            <p class="bodypar"> رقم الفاتورة :- <span> 10  </span></p>
                                            <p class="bodypar"> تاريخ الفاتورة :- <span> 22-10-2023  </span></p>
                                            <p class="bodypar"> رقم الاشتراك :- <span> 15  </span></p>
                                            <p class="bodypar"> تاريخ الاشتراك :- <span> 22-10-2023  </span></p>
                                        </div>
                                        <div class="col-xs-6">
                                            <table id="bill1" style="table-layout: fixed;">
                                                <tbody>
                                                <tr>
                                                    <th style="width: 165px">المرجع</th>
                                                    <td> Nv379</td>
                                                </tr>

                                                <tr>
                                                    <th style="width: 165px"> نوع الفاتورة</th>
                                                    <td> فاتورة ضريبية مبسطة</td>
                                                </tr>
                                                <tr>
                                                    <th style="width: 165px">تاريخ الاصدار</th>
                                                    <td> 2024-08-10</td>
                                                </tr>

                                                <tr>
                                                    <th style="width: 165px">تاريخ الاستحقاق</th>
                                                    <td> 2024-08-17</td>
                                                </tr>


                                                </tbody>

                                                <tfoot>
                                                <tr>
                                                    <th style="width: 165px">المبلغ المستحق</th>
                                                    <th> 2097 L.E</th>
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
                                                    <th>الاشتراك</th>
                                                    <th> تاريخ البداية</th>
                                                    <th>تاريخ النهاية</th>
                                                    <th> عدد السيشن</th>
                                                    <th>التكلفة</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                <tr>
                                                    <td> باقة ستة اشهر</td>
                                                    <td> 2024-08-17</td>
                                                    <td> 2025-02-17</td>
                                                    <td> 4</td>
                                                    <td> 2097 L.E</td>
                                                </tr>

                                                <tr>
                                                    <td> باقة ستة اشهر</td>
                                                    <td> 2024-08-17</td>
                                                    <td> 2025-02-17</td>
                                                    <td> 4</td>
                                                    <td> 2097 L.E</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                    <div class="row  d-flex align-items-center justify-content-between"
                                         style="margin-top:1em;">
                                        <div class="col-xs-4 no-padding">
                                            <img src="{{asset('assets/print/qrcode.png')}}">
                                        </div>

                                        <div class="col-xs-7 no-padding">
                                            <table id="bill1" style="table-layout: fixed;">
                                                <tbody>
                                                <tr>
                                                    <th style="width: 165px">المجموع</th>
                                                    <td> 4194 L.E</td>
                                                </tr>

                                                <tr>
                                                    <th style="width: 165px">بعد الخصم</th>
                                                    <td> 2097 L.E</td>
                                                </tr>
                                                <tr>
                                                    <th style="width: 165px">التنقل</th>
                                                    <td> 0.00 L.E</td>
                                                </tr>


                                                </tbody>

                                                <tfoot>
                                                <tr>
                                                    <th style="width: 165px">المجموع الكلي</th>
                                                    <th> 2097 L.E</th>
                                                </tr>

                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>


                                    <div class="row" style="margin-top: 1em;">
                                        <div class="col-xs-12 no-padding">
                                            <p> هذا النص تجريبى ويمكن حذفه هذا النص تجريبى ويمكن حذفه هذا النص تجريبى
                                                ويمكن حذفه هذا النص تجريبى ويمكن حذفه هذا النص تجريبى ويمكن حذفه هذا
                                                النص تجريبى ويمكن حذفه هذا النص تجريبى ويمكن حذفه هذا النص تجريبى ويمكن
                                                حذفه هذا النص تجريبى ويمكن حذفه هذا النص تجريبى ويمكن حذفه </p>

                                            <ul class="ullist">
                                                <li> هذا النص تجريبى ويمكن حذفه</li>
                                                <li> هذا النص تجريبى ويمكن حذفه</li>

                                            </ul>
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
            <img src="{{asset('assets/print/logo.png')}}" class="imglogo">
        </div>

        <div class="col-xs-5 text-center">
            <p class="headpar"> فاتورة ضريبية مبسطة </p>
            <p class="headpar"> مؤسسة مستقبل الشرق للأفراح </p>
            <p class="headpar"> طريق ابوبكر الصديق - الرياض - السعودية </p>
            <p class="headpar"> 05959592511 - 0549529851 - 0508737349 </p>
            <p class="headpar"> الرقم الضريبى :- 3103478192000003</p>
            <p class="headpar"> رقم السجل التجارى:- 1118104630 </p>

        </div>
    </div>

</div>

<script>
    setTimeout(function () {
        var divElements = document.getElementById("printdiv").innerHTML;
        var oldPage = document.body.innerHTML;

        document.body.innerHTML =
            "<html><head><title></title></head><body><div id='printdiv'>" +
            divElements + "</div></body>";


        window.print();
        // window.close();


        //Restore orignal HTML
        document.body.innerHTML = oldPage;

        window.location = "{{route('admin.subscriptions.member-subscriptions.index')}}";
    }, 1000);

</script>
</body>

</html>
