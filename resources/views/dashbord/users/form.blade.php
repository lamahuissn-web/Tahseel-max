@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('users.users');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.users.index')],
                ['label' => trans('Toolbar.users'), 'link' => ''],
                ['label' => trans('users.users_table'), 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ BackButton(route('admin.users.index')) }}

        </div>
    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="t_container">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                generateCardHeader('users.add_user', 'admin.users.index', ' ');
            @endphp

            <form action="{{ route('admin.users.store') }}" method="post" enctype="multipart/form-data" id="store_form">
                @csrf
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="is_employee" class="form-label">{{ trans('users.is_employee') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('select') !!}</span>
                                <select class="form-select" name="is_employee" id="is_employee"
                                    onchange="toggleEmployeeFields()">
                                    {{-- <option value="0" selected>{{ trans('users.no') }}</option>
                                    <option value="1">{{ trans('users.yes') }}</option> --}}
                                    <option value="0" {{ old('is_employee') == '0' ? 'selected' : '' }}>
                                        {{ trans('users.no') }}</option>
                                    <option value="1" {{ old('is_employee') == '1' ? 'selected' : '' }}>
                                        {{ trans('users.yes') }}</option>
                                </select>
                            </div>
                            @error('is_employee')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" id="select_emp_div" style="display: none; margin-top: 10px">
                            <label for="emp_id" class="form-label">{{ trans('users.emp_id') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('id-card') !!}</span>
                                <select class="form-select" name="emp_id" id="emp_id"
                                    onchange="updateEmployeePosition()">
                                    <option value="">{{ trans('users.select_employee') }}</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}"
                                            {{ old('emp_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name . ' ' . $employee->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('emp_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="name" class="form-label">{{ trans('users.name') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name') }}">
                            </div>
                            @error('name')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="position" class="form-label">{{ trans('users.position') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="position" id="position"
                                    value="{{ old('position') }}">
                            </div>
                            @error('position')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="email" class="form-label">{{ trans('users.email') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('email') !!}</span>
                                <input type="email" class="form-control" name="email" id="email"
                                    value="{{ old('email') }}">
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="password" class="form-label">{{ trans('users.password') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('password') !!}</span>
                                <input type="password" class="form-control" name="password" id="password">
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="phone" class="form-label">{{ trans('users.phone') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('phone') !!}</span>
                                <input type="text" class="form-control" name="phone" id="phone"
                                    value="{{ old('phone') }}">
                            </div>
                            @error('phone')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="role" class="form-label">{{ trans('users.role') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('role') !!}</span>
                                <select class="form-select" name="role" id="role">
                                    <option value="">{{ trans('users.select_role') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ old('role') == $role->id ? 'selected' : '' }}>
                                            {{ $role->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('role')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="address" class="form-label">{{ trans('users.address') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('address') !!}</span>
                                <input type="text" class="form-control" name="address" id="address"
                                    value="{{ old('address') }}">
                            </div>
                            @error('address')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="image" class="form-label">{{ trans('users.image') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('image') !!}</span>
                                <input type="file" class="form-control" name="image" id="image">
                            </div>
                            @error('image')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="status" class="form-label">{{ trans('users.status') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('status') !!}</span>
                                <select class="form-select" name="status" id="status">
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>
                                        {{ trans('users.active') }}</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>
                                        {{ trans('users.inactive') }}</option>
                                </select>
                            </div>
                            @error('status')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>


                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">{{ trans('users.save') }}</button>
                </div>
            </form>
        </div>

    </div>


@stop
@section('js')
    <script>
        function toggleEmployeeFields() {
            var isEmployee = document.getElementById('is_employee').value;
            var empId = document.getElementById('emp_id');
            var nameField = document.getElementById('name');
            var positionField = document.getElementById('position');
            // var emailField = document.getElementById('email');
            var phoneField = document.getElementById('phone');
            var addressField = document.getElementById('address');
            var empSelectDiv = document.getElementById('select_emp_div');

            if (isEmployee == '1') {
                nameField.readOnly = true;
                positionField.readOnly = true;
                // emailField.readOnly = true;
                phoneField.readOnly = true;
                addressField.readOnly = true;
                empSelectDiv.style.display = 'block';
            } else {
                nameField.readOnly = false;
                positionField.readOnly = false;
                // emailField.readOnly = false;
                phoneField.readOnly = false;
                addressField.readOnly = false;
                empSelectDiv.style.display = 'none';
                nameField.value = '';
                positionField.value = '';
                // emailField.value = '';
                phoneField.value = '';
                addressField.value = '';
                empId.value = '';
            }
        }

        function updateEmployeePosition() {
            var empId = document.getElementById('emp_id').value;
            var nameField = document.getElementById('name');
            var positionField = document.getElementById('position');
            // var emailField = document.getElementById('email');
            var phoneField = document.getElementById('phone');
            var addressField = document.getElementById('address');

            if (empId) {
                var selectedEmployees = @json($employees);
                var employee = selectedEmployees.find(emp => emp.id == empId);

                if (employee) {
                    nameField.value = employee.first_name + ' ' + employee.last_name;
                    positionField.value = employee.position;
                    // emailField.value = employee.email;
                    phoneField.value = employee.phone;
                    addressField.value = employee.address;
                }
            } else {
                nameField.value = '';
                positionField.value = '';
                // emailField.value = '';
                phoneField.value = '';
                addressField.value = '';
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            toggleEmployeeFields();

            var oldEmpId = "{{ old('emp_id') }}";
            if (oldEmpId) {
                updateEmployeePosition();
            }
        });

        function showSuccessMessage(message) {
            $('#success_message').text(message).removeClass('d-none').show();
            setTimeout(function() {
                $('#success_message').fadeOut().addClass('d-none');
            }, 8000);
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>


@endsection
