@extends('web_site.layouts.master')
@section('content')
<style>
  iframe {
      width: 100%;
      height: 200px;
  }
</style>
@php
  $mainData=getMainData();
@endphp


@endsection
@section('js')
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Site\ContactRequest', '#contact-form') !!}
@endsection



