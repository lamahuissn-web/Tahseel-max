@extends('dashbord.layouts.master')
{{-- @section('css')

    @notifyCss
@endsection --}}
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('employees.employees');
         $breadcrumbs = [
                  ['label' => trans('Toolbar.home'), 'link' => ''],
                  ['label' => trans('Toolbar.employees'), 'link' => route('admin.employee_data')],
                  ['label' => trans('employees.add_employee'), 'link' => '']
                  ];

          PageTitle($title, $breadcrumbs);
        @endphp


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ BackButton(route('admin.employee_data'))}}

        </div>
    </div>

@endsection
@section('content')


    <div id="kt_app_content" class="app-content flex-column-fluid" >
        <div id="kt_app_content_container" class="t_container" >
            <div class="card shadow-sm " style="border-top: 3px solid #007bff;">
                {{-- <div class="card-header">
                    <h3 class="card-title"></i> {{trans('employees.add_employee')}}</h3>
                    <div class="card-toolbar">
                        <div class="text-center">
                            <a class="btn btn-primary" href="{{ route('admin.employee_data') }}">
                                <i class="bi bi-arrow-clockwise fs-3"></i>{{trans('employees.back')}}
                            </a>
                        </div>
                    </div>
                </div> --}}
                @php
                    generateCardHeader('employees.add_employee','admin.employee_data',' ')
                @endphp
                <form action="{{ route('admin.save_employee') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-12 row" style="margin-top: 10px">
                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="emp_code" class="form-label">{{ trans('employees.emp_code') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-caret-down fs-2"></i></span>
                                    <input type="text" class="form-control" name="emp_code" id="emp_code" value="{{ $emp_code }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="first_name" class="form-label">{{ trans('employees.first_name') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-person fs-2"></i></span>
                                    <input type="text" class="form-control" name="first_name" id="first_name" value="{{ old('first_name') }}">
                                </div>
                                @error('first_name')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="last_name" class="form-label">{{ trans('employees.last_name') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-person fs-2"></i></span>
                                    <input type="text" class="form-control" name="last_name" id="last_name" value="{{ old('last_name') }}">
                                </div>
                                @error('last_name')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- <div class="col-md-3" style="margin-top: 10px">
                                <label for="email" class="form-label">{{ trans('employees.email') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-envelope fs-2"></i></span>
                                    <input type="text" class="form-control" name="email" id="email" value="{{ old('email') }}">
                                </div>
                                @error('email')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 row" style="margin-top: 10px">
                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="national_id" class="form-label">{{ trans('employees.national_id') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-caret-down fs-2"></i></span>
                                    <input type="number" class="form-control" name="national_id" id="national_id" value="{{ old('national_id') }}">
                                </div>
                                @error('national_id')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="religion" class="form-label">{{ trans('employees.religion') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-caret-down fs-2"></i></span>
                                    <div class="overflow-hidden flex-grow-1">
                                        <select class="form-select rounded-start-0" name="religion" id="religion" data-placeholder="{{ trans('employees.select') }}">
                                            <option value="">{{ trans('employees.select') }}</option>
                                            <option value="muslim" {{ old('religion') == 'muslim' ? 'selected' : '' }}>{{ trans('employees.muslim') }}</option>
                                            <option value="mese7y" {{ old('religion') == 'mese7y' ? 'selected' : '' }}>{{ trans('employees.mese7y') }}</option>
                                        </select>
                                    </div>
                                </div>
                                @error('religion')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div> --}}

                            {{-- <div class="col-md-3" style="margin-top: 10px">
                                <label for="phone" class="form-label">{{ trans('employees.phone') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-phone fs-2"></i></span>
                                    <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone') }}">
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

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="whatsapp_num" class="form-label">{{ trans('clients.phone') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text">{!! form_icon('phone') !!}</span>
                                    <input type="tel" class="form-control @error('whatsapp_num') is-invalid @enderror"
                                        name="whatsapp_num" id="whatsapp_num" value="{{ old('whatsapp_num') }}" placeholder="123456789" maxlength="13">
                                    <select class="form-select" id="country_codee" style="max-width: 130px;">
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
                                @error('whatsapp_num')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                        {{-- </div>

                        <div class="col-md-12 row" style="margin-top: 10px"> --}}
                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="address" class="form-label">{{ trans('employees.address') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-house-door fs-2"></i></span>
                                    <input type="text" class="form-control" name="address" id="address" value="{{ old('address') }}">
                                </div>
                                @error('address')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- <div class="col-md-3" style="margin-top: 10px">
                                <label for="date_of_birth" class="form-label">{{ trans('employees.date_of_birth') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-calendar fs-2"></i></span>
                                    <input type="date" class="form-control" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}">
                                </div>
                                @error('date_of_birth')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="gender" class="form-label">{{ trans('employees.gender') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-caret-down fs-2"></i></span>
                                    <div class="overflow-hidden flex-grow-1">
                                        <select class="form-select rounded-start-0" name="gender" id="gender" data-placeholder="{{ trans('employees.select') }}">
                                            <option value="">{{ trans('employees.select') }}</option>
                                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>{{ trans('employees.male') }}</option>
                                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>{{ trans('employees.female') }}</option>
                                        </select>
                                    </div>
                                </div>
                                @error('gender')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="material_status" class="form-label">{{ trans('employees.material_status') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-caret-down fs-2"></i></span>
                                    <div class="overflow-hidden flex-grow-1">
                                        <select class="form-select rounded-start-0" name="material_status" id="material_status" data-placeholder="{{ trans('employees.select') }}">
                                            <option value="">{{ trans('employees.select') }}</option>
                                            <option value="single" {{ old('material_status') == 'single' ? 'selected' : '' }}>{{ trans('employees.single') }}</option>
                                            <option value="married" {{ old('material_status') == 'married' ? 'selected' : '' }}>{{ trans('employees.married') }}</option>
                                            <option value="divorced" {{ old('material_status') == 'divorced' ? 'selected' : '' }}>{{ trans('employees.divorced') }}</option>
                                        </select>
                                    </div>
                                </div>
                                @error('material_status')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 row" style="margin-top: 10px"> --}}

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="position" class="form-label">{{ trans('employees.position') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-briefcase fs-2"></i></span>
                                    <input type="text" class="form-control" name="position" id="position" value="{{ old('position') }}">
                                </div>
                                @error('position')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="salary" class="form-label">{{ trans('employees.salary') }}</label>
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text" id="basic-addon3"><i class="bi bi-cash fs-2"></i></span>
                                    <input type="text" class="form-control" name="salary" id="salary" value="{{ old('salary') }}">
                                </div>
                                @error('salary')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="personal_photo" class="form-label">{{ trans('employees.personal_photo') }}</label>
                                <div class="input-group flex-nowrap">
                                    <input type="file" class="form-control" name="personal_photo" id="personal_photo">
                                </div>
                                @error('personal_photo')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3" style="margin-top: 10px">
                                <label for="status" class="form-label">{{ trans('employees.status') }}</label>
                                <div class="input-group flex-nowrap ">
                                    <span class="input-group-text" id="basic-addon3">{!! form_icon('select1') !!}</span>
                                    <select class="form-select" name="status" id="status">
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>{{ trans('employees.active') }}</option>
                                        <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>{{ trans('employees.inactive') }}</option>
                                    </select>
                                </div>
                                @error('status')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save fs-2"></i> {{ trans('employees.save') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection

@section('js')


    @notifyJs
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countryCodeSelect = document.getElementById('country_codee');
            const phoneInput = document.getElementById('whatsapp_num');

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

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Admin\Employees\AddEmployeeRequest', '#store_form') !!}
@endsection



