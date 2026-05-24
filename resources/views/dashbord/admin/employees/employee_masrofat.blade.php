@extends('dashbord.layouts.master')
@section('css')

@notifyCss
@endsection
@section('content')


<div id="kt_app_content" class="app-content flex-column-fluid">

    <div id="kt_app_content_container" class="app-container container-xxl">

        <div class="card mb-6 mb-xl-9">
            <div class="card-body pt-9 pb-0">

                @include('dashbord.admin.employees.load_employee_data')
                @include('dashbord.admin.employees.employee_nav')

            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title fs-3 fw-bold">{{trans('employees.employee_revenues')}}</div>
            </div>
            <div class="card" style="margin-top:10px">
                @can('add_employee_masrofat')
                @include('dashbord.admin.employees.employee_masrofat_form')
                @endcan
                @can('view_employee_masrofat')
                @include('dashbord.admin.employees.employee_masrofat_data')
                @endcan
            </div>

        </div>


    </div>

</div>













@endsection

@section('js')


@notifyJs

@endsection