{{--@extends('translation::layout')

@section('body')--}}
@extends('dashbord.layouts.master')

@section('page-header')

    <div class="d-flex flex-wrap flex-md-nowrap mb-6">
        <div class="mr-0 mr-md-auto">
            <h2 class="mb-0 text-heading fs-22 lh-15">{{ __('translation::translation.languages') }}
            </h2>
        </div>
        <div class="form-inline justify-content-md-end mx-n2">

        </div>
    </div>
@endsection
@section('content')
    @if(count($languages))



                <div class="table-responsive">
                    <table class="table table-hover bg-white border rounded-lg">

                    <thead>
                        <tr>
                            <th>{{ __('translation::translation.language_name') }}</th>
                            <th>{{ __('translation::translation.locale') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($languages as $language => $name)
                            <tr>
                                <td>
                                    {{ $name }}
                                </td>
                                <td>
                                    <a href="{{ route('languages.translations.index', $language) }}">
                                        {{ $language }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

    @endif

@endsection
