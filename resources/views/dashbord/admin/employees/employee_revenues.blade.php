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
                @include('dashbord.admin.employees.employee_revenues_data')
            </div>

        </div>


    </div>

</div>




@endsection


<script>
    window.invoice_details = function(url) {
        $.get(url, function(data) {
            $('#result_info').html(data);
            var modalElement = document.getElementById('modaldetails');
            if (window.bootstrap && bootstrap.Modal) {
                var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
                $('#modaldetails').modal('show');
            } else {
                modalElement.classList.add('show');
                modalElement.style.display = 'block';
                modalElement.removeAttribute('aria-hidden');
                modalElement.setAttribute('aria-modal', 'true');
            }
        });
    }

    
</script>
@notifyJs

