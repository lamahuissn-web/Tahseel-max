@extends('translation::layout')

@section('page-header')


    <div class="d-flex flex-wrap flex-md-nowrap mb-6">
        <div class="mr-0 mr-md-auto">
            <h2 class="mb-0 text-heading fs-22 lh-15">    {{ __('translation::translation.translations') }}
            </h2>
        </div>
        <form action="{{ route('languages.translations.index', ['language' => $language]) }}" method="get">
            <div class="input-group input-group-lg bg-white border">

                {{--                    @include('translation::forms.search', ['name' => 'filter', 'value' => Request::get('filter')])--}}

                {{--                    @include('translation::forms.select', ['name' => 'language', 'items' => $languages, 'submit' => true, 'selected' => $language])--}}

                <div class="sm:hidden lg:flex items-center">

                    @include('translation::forms.select', ['name' => 'group', 'items' => $groups, 'submit' => true, 'selected' => Request::get('group'), 'optional' => true])
                    @can('add_word_translate')

                        <a href="{{ route('languages.translations.create', $language) }}" class="button">
                            {{ __('translation::translation.add') }}
                        </a>
                    @endcan
                </div>

            </div>
        </form>
    </div>
@endsection
@section('body')

    <form action="{{ route('languages.translations.index', ['language' => $language]) }}" method="get">

    {{--      <div class="card">

              <div class="card-header">

                  {{ __('translation::translation.translations') }}

                  <div class="flex flex-grow justify-end items-center">

  --}}{{--                    @include('translation::forms.search', ['name' => 'filter', 'value' => Request::get('filter')])--}}{{--

  --}}{{--                    @include('translation::forms.select', ['name' => 'language', 'items' => $languages, 'submit' => true, 'selected' => $language])--}}{{--

                      <div class="sm:hidden lg:flex items-center">

                          @include('translation::forms.select', ['name' => 'group', 'items' => $groups, 'submit' => true, 'selected' => Request::get('group'), 'optional' => true])
                          @can('add_word_translate')

                              <a href="{{ route('languages.translations.create', $language) }}" class="button">
                                  {{ __('translation::translation.add') }}
                              </a>
                          @endcan
                      </div>

                  </div>

              </div>

              <div class="card-body">--}}

    @if(count($translations))
        <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{trans('viewdata.Car Details')}}</h2>
                    </div>
                    <!--end::Card title-->
                    <!--begin::Card toolbar-->

                </div>
                <!--end::Card header-->
                <!------------------------------------------------------------------------------------------->


                <!--begin::Card body-->
                <div class="card-body pt-0 pb-5">
                    <div class="table-responsive">

                        <table class="table table-striped border rounded gy-5 gs-7" id="words-list">

                            <thead>
                            <tr>
                                <th class=" uppercase font-thin">{{ __('translation::translation.group_single') }}</th>
                                <th class=" uppercase font-thin">{{ __('translation::translation.key') }}</th>
                                <th class="uppercase font-thin">{{ config('app.locale') }}</th>
                                <th class="uppercase font-thin">{{ $language }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($translations as $type => $items)

                                @foreach($items as $group => $translations)

                                    @foreach($translations as $key => $value)

                                        @if(!is_array($value[config('app.locale')]))
                                            <tr>
                                                <td>{{ $group }}</td>
                                                <td>{{ $key }}</td>
                                                <td>{{ $value[config('app.locale')] }}</td>
                                                <td>
                                                    <translation-input
                                                            initial-translation="{{ $value[$language] }}"
                                                            language="{{ $language }}"
                                                            group="{{ $group }}"
                                                            translation-key="{{ $key }}"
                                                            route="{{ config('translation.ui_url') }}">
                                                    </translation-input>
                                                </td>
                                            </tr>
                                        @endif

                                    @endforeach

                                @endforeach

                            @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{--</div>

    </div>--}}

    </form>

@endsection
@section('js')

    <script>
        $(document).ready(function () {
            var table = $('#words-list').DataTable();
        });
    </script>
@endsection
