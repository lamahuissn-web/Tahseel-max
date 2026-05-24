@extends('web_site.layouts.master')
@section('content')

    @php
        $mainData=getMainData();
    @endphp









@endsection
@section('js')
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Site\StudentRegistrationRequest', '#StorForm') !!}
@endsection



