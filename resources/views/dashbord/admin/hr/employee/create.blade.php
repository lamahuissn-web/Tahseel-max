@extends('layouts.admin')

@section('page-title')
    {{ __('Create Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Employee') }}</li>
@endsection


@section('content')
<div class="row">
    {{ Form::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data', 'class'=>'needs-validation', 'novalidate']) }}
    <div class="">
        <div class="">
            <div class="row">
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Personal Detail') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::text('name', old('name'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>__('Enter employee name')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {{-- {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}<x-required></x-required> --}}
                                    {{-- {!! Form::text('phone', old('phone'), ['class' => 'form-control' , 'required' => 'required' ,'placeholder'=>'Enter employee phone'  ]) !!} --}}
                                    <x-mobile label="{{__('Phone')}}" name="phone" value="{{old('phone')}}" required placeholder="Enter employee phone"></x-mobile>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<x-required></x-required>
                                        {{ Form::date('dob', null, ['class' => 'form-control ', 'required' => 'required', 'autocomplete' => 'off','placeholder'=>'Select Date of Birth']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label' , 'required' => 'required' ]) !!}<x-required></x-required>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input" checked>
                                                <label class="form-check-label " for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input">
                                                <label class="form-check-label "
                                                    for="g_female">{{ __('Female') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::email('email', old('email'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter employee email']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::password('password',['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter employee new password']) !!}
                                </div>
                            </div>
                            <div class="form-group">
                                {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<x-required></x-required>
                                {!! Form::textarea('address', old('address'), ['class' => 'form-control', 'rows' => 2 ,'placeholder'=>__('Enter employee address') , 'required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Company Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                @csrf
                                <div class="form-group ">
                                    {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                    {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('branch_id', __('Select Branch'), ['class' => 'form-label']) }}<x-required></x-required>
                                    <div class="form-icon-user">
                                        {{ Form::select('branch_id', $branches, null, ['class' => 'form-control ', 'placeholder' => 'Select Branch','required' => 'required']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('department_id', __('Select Department'), ['class' => 'form-label']) }}<x-required></x-required>
                                    <div class="form-icon-user">
                                        {{ Form::select('department_id', $departments, null, ['class' => 'form-control ', 'id' => 'department_id' , 'placeholder' => 'Select Department','required' => 'required']) }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}<x-required></x-required>

                                    <div class="form-icon-user">
                                        {{ Form::select('designation_id', $designations, null, ['class' => 'form-control ', 'id' => 'designation_id' , 'placeholder' => 'Select Designation','required' => 'required']) }}
                                    </div>
                                </div>
                                {{-- <div class="form-group col-md-6">
                                    {!! Form::label('biometric_emp_id', __('Employee Code'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::text('biometric_emp_id', old('biometric_emp_id'), [
                                        'class' => 'form-control',
                                        'placeholder' => 'Enter Employee Code',
                                        'required' => 'required'
                                    ]) !!}
                                </div> --}}
                                <div class="form-group col-md-6">
                                    {!! Form::label('company_doj', __('Company Date Of Joining'), ['class' => '  form-label']) !!}<x-required></x-required>
                                    {{ Form::date('company_doj', null, ['class' => 'form-control ', 'required' => 'required', 'autocomplete' => 'off' ,'placeholder'=>'Select company date of joining']) }}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Document') }}</h6>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            @foreach ($documents as $key => $document)
                                <div class="row">
                                    <div class="form-group col-12 d-flex">
                                        <div class="float-left col-4">
                                            <label for="document"
                                                class="float-left pt-1 form-label">{{ $document->name }}
                                                @if ($document->is_required == 1)
                                                    <x-required></x-required>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="float-right col-8">
                                            <input type="hidden" name="emp_doc_id[{{ $document->id }}]" id=""
                                                value="{{ $document->id }}">
                                            <div class="choose-files">
                                                <label for="document[{{ $document->id }}]">
                                                    <div class=" bg-primary document "> <i
                                                            class="ti ti-upload "></i>{{ __('Choose file here') }}
                                                    </div>
                                                    <input type="file"
                                                        class="form-control file file-validate d-none @error('document') is-invalid @enderror"
                                                        @if ($document->is_required == 1) required @endif
                                                        name="document[{{ $document->id }}]" id="document[{{ $document->id }}]"
                                                        data-filename="{{ $document->id . '_filename' }}" onchange="document.getElementById('{{'blah'.$key}}').src = window.URL.createObjectURL(this.files[0])">
                                                    <p id="" class="file-error text-danger"></p>
                                                </label>
                                                <img id="{{'blah'.$key}}" src=""  width="50%" />

                                            </div>

                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Bank Account Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                    {!! Form::text('account_holder_name', old('account_holder_name'), ['class' => 'form-control' ,'placeholder'=>__('Enter account holder name')]) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                    {!! Form::number('account_number', old('account_number'), ['class' => 'form-control' ,'placeholder'=>__('Enter account number')]) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                                    {!! Form::text('bank_name', old('bank_name'), ['class' => 'form-control'  ,'placeholder'=>__('Enter bank name')]) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => 'form-label']) !!}
                                    {!! Form::text('bank_identifier_code', old('bank_identifier_code'), ['class' => 'form-control' ,'placeholder'=>__('Enter bank identifier code')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                    {!! Form::text('branch_location', old('branch_location'), ['class' => 'form-control' ,'placeholder'=>__('Enter branch location')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                                    {!! Form::text('tax_payer_id', old('tax_payer_id'), ['class' => 'form-control' ,'placeholder'=>__('Enter tax payer id')]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="float-end">
            <button type="submit" class="btn  btn-primary">{{ 'Create' }}</button>
        </div>
    </div>
    {!! Form::close() !!}
</div>
@endsection

@push('script-page')
    <script>
        $('input[type="file"]').change(function(e) {
            var file = e.target.files[0].name;
            var file_name = $(this).attr('data-filename');
            $('.' + file_name).append(file);
        });
    </script>
    <script>
        $(document).ready(function() {
            var d_id = $('.department_id').val();
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {

            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#designation_id').empty();
                    $('#designation_id').append('<option value="">Select any Designation</option>');
                    $.each(data, function (key, value) {
                        $('#designation_id').append('<option value="' + key + '"  >' + value + '</option>');
                    });
                }


            });
        }
    </script>
@endpush
