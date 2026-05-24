@extends('dashbord.layouts.master')

@section('css')
    @notifyCss
@endsection

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('Toolbar.clients')}}</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">

                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                        {{trans('Toolbar.home')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.clients')}}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.add_invoice')}}
                </li>


            </ul>

        </div>

    </div>

@endsection

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">

        <div id="kt_app_content_container" class="app-container container-xxl">

            <div class="card mb-6 mb-xl-9">
                <div class="card-body pt-9 pb-0">

                    @include('dashbord.clients.client_details')
                    @include('dashbord.clients.client_nav')

                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title fs-3 fw-bold">{{trans('clients.add_invoice')}}</div>
                </div>

            </div>

            <div class="card" style="margin-top:10px">
                @include('dashbord.clients.add_invoice.add_invoice_form')
            </div>

        </div>

    </div>
@endsection


@section('js')
    <script>
        $(document).ready(function() {
            toggleSubscription();
            setTimeout(function() {
                $("#subscription_id").trigger("change");
            }, 300);
        });

        function toggleSubscription() {
            let invoiceType = document.getElementById("invoice_type").value;
            let subscriptionSection = document.getElementById("subscription_section");
            let amountField = document.getElementById("amount");

            if (invoiceType === "subscription") {
                subscriptionSection.style.display = "block";
                amountField.readOnly = true;
                amountField.value = '';
            } else {
                subscriptionSection.style.display = "none";
                amountField.readOnly = false;
                amountField.value = '';
            }
        }
    </script>
    <script>
        function get_price(id) {
            if (!id) {
                document.getElementById("amount").value = "";
                return;
            }

            $.ajax({
                url: "{{ route('admin.get_price', ['id' => '__id__']) }}".replace('__id__', id),
                type: "get",
                dataType: "json",
                success: function(data) {
                    $('#amount').val(data.price || '<?= old('amount') ?>');
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching price:", status, error);
                    $('#amount').val('0');
                }
            });
        }
    </script>
@endsection
