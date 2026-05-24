<!DOCTYPE html>
<html lang="en">

{{--@if(app()->getLocale() =='ar')
    <html direction="rtl" dir="rtl" style="direction: rtl">

    @else
    <html lang="en">

    @endif--}}
<!--begin::Head-->
    @php
        $mainData=getMainData();
    @endphp
<head>
    <base href="../../"/>
    <title>{{(!empty($mainData->name)) ? $mainData->name : 'Royal'}}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="#">
    <meta name="keywords" content="#">
    <meta http-equiv="x-ua-compatible" content="ie=edge">


    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('web_site.layouts.head')

</head>

<body >
  <!-- :: Loading -->
  <div class="loading">
    <div class="loading-box">
        <div class="lds-roller">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</div>



@include('web_site.layouts.main-headerbar')


@yield('content')

@include('web_site.layouts.footer')
   <!-- :: Scroll UP -->
   <div class="scroll-up">
    <a class="move-section" href="#page">
        <i class="fas fa-long-arrow-alt-up"></i>
    </a>
</div>

<!-- :: side social -->
<div class="sideSocial d-md-flex">
<a href="#" target="_blank" title="facebook"><i class="fab fa-whatsapp">  </i> </a>
 <a href="tel:0120347859" target="_blank" title="youtube"> <i class="fas fa-phone-alt">   </i>  </a>
</div>

@include('web_site.layouts.footer-scripts')


</body>

</html>
