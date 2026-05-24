@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('accounts.accounts');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.account_settings'), 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp

    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="t_container">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">

            <form method="post" action="{{ route('admin.save_account_setting') }}" enctype="multipart/form-data"
                id="form">
                @csrf

                <div class="row col-md-12" style="margin: 10px">
                    <div class="col-md-6">
                        <label for="general_account_id" class="form-label">{{ trans('accounts.general_account') }}</label>
                        <select class="form-control" name="general_account_id" id="general_account_id"
                        @cannot('save_account_settings') disabled @endcannot>
                            <option value="">{{ trans('accounts.select_account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('general_account_id', $accountSetting->general_account_id ?? '') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="masrofat_account_id" class="form-label">{{ trans('accounts.masrofat_account') }}</label>
                        <select class="form-control" name="masrofat_account_id" id="masrofat_account_id"
                        @cannot('save_account_settings') disabled @endcannot>
                            <option value="">{{ trans('accounts.select_account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('masrofat_account_id', $accountSetting->masrofat_account_id ?? '') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="employee_account_id"
                            class="form-label">{{ trans('accounts.employee_account') }}</label>
                        <select class="form-control" name="employee_account_id" id="employee_account_id"
                        @cannot('save_account_settings') disabled @endcannot>
                            <option value="">{{ trans('accounts.select_account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('employee_account_id', $accountSetting->employee_account_id ?? '') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="accountant_account_id"
                            class="form-label">{{ trans('accounts.accountant_account') }}</label>
                        <select class="form-control" name="accountant_account_id" id="accountant_account_id"
                        @cannot('save_account_settings') disabled @endcannot>
                            <option value="">{{ trans('accounts.select_account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('accountant_account_id', $accountSetting->accountant_account_id ?? 10) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer" style="margin-top: 10px">
                    @can('save_account_settings')
                        <button type="submit" class="btn btn-primary">{{ trans('accounts.save') }}</button>
                    @endcan
                    <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">{{ trans('accounts.cancel') }}</button>
                </div>
            </form>
        </div>

    </div>
@stop
@section('js')
    <script>
        function showSuccessMessage(message) {
            $('#success_message').text(message).removeClass('d-none').show();
            setTimeout(function() {
                $('#success_message').fadeOut().addClass('d-none');
            }, 8000);
        }
    </script>

@endsection
