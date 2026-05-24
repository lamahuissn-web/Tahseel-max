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
                <div class="card-title fs-3 fw-bold">{{trans('employees.employee_transactions')}}</div>
            </div>
            <div class="card" style="margin-top:10px">
                <div class="" style="margin-top: 30px;padding: 30px;">
                    @if(isset($transactions) && !empty($transactions))
                    <table id="table_10" class="table table-bordered responsive nowrap text-center" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr class="greentd" style="background-color: lightgrey">
                                <th>{{trans('accounts.hash') }}</th>
                                <th>{{ trans('accounts.amount') }}</th>
                                <th>{{ trans('accounts.account') }}</th>
                                <th>{{ trans('accounts.date') }}</th>
                                <th>{{ trans('accounts.time') }}</th>
                                <th>{{ trans('accounts.type') }}</th>
                                <th>{{ trans('accounts.notes') }}</th>
                              
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $x = 1;
                            @endphp
                            @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{ $x++ }}</td>
                                <td data-order="{{ $transaction->amount }}">{{ number_format($transaction->amount, 2) }}</td>
                                <td>{{ @$transaction->account->name }}</td>
                                <td>{{ $transaction->date}}</td>
                                <td>{{ $transaction->time}}</td>
                                <td>{{ $transaction->type ='qabd' ? 'قبض' : 'صرف'}}</td>
                                <td class="fnt_center_blue">{{ $transaction->notes ?? 'N\A' }}</td>

                            </tr>
                            @endforeach
                        </tbody>
                      
                    </table>
                    @endif
                </div>





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