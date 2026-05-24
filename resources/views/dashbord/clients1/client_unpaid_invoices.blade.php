@extends('dashbord.layouts.master')
@section('css')

    @notifyCss
@endsection
@section('content')

    @include('dashbord.clients.client_nav')

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="t_container">


            <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
                <div class="card-header" style="background-color: #f8f9fa;">
                    <h3 class="card-title"></i> {{trans('clients.client_unpaid_invoices')}}</h3>
                    <div class="card-toolbar">
                        <div class="text-center">
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding-left: 0px !important;">
                    <div class="col-md-12 row">
                        <div class="col-md-8">
                            @can('view_client_unpaid_invoices')
                                @include('dashbord.clients.client_unpaid_invoices_data')
                            @endcan
                        </div>
                        <div class="col-md-4">
                            @include('dashbord.clients.client_details')

                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

    </div>

    <div class="modal fade" tabindex="-1" id="modaldetails">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><?= trans('invoices.invoice_details') ?></h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1">&times;</i>
                    </div>

                </div>

                <div id="result_info">

                </div>

            </div>
        </div>
    </div>


@endsection

@section('js')


    @notifyJs

@endsection



