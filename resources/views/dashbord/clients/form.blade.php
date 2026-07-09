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

    <div id="kt_app_content_container" class="app-container container-xxl">

        <div class="card shadow-sm card-border-top-primary">
            @php
                generateCardHeader('clients.add_client', 'admin.clients.index', ' ');
            @endphp


            <form action="{{ route('admin.clients.store') }}" method="post" enctype="multipart/form-data" id="store_form">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 form-field">
                            <label for="emp_code" class="form-label">{{ trans('clients.client_code') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="client_code" id="client_code"
                                    value="{{ $client_code }}" readonly>
                            </div>
                            @error('client_code')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" form-field>
                            <label for="first_name" class="form-label">{{ trans('clients.name') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name') }}">
                            </div>
                            @error('name')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- <div class="col-md-3" form-field>
                            <label for="last_name" class="form-label">{{ trans('clients.phone') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('phone') !!}</span>
                                <input type="number" class="form-control" name="phone" id="phone"
                                    value="{{ old('phone') }}">
                            </div>
                            @error('phone')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div> --}}
                        <div class="col-md-3" form-field>
                            <label for="phone" class="form-label">{{ trans('clients.phone') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">{!! form_icon('phone') !!}</span>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                    name="phone" id="phone" value="{{ old('phone') }}" placeholder="123456789" maxlength="13">
                                <select class="form-select" id="country_code" style="max-width: 130px;">
                                    <option value="+961">+961 (لبنان)</option>
                                    <option value="+20">+20 (مصر)</option>
                                    <option value="+966">+966 (السعودية)</option>
                                    <option value="+971">+971 (الإمارات)</option>
                                    <option value="+213">+213 (الجزائر)</option>
                                    <option value="+973">+973 (البحرين)</option>
                                    <option value="+974">+974 (قطر)</option>
                                    <option value="+965">+965 (الكويت)</option>
                                    <option value="+968">+968 (عُمان)</option>
                                    <option value="+962">+962 (الأردن)</option>
                                    <option value="+963">+963 (سوريا)</option>
                                    <option value="+964">+964 (العراق)</option>
                                    <option value="+967">+967 (اليمن)</option>
                                    <option value="+212">+212 (المغرب)</option>
                                    <option value="+216">+216 (تونس)</option>
                                    <option value="+218">+218 (ليبيا)</option>
                                    <option value="+249">+249 (السودان)</option>
                                    <option value="+252">+252 (الصومال)</option>
                                    <option value="+253">+253 (جيبوتي)</option>
                                    <option value="+222">+222 (موريتانيا)</option>
                                    <option value="+970">+970 (فلسطين)</option>
                                    <option value="+1268">+1268 (جزر القمر)</option>
                                </select>
                            </div>
                            @error('phone')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- <div class="col-md-3" form-field>
                            <label for="email" class="form-label">{{ trans('clients.email') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('email') !!}</span>
                                <input type="text" class="form-control" name="email" id="email" value="{{ old('email') }}">
                            </div>
                            @error('email')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div> --}}
                        <div class="col-md-3" form-field>
                            <label for="user" class="form-label">{{ trans('clients.user') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="user" id="user"
                                    value="{{ old('user') }}">
                            </div>
                            @error('user')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- </div>

                    <div class="col-md-12 row" form-field> --}}
                        <div class="col-md-3" form-field>
                            <label for="national_id" class="form-label">{{ trans('clients.address1') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('address') !!}</span>
                                <input type="text" class="form-control" name="address1" id="address1"
                                    value="{{ old('address1') }}">
                            </div>
                            @error('address1')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- <div class="col-md-3" form-field>
                            <label for="national_id" class="form-label">{{ trans('clients.address2') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('address') !!}</span>
                                <input type="text" class="form-control" name="address2" id="address2" value="{{old('address2')}}">
                            </div>
                            @error('address2')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div> --}}
                        <div class="col-md-3" form-field>
                            <label for="national_id" class="form-label">{{ trans('clients.box_switch') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="box_switch" id="box_switch"
                                    value="{{ old('box_switch') }}">
                            </div>
                            @error('box_switch')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" form-field>
                            <label for="client_type" class="form-label">{{ trans('clients.client_type') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon4">{!! form_icon('select2') !!}</span>
                                <select class="form-select" name="client_type" id="client_type">
                                    <option value="internet" {{ old('client_type') == 'internet' ? 'selected' : '' }}>
                                        {{ trans('clients.internet') }}
                                    </option>
                                    <option value="satellite" {{ old('client_type') == 'satellite' ? 'selected' : '' }}>
                                        {{ trans('clients.satellite') }}
                                    </option>
                                </select>
                            </div>
                            @error('client_type')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" form-field>
                            <label for="phone" class="form-label">{{ trans('clients.image') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('image') !!}</span>
                                <input type="file" class="form-control" name="image" id="image"
                                    value="{{ old('image') }}">
                            </div>
                            @error('image')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- <div class="col-md-3" form-field>
                            <label for="whatsapp_num" class="form-label">{{ trans('clients.commercial_register') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('number') !!}</span>
                                <input type="number" class="form-control" name="commercial_register" id="commercial_register" value="{{ old('commercial_register') }}">
                            </div>
                            @error('commercial_register')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div> --}}

                        <div class="col-md-3" form-field>
                            <label for="basic-url"class="form-label">{{ trans('clients.subscription') }}</label>
                            <div class="input-group ">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select1') !!}</i></span>
                                <div class="overflow-hidden flex-grow-1">
                                    <select class="form-select rounded-start-0" name="subscription_id"
                                        id="subscription_id" onchange="get_price(this.value)"
                                        data-placeholder="{{ trans('clients.select') }}">
                                        <option value="">{{ trans('clients.select') }}</option>
                                        @foreach ($subscriptions as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('subscription_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('subscription_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- </div>

                    <div class="col-md-12 row" form-field> --}}
                        <div class="col-md-3" form-field>
                            <label for="price" class="form-label">{{ trans('clients.price') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('price') !!}</span>
                                <input type="text" class="form-control" id="price" name="price" >
                            </div>
                        </div>
                        <div class="col-md-3" form-field>
                            <label for="subscription_date"
                                class="form-label">{{ trans('clients.subscription_date') }}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('date') !!}</i></span>
                                <input type="date" class="form-control" name="subscription_date"
                                    id="subscription_date" value="{{ old('subscription_date') }}">
                            </div>
                            @error('subscription_date')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" form-field>
                            <label for="start_date" class="form-label">{{ trans('clients.start_date') }}</label>
                            <div class="input-group ">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('date') !!}</span>
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                    value="{{ old('start_date') }}">
                            </div>
                            @error('start_date')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3" form-field>
                            <label for="is_active" class="form-label">{{ trans('clients.is_active') }}</label>
                            <div class="input-group ">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select1') !!}</span>
                                <select class="form-select" name="is_active" id="is_active">
                                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>
                                        {{ trans('clients.active') }}</option>
                                    <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>
                                        {{ trans('clients.inactive') }}</option>
                                </select>
                            </div>
                            @error('is_active')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    <div class="col-md-12">
                        <label for="area" class="form-label">{{ trans('clients.notes') }}</label>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon3">
                                {!! form_icon('text') !!}
                            </span>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                                aria-describedby="basic-addon3" rows="3"></textarea>
                        </div>
                        @error('notes')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- SAS 4 Account Section -->
                    <div class="col-md-12 mt-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bi bi-wifi"></i> {{ trans('clients.sas4_account') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sas4_mode" id="sas4_mode_link" value="link" checked>
                                        <label class="form-check-label" for="sas4_mode_link">{{ trans('clients.sas4_link_existing') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sas4_mode" id="sas4_mode_create" value="create">
                                        <label class="form-check-label" for="sas4_mode_create">{{ trans('clients.sas4_create_new') }}</label>
                                    </div>
                                </div>

                                <!-- Link Existing -->
                                <div id="sas4_link_section">
                                    <label for="sas4_search" class="form-label">{{ trans('clients.sas4_search_user') }}</label>
                                    <input type="text" class="form-control" id="sas4_search" placeholder="{{ trans('clients.sas4_search_placeholder') }}" autocomplete="off">
                                    <input type="hidden" name="sas_username" id="sas_username">
                                    <div id="sas4_search_results" class="list-group mt-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                                    <div id="sas4_selected_user" class="mt-2" style="display: none;">
                                        <span class="badge bg-success fs-6" id="sas4_selected_badge"></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearSas4Selection()">{{ trans('clients.sas4_remove') }}</button>
                                    </div>
                                </div>

                                <!-- Create New -->
                                <div id="sas4_create_section" style="display: none;">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="sas4_new_username" class="form-label">{{ trans('clients.sas4_username') }}</label>
                                            <input type="text" class="form-control" name="sas4_new_username" id="sas4_new_username">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="sas4_new_password" class="form-label">{{ trans('clients.sas4_password') }}</label>
                                            <input type="text" class="form-control" name="sas4_new_password" id="sas4_new_password">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="sas4_new_profile" class="form-label">{{ trans('clients.sas4_profile') }}</label>
                                            <select class="form-select" name="sas4_new_profile" id="sas4_new_profile">
                                                <option value="">{{ trans('clients.select') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="sas4_new_expiration" class="form-label">{{ trans('clients.sas4_expiration_date') }}</label>
                                            <input type="date" class="form-control" name="sas4_new_expiration" id="sas4_new_expiration">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-check form-switch form-check-success">
                                                <input class="form-check-input" type="checkbox" role="switch" name="sas4_new_enabled" id="sas4_new_enabled" value="1">
                                                <label class="form-check-label fw-bold" for="sas4_new_enabled">
                                                    <i class="bi bi-toggle-on text-success"></i> {{ trans('clients.sas4_enable_on_create') }}
                                                </label>
                                                <small class="text-muted d-block mt-1">{{ trans('clients.sas4_enable_on_create_hint') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

            if (!phoneInput.value.startsWith('+') && countryCodeSelect) {
                phoneInput.value = countryCodeSelect.value + phoneInput.value;
            }

            if (countryCodeSelect) {
                countryCodeSelect.addEventListener('change', function() {
                    const currentNumber = phoneInput.value.replace(/^\+\d{1,3}/, '');
                    phoneInput.value = this.value + currentNumber;
                });
            }

            phoneInput.addEventListener('blur', function() {
                if (!/^\+\d{1,3}/.test(this.value)) {
                    this.value = (countryCodeSelect ? countryCodeSelect.value : '+961') + this.value;
                }
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Admin\clients\SaveRequests', '#store_form1') !!}

    <script>
        // SAS 4 mode toggle
        document.querySelectorAll('input[name="sas4_mode"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('sas4_link_section').style.display = this.value === 'link' ? 'block' : 'none';
                document.getElementById('sas4_create_section').style.display = this.value === 'create' ? 'block' : 'none';
            });
        });

        // SAS 4 search with debounce
        var searchTimeout;
        document.getElementById('sas4_search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var query = this.value.trim();
            if (query.length < 2) {
                document.getElementById('sas4_search_results').style.display = 'none';
                return;
            }
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: '{{ route('admin.sas4.search_users') }}',
                    type: 'GET',
                    data: { q: query },
                    dataType: 'json',
                    success: function(res) {
                        var $results = $('#sas4_search_results');
                        $results.empty();
                        if (res.data && res.data.length > 0) {
                            res.data.forEach(function(user) {
                                var profileName = (user.profile_details && user.profile_details.name) || '';
                                var profileBadge = profileName ? ' <span class="badge bg-secondary">' + profileName + '</span>' : '';
                                $results.append(
                                    '<a href="#" class="list-group-item list-group-item-action sas4-user-item" ' +
                                    'data-username="' + user.username + '" ' +
                                    'data-name="' + (user.firstname || '') + '">' +
                                    '<strong>' + user.username + '</strong> — ' + (user.firstname || '') + profileBadge +
                                    '</a>'
                                );
                            });
                            $results.show();
                        } else {
                            $results.html('<div class="list-group-item text-muted">{{ trans('clients.sas4_no_results') }}</div>').show();
                        }
                    }
                });
            }, 300);
        });

        $(document).on('click', '.sas4-user-item', function(e) {
            e.preventDefault();
            var username = $(this).data('username');
            var name = $(this).data('name');
            $('#sas_username').val(username);
            $('#sas4_selected_badge').text(username + ' — ' + name);
            $('#sas4_selected_user').show();
            $('#sas4_search_results').hide();
            $('#sas4_search').val('').prop('disabled', true);
        });

        function clearSas4Selection() {
            $('#sas_username').val('');
            $('#sas4_selected_user').hide();
            $('#sas4_search').val('').prop('disabled', false);
        }

        // Load SAS 4 profiles
        $.ajax({
            url: '{{ route('admin.sas4.profiles') }}',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                var $select = $('#sas4_new_profile');
                if (res.data) {
                    res.data.forEach(function(profile) {
                        $select.append('<option value="' + profile.id + '">' + profile.name + '</option>');
                    });
                }
            }
        });
    </script>

@endsection
