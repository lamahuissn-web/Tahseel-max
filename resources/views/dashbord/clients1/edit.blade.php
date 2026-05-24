@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('client.clients');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.clients.index')],
                ['label' => trans('Toolbar.clients'), 'link' => ''],
                ['label' => trans('client.clients_table'), 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ BackButton(route('admin.clients.index')) }}

        </div>
    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="app-container container-xxxl">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                generateCardHeader('clients.add_client', 'admin.clients.index', ' ');
            @endphp


            <form action="{{ route('admin.clients.update', $all_data->id) }}" method="post" enctype="multipart/form-data"
                id="store_form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="emp_code" class="form-label">{{ trans('clients.client_code') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="client_code" id="client_code"
                                    value="{{ $all_data->client_code }}" readonly>
                            </div>
                            @error('client_code')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="first_name" class="form-label">{{ trans('clients.name') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name', $all_data->name) }}">
                            </div>
                            @error('name')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- <div class="col-md-3" style="margin-top: 10px">
                            <label for="last_name" class="form-label">{{ trans('clients.phone') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('phone') !!}</span>
                                <input type="phone" class="form-control" name="phone" id="phone"
                                    value="{{ old('phone', $all_data->phone) }}">
                            </div>
                            @error('phone')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div> --}}

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="phone" class="form-label">{{ trans('clients.phone') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('phone') !!}</span>

                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                    name="phone" id="phone" value="{{ old('phone', $all_data->phone) }}"
                                    placeholder="123456789" maxlength="13">

                                <select class="form-select" id="country_code" style="max-width: 120px;">
                                    <option value="+961" {{ Str::startsWith(old('phone', $all_data->phone), '+961') ? 'selected' : '' }}>+961 (لبنان)</option>
                                    <option value="+20" {{ Str::startsWith(old('phone', $all_data->phone), '+20') ? 'selected' : '' }}>+20 (مصر)</option>
                                    <option value="+966" {{ Str::startsWith(old('phone', $all_data->phone), '+966') ? 'selected' : '' }}>+966 (السعودية)</option>
                                    <option value="+971" {{ Str::startsWith(old('phone', $all_data->phone), '+971') ? 'selected' : '' }}>+971 (الإمارات)</option>
                                    <option value="+213" {{ Str::startsWith(old('phone', $all_data->phone), '+213') ? 'selected' : '' }}>+213 (الجزائر)</option>
                                    <option value="+973" {{ Str::startsWith(old('phone', $all_data->phone), '+973') ? 'selected' : '' }}>+973 (البحرين)</option>
                                    <option value="+974" {{ Str::startsWith(old('phone', $all_data->phone), '+974') ? 'selected' : '' }}>+974 (قطر)</option>
                                    <option value="+965" {{ Str::startsWith(old('phone', $all_data->phone), '+965') ? 'selected' : '' }}>+965 (الكويت)</option>
                                    <option value="+968" {{ Str::startsWith(old('phone', $all_data->phone), '+968') ? 'selected' : '' }}>+968 (عُمان)</option>
                                    <option value="+962" {{ Str::startsWith(old('phone', $all_data->phone), '+962') ? 'selected' : '' }}>+962 (الأردن)</option>
                                    <option value="+963" {{ Str::startsWith(old('phone', $all_data->phone), '+963') ? 'selected' : '' }}>+963 (سوريا)</option>
                                    <option value="+964" {{ Str::startsWith(old('phone', $all_data->phone), '+964') ? 'selected' : '' }}>+964 (العراق)</option>
                                    <option value="+967" {{ Str::startsWith(old('phone', $all_data->phone), '+967') ? 'selected' : '' }}>+967 (اليمن)</option>
                                    <option value="+212" {{ Str::startsWith(old('phone', $all_data->phone), '+212') ? 'selected' : '' }}>+212 (المغرب)</option>
                                    <option value="+216" {{ Str::startsWith(old('phone', $all_data->phone), '+216') ? 'selected' : '' }}>+216 (تونس)</option>
                                    <option value="+218" {{ Str::startsWith(old('phone', $all_data->phone), '+218') ? 'selected' : '' }}>+218 (ليبيا)</option>
                                    <option value="+249" {{ Str::startsWith(old('phone', $all_data->phone), '+249') ? 'selected' : '' }}>+249 (السودان)</option>
                                    <option value="+252" {{ Str::startsWith(old('phone', $all_data->phone), '+252') ? 'selected' : '' }}>+252 (الصومال)</option>
                                    <option value="+253" {{ Str::startsWith(old('phone', $all_data->phone), '+253') ? 'selected' : '' }}>+253 (جيبوتي)</option>
                                    <option value="+222" {{ Str::startsWith(old('phone', $all_data->phone), '+222') ? 'selected' : '' }}>+222 (موريتانيا)</option>
                                    <option value="+970" {{ Str::startsWith(old('phone', $all_data->phone), '+970') ? 'selected' : '' }}>+970 (فلسطين)</option>
                                    <option value="+1268" {{ Str::startsWith(old('phone', $all_data->phone), '+1268') ? 'selected' : '' }}>+1268 (جزر القمر)</option>
                                </select>
                            </div>
                            @error('phone')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>


                        {{-- <div class="col-md-3" style="margin-top: 10px">
                            <label for="email" class="form-label">{{ trans('clients.email') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('email') !!}</span>
                                <input type="text" class="form-control" name="email" id="email"
                                    value="{{ old('email', $all_data->email) }}">
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div> --}}

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="user" class="form-label">{{ trans('clients.user') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="user" id="user"
                                        value="{{ old('user', $all_data->user) }}">
                            </div>
                            @error('user')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                    {{-- </div>

                    <div class="col-md-12 row" style="margin-top: 10px"> --}}
                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="national_id" class="form-label">{{ trans('clients.address1') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('address') !!}</span>
                                <input type="text" class="form-control" name="address1" id="address1"
                                    value="{{ old('address1', $all_data->address1) }}">
                            </div>
                            @error('address1')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- <div class="col-md-3" style="margin-top: 10px">
                            <label for="national_id" class="form-label">{{ trans('clients.address2') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('address') !!}</span>
                                <input type="text" class="form-control" name="address2" id="address2"
                                    value="{{ old('address2', $all_data->address2) }}">
                            </div>
                            @error('address2')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div> --}}
                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="box_switch" class="form-label">{{ trans('clients.box_switch') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="box_switch" id="box_switch"
                                        value="{{ old('box_switch', $all_data->box_switch) }}">
                            </div>
                            @error('box_switch')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="client_type" class="form-label">{{ trans('clients.client_type') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon4">{!! form_icon('select2') !!}</span>
                                <select class="form-select" name="client_type" id="client_type">
                                    <option value="satellite" {{ old('client_type', $all_data->client_type) == 'satellite' ? 'selected' : '' }}>
                                        {{ trans('clients.satellite') }}
                                    </option>
                                    <option value="internet" {{ old('client_type', $all_data->client_type) == 'internet' ? 'selected' : '' }}>
                                        {{ trans('clients.internet') }}
                                    </option>
                                </select>
                            </div>
                            @error('client_type')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>

                        {{-- <div class="col-md-3" style="margin-top: 10px">
                            <label for="whatsapp_num" class="form-label">{{ trans('clients.commercial_register') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('number') !!}</span>
                                <input type="number" class="form-control" name="commercial_register"
                                    id="commercial_register"
                                    value="{{ old('commercial_register', $all_data->commercial_register) }}">
                            </div>
                            @error('commercial_register')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div> --}}

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="basic-url"class="form-label">{{ trans('clients.subscription') }}</label>
                            <div class="input-group flex-nowrap ">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select1') !!}</span>
                                <div class="overflow-hidden flex-grow-1">
                                    <select class="form-select rounded-start-0" name="subscription_id"
                                        id="subscription_id" onchange="get_price(this.value)" data-placeholder="{{ trans('clients.select') }}">
                                        <option value="">{{ trans('clients.select') }}</option>
                                        @foreach ($subscriptions as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('subscription_id', $all_data->subscription_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('subscription_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="price" class="form-label">{{ trans('clients.price') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('price') !!}</span>
                                <input type="text" class="form-control" id="price" name="price" readonly>
                            </div>
                        </div>
                    {{-- </div>

                    <div class="col-md-12 row" style="margin-top: 10px"> --}}
                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="subscription_date"
                                class="form-label">{{ trans('clients.subscription_date') }}</label>
                            <div class="input-group flex-nowrap ">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('date') !!}</span>
                                <input type="date" class="form-control" name="subscription_date"
                                    id="subscription_date"
                                    value="{{ old('subscription_date', $all_data->subscription_date) }}">
                                @error('subscription_date')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="start_date" class="form-label">{{ trans('clients.start_date') }}</label>
                            <div class="input-group flex-nowrap ">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('date') !!}</span>
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                    value="{{ old('start_date', $all_data->start_date) }}">
                                @error('start_date')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="is_active" class="form-label">{{ trans('clients.status') }}</label>
                            <div class="input-group flex-nowrap ">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select1') !!}</span>
                                <select class="form-select" name="is_active" id="is_active">
                                    <option value="1"
                                        {{ old('is_active', $all_data->is_active) == 1 ? 'selected' : '' }}>
                                        {{ trans('clients.active') }}</option>
                                    <option value="0"
                                        {{ old('is_active', $all_data->is_active) == 0 ? 'selected' : '' }}>
                                        {{ trans('clients.inactive') }}</option>
                                </select>
                            </div>
                            @error('is_active')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" style="margin-top: 10px">
                            <label for="phone" class="form-label">{{ trans('clients.image') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('image') !!}</span>
                                <input type="file" class="form-control" name="image" id="image"
                                    value="{{ old('image') }}">
                            </div>
                            @error('image')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror

                            @if ($all_data->image)
                                <div class="mt-2">
                                    <img src="{{ asset('images/' . $all_data->image) }}" alt="Current Photo"
                                        class="img-thumbnail" width="100">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label for="area" class="form-label">{{ trans('clients.notes') }}</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text" id="basic-addon3">
                                {!! form_icon('text') !!}
                            </span>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                                aria-describedby="basic-addon3" rows="3">{{ old('notes', $all_data->notes) }}</textarea>
                        </div>
                        @error('notes')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>


                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        {{ trans('clients.save') }}
                    </button>
                </div>
            </form>
        </div>

    </div>












@stop
@section('js')
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $("#subscription_id").trigger("change");
            }, 300);
        });
    </script>
    <script>
        function get_price(id) {
            $.ajax({
                url: "{{ route('admin.get_price', ['id' => '__id__']) }}".replace('__id__', id),
                type: "get",
                dataType: "json",
                success: function(data) {
                    $('#price').val(data.price || '<?= old('price') ?>');
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching price:", status, error);
                    $('#price').val('0');
                }
            });
        }
    </script>
    <script>
        function showSuccessMessage(message) {
            $('#success_message').text(message).removeClass('d-none').show();
            setTimeout(function() {
                $('#success_message').fadeOut().addClass('d-none');
            }, 8000);
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countryCodeSelect = document.getElementById('country_code');
            const phoneInput = document.getElementById('phone');

            function extractNumber(phone) {
                return phone.replace(/^\+\d{1,3}/, '');
            }

            function updatePhoneInput() {
                const currentNumber = extractNumber(phoneInput.value);
                phoneInput.value = countryCodeSelect.value + currentNumber;
            }

            if (phoneInput.value && !phoneInput.value.startsWith('+')) {
                updatePhoneInput();
            }

            countryCodeSelect.addEventListener('change', updatePhoneInput);

            phoneInput.addEventListener('blur', function() {
                if (!phoneInput.value.startsWith('+')) {
                    updatePhoneInput();
                }
            });
        });
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Admin\clients\SaveRequests', '#store_form1') !!}



@endsection
