@extends('dashbord.layouts.master')
@section('css')

    @notifyCss
@endsection
@section('content')

    @include('dashbord.company.company_nav')

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="t_container">


            <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
                <div class="card-header" style="background-color: #f8f9fa;">
                    <h3 class="card-title"></i> {{trans('company.company_projects')}}</h3>
                    <div class="card-toolbar">
                        <div class="text-center">
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding-left: 0px !important;">
                    <div class="col-md-12 row">
                        <div class="col-md-8">
                            @include('dashbord.company.projects.company_project_form')
                            @include('dashbord.company.projects.company_project_data')
                        </div>
                        <div class="col-md-4">
                            @include('dashbord.company.company_details')

                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

    </div>

@endsection

@section('js')


    @notifyJs

@endsection



