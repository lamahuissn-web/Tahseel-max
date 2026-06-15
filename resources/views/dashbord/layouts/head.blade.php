<!--begin::Head-->
<title>@yield('title')</title>
<link rel="canonical" href="https://preview.keenthemes.com/keen" />
{{--<link rel="shortcut icon" href="{{asset('assets/media/logos/favicon.ico')}}"/>--}}
<link rel="shortcut icon" href="{{asset('assets/media/logos/favicon.ico')}}" />
<link rel="manifest" href="{{ asset('manifest.json') }}">
<!--begin::Fonts(mandatory for all pages)-->
{{--<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>--}}

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;700;800;900&amp;family=Roboto:wght@300;400;500;700;900&amp;display=swap" rel="stylesheet">

<link href="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
<!-- Font Awesome 6 (Free) - SVG+JS -->
<link href="{{asset("assets/css/fontawsome/svg-with-js.min.css")}}" rel="stylesheet" type="text/css" />
<script defer src="{{asset("assets/js/custom/fontawsome/all.min.js")}}"></script>
<link href="{{asset('assets/css/custome/fonts.css')}}" rel="stylesheet" type="text/css" />
@if(app()->getLocale() =='ar')
{{-- <link href="{{asset('assets/plugins/custom/prismjs/prismjs.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />--}}
{{--<link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css"/>--}}

{{-- <link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css"/>--}}
<link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
@else
{{-- <link href="{{asset('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css"/>--}}
<link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />

@endif
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600&family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
<style>
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    p,
    div,
    ul,
    li a,
    input,
    button,
    label,
    span,
    option,
    th,
    tr,
    i {
        font-family: 'Cairo', sans-serif !important;
        line-height: 1.7;
    }
</style>
<style>
    .t_container {
        padding: 30px;
        padding-top: 0px !important;
    }

    .container-xxl {
        max-width: 100% !important;

    }
</style>
@yield('css')