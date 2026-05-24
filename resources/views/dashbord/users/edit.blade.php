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
                generateCardHeader('masrofat.edit_masrofat', 'admin.masrofat.index', ' ');
            @endphp


            {{-- <form action="{{ route('admin.test.update', $all_data->id) }}" method="post" enctype="multipart/form-data" id="edit_form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4">
                            <label for="test_code" class="form-label">{{ trans('tests.test_code') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="test_code" id="test_code" value="{{ $all_data->test_code }}" readonly>
                            </div>
                            @error('test_code')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="client_id" class="form-label">{{ trans('tests.client') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="client_id" id="client_id">
                                    <option value="">{{ trans('tests.select') }}</option>
                                    @foreach ($clients as $item)
                                        <option value="{{ $item->id }}" {{ $all_data->client_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('client_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="company_id" class="form-label">{{ trans('tests.company') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="company_id" id="company_id">
                                    <option value="">{{ trans('tests.select') }}</option>
                                    @foreach ($companies as $item)
                                        <option value="{{ $item->id }}" {{ $all_data->company_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('company_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4">
                            <label for="project_id" class="form-label">{{ trans('tests.project') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                                <select class="form-select rounded-start-0" name="project_id" id="project_id">
                                    <option value="">{{ trans('tests.select') }}</option>
                                    @foreach ($projects as $item)
                                        <option value="{{ $item->id }}" {{ $all_data->project_id == $item->id ? 'selected' : '' }}>{{ $item->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('project_id')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="talab_number" class="form-label">{{ trans('tests.talab_number') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="number" class="form-control" name="talab_number" id="talab_number" value="{{ $all_data->talab_number }}">
                            </div>
                            @error('talab_number')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="talab_title" class="form-label">{{ trans('tests.talab_title') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="talab_title" id="talab_title" value="{{ $all_data->talab_title }}">
                            </div>
                            @error('talab_title')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4">
                            <label for="talab_image" class="form-label">{{ trans('tests.talab_image') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('image') !!}</span>
                                <input type="file" class="form-control" name="talab_image" id="talab_image">
                            </div>
                            @error('talab_image')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror

                            @if (isset($all_data) && $all_data->talab_image)
                                <div class="mt-2">
                                    <img src="{{ asset('images/' . $all_data->talab_image) }}" alt="Talab Image" class="img-thumbnail" width="150">
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label for="talab_date" class="form-label">{{ trans('tests.talab_date') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('date') !!}</span>
                                <input type="date" class="form-control" name="talab_date" id="talab_date" value="{{ $all_data->talab_date }}">
                            </div>
                            @error('talab_date')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="talab_end_date" class="form-label">{{ trans('tests.talab_end_date') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('date') !!}</span>
                                <input type="date" class="form-control" name="talab_end_date" id="talab_end_date" value="{{ $all_data->talab_end_date }}">
                            </div>
                            @error('talab_end_date')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        {{ trans('tests.update') }}
                    </button>
                </div>
            </form> --}}
            {{-- <form action="{{ route('admin.users.update', $admin->id) }}" method="post" enctype="multipart/form-data"
                id="edit_form">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="is_employee" class="form-label">{{ trans('users.is_employee') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('select') !!}</span>
                                <select class="form-select" name="is_employee" id="is_employee"
                                    onchange="toggleEmployeeFields()">
                                    <option value="0" {{ $admin->is_employee == 0 ? 'selected' : '' }}>
                                        {{ trans('users.no') }}</option>
                                    <option value="1" {{ $admin->is_employee == 1 ? 'selected' : '' }}>
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
                                            {{ $admin->emp_id == $employee->id ? 'selected' : '' }}>
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
                                    value="{{ old('name', $admin->name) }}">
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
                                    value="{{ old('position', $admin->position) }}">
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
                                    value="{{ old('email', $admin->email) }}">
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="phone" class="form-label">{{ trans('users.phone') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('phone') !!}</span>
                                <input type="text" class="form-control" name="phone" id="phone"
                                    value="{{ old('phone', $admin->phone) }}">
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
                                            {{ old('role', $admin->role) == $role->id ? 'selected' : '' }}>
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
                            <label for="status" class="form-label">{{ trans('users.status') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('status') !!}</span>
                                <select class="form-select" name="status" id="status">
                                    <option value="1" {{ old('status', $admin->status) == '1' ? 'selected' : '' }}>
                                        {{ trans('users.active') }}</option>
                                    <option value="0" {{ old('status', $admin->status) == '0' ? 'selected' : '' }}>
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
            </form> --}}

            <form action="{{ route('admin.users.update', $admin->id) }}" method="post" enctype="multipart/form-data" id="edit_form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="is_employee" class="form-label">{{ trans('users.is_employee') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('select') !!}</span>
                                <select class="form-select" name="is_employee" id="is_employee" onchange="toggleEmployeeFields()">
                                    <option value="0" {{ old('is_employee', $admin->is_employee) == 0 ? 'selected' : '' }}>{{ trans('users.no') }}</option>
                                    <option value="1" {{ old('is_employee', $admin->is_employee) == 1 ? 'selected' : '' }}>{{ trans('users.yes') }}</option>
                                </select>
                            </div>
                            @error('is_employee')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" id="select_emp_div" style="{{ $admin->is_employee ? 'display: block' : 'display: none' }}; margin-top: 10px">
                            <label for="emp_id" class="form-label">{{ trans('users.emp_id') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('id-card') !!}</span>
                                <select class="form-select" name="emp_id" id="emp_id" onchange="updateEmployeePosition()">
                                    <option value="">{{ trans('users.select_employee') }}</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('emp_id', $admin->emp_id) == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name .' '.$employee->last_name }}
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
                                <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $admin->name) }}">
                            </div>
                            @error('name')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="position" class="form-label">{{ trans('users.position') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="position" id="position" value="{{ old('position', $admin->position) }}">
                            </div>
                            @error('position')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="email" class="form-label">{{ trans('users.email') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('email') !!}</span>
                                <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $admin->email) }}">
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
                                <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone', $admin->phone) }}">
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
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role', $admin->roles->first()->id ?? '') == $role->id ? 'selected' : '' }}>
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
                                <input type="text" class="form-control" name="address" id="address" value="{{ old('address', $admin->address) }}">
                            </div>
                            @error('address')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4" style="margin-top: 10px">
                            <label for="status" class="form-label">{{ trans('users.status') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text">{!! form_icon('status') !!}</span>
                                <select class="form-select" name="status" id="status">
                                    <option value="1" {{ old('status', $admin->status) == '1' ? 'selected' : '' }}>{{ trans('users.active') }}</option>
                                    <option value="0" {{ old('status', $admin->status) == '0' ? 'selected' : '' }}>{{ trans('users.inactive') }}</option>
                                </select>
                            </div>
                            @error('status')
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
                            @if($admin->image)
                                <div class="mt-2">
                                    <img src="{{ asset('images/' . $admin->image) }}" alt="admin Image" class="img-thumbnail" width="150">
                                </div>
                            @endif
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
            var nameField = document.getElementById('name');
            var positionField = document.getElementById('position');
            var empSelectDiv = document.getElementById('select_emp_div');

            if (isEmployee == '1') {
                nameField.readOnly = true;
                positionField.readOnly = true;
                empSelectDiv.style.display = 'block';
            } else {
                nameField.readOnly = false;
                positionField.readOnly = false;
                empSelectDiv.style.display = 'none';
            }
        }

        function updateEmployeePosition() {
            var empId = document.getElementById('emp_id').value;
            var nameField = document.getElementById('name');
            var positionField = document.getElementById('position');
            var emailField = document.getElementById('email');

            if (empId) {
                var selectedEmployees = @json($employees);
                var employee = selectedEmployees.find(emp => emp.id == empId);

                if (employee) {
                    nameField.value = employee.first_name + ' ' + employee.last_name;
                    positionField.value = employee.position;
                    emailField.value = employee.email;
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            toggleEmployeeFields();
            updateEmployeePosition();
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
