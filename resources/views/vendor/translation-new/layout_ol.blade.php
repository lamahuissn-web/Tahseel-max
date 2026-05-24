<!DOCTYPE html>
<html lang="en">
{{--<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
</head>--}}

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Real Estate Html Template">
    <meta name="author" content="">
    <meta name="generator" content="Jekyll">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Google fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/translation/css/main.css') }}">

@include('dashbord.layouts.head')

    <!-- Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@">
    <meta name="twitter:creator" content="@">
    <meta name="twitter:title" content="Dashboard">
    <meta name="twitter:description" content="Real Estate Html Template">

    <!-- Facebook -->
    <meta property="og:url" content="dashboard.html">
    <meta property="og:title" content="Dashboard">
    <meta property="og:description" content="Real Estate Html Template">
    <meta property="og:type" content="website">

    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
</head>


<body>

{{--  <div id="app">

      @include('translation::nav')
      @include('translation::notifications')

      @yield('body')

  </div>--}}
    <div id="app">

    <div class="wrapper dashboard-wrapper">
        <div class="d-flex flex-wrap flex-xl-nowrap">
        @include('dashbord.layouts.main-sidebar')

{{--        @include('translation::nav')--}}
{{--        @include('translation::notifications')--}}

        <!-- main-content -->
            <div class="page-content">
            @include('dashbord.layouts.main-headerbar')
            <!-- container -->
                <main id="content" class="bg-gray-01">
                    <div class="px-3 px-lg-6 px-xxl-13 py-5 py-lg-10" data-animated-id="1">
                        @yield('page-header')

                        @yield('body')
                    </div>

                </main>
            </div>
        </div>
    </div>
    </div>

    @include('dashbord.layouts.footer-scripts')

    <script src="{{ asset('vendor/translation/js/app.js') }}"></script>

</body>
</html>
