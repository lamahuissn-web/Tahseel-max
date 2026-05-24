@extends('dashbord.layouts.master')
@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .permission-section {
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 18px;
            color: blue;
            margin: 0;
        }

        .select-all-section {
            font-size: 14px;
        }

        .larger-checkbox {
            width: 1.5em;
            height: 1.5em;
            margin-top: 0;
            margin-right: 0.5em;
            border: 2px solid #007bff;
        }

        .form-check-label {
            margin-left: 0.5em;
            vertical-align: middle;
        }

        .larger-checkbox {
            width: 1.5em;
            height: 1.5em;
        }

        .form-check-label {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
            transition: color 0.3s ease-in-out;
        }
    </style>

@endsection
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('roles.permissions');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.roles'), 'link' => route('admin.roles.index')],
                ['label' => trans('roles.permissions'), 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{ BackButton(route('admin.roles.index')) }}

        </div>
    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="t_container">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                generateCardHeader('roles.edit_role', 'admin.roles.index', ' ');
            @endphp

            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="col-md-12 row" style="margin-top: 10px">
                        <div class="col-md-3">
                            <label for="title_en" class="form-label">{{ trans('roles.role_title_en') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="title[en]" id="title_en"
                                    value="{{ old('title[en]', $role->getTranslation('title', 'en')) }}" required>
                            </div>
                            @error('title[en]')
                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="title_ar" class="form-label">{{ trans('roles.role_title_ar') }}</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                                <input type="text" class="form-control" name="title[ar]" id="title_ar"
                                    value="{{ old('title[ar]', $role->getTranslation('title', 'ar')) }}" required>
                            </div>
                            @error('title[ar]')
                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="m-3 d-flex align-items-center">
                        <label class="form-check-label fs-5 fw-semibold" for="checkAllPermissions">
                            {{ trans('roles.check_all_permissions') }}
                        </label>
                        <input class="form-check-input larger-checkbox" type="checkbox" id="checkAllPermissions">
                    </div>

                    <div class="row">
                        @foreach ($sections as $sectionName => $permissions)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <span class="section-title fw-bold">{{ trans('roles.' . $sectionName) }}</span>
                                        <div class="form-check">
                                            <input class="form-check-input larger-checkbox section-checkbox" type="checkbox"
                                                data-section="{{ Str::slug($sectionName) }}"
                                                id="check{{ Str::studly($sectionName) }}"
                                                {{ $role->permissions->pluck('name')->intersect($permissions->pluck('name'))->count() === count($permissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="check{{ Str::studly($sectionName) }}">
                                                {{ trans('roles.select_all') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body" data-section-group="{{ Str::slug($sectionName) }}">
                                        @foreach ($permissions as $permission)
                                            <div class="form-check">
                                                <input class="form-check-input larger-checkbox permission-checkbox"
                                                    type="checkbox" name="permissions[]"
                                                    value="{{ $permission->name }}"
                                                    id="{{ $permission->name }}"
                                                    {{ in_array($permission->name, $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $permission->name }}">
                                                    {{ $permission->title }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">{{ trans('roles.save') }}</button>
                    </div>
                </div>
            </form>

        </div>

    </div>

@stop

@section('js')
    <script>
        document.getElementById("checkAllPermissions").addEventListener("change", function() {
            const checkboxes = document.getElementsByClassName("permission-checkbox");
            const sectionCheckboxes = document.getElementsByClassName("section-checkbox");

            for (let checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }

            for (let sectionBox of sectionCheckboxes) {
                sectionBox.checked = this.checked;
            }
        });

        document.querySelectorAll('.section-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const section = this.dataset.section;
                const sectionCheckboxes = document.querySelector(`[data-section-group="${section}"]`)
                    .querySelectorAll('.permission-checkbox');

                sectionCheckboxes.forEach(box => {
                    box.checked = this.checked;
                });

                updateMainCheckbox();
            });
        });

        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const section = this.closest('[data-section-group]').dataset.sectionGroup;
                updateSectionCheckbox(section);
                updateMainCheckbox();
            });
        });

        function updateSectionCheckbox(section) {
            const sectionCheckboxes = document.querySelector(`[data-section-group="${section}"]`)
                .querySelectorAll('.permission-checkbox');
            const sectionCheckbox = document.querySelector(`[data-section="${section}"]`);

            const allChecked = Array.from(sectionCheckboxes).every(box => box.checked);
            sectionCheckbox.checked = allChecked;
        }

        function updateMainCheckbox() {
            const allCheckboxes = document.getElementsByClassName("permission-checkbox");
            const mainCheckbox = document.getElementById("checkAllPermissions");

            mainCheckbox.checked = Array.from(allCheckboxes).every(box => box.checked);
        }

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
