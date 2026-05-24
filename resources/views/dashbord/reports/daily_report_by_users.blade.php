@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('reports.daily_report_by_users') ?? 'تقرير يومي بالمستخدمين';
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.reports'), 'link' => ''],
                ['label' => $title, 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp
    </div>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-xxxl">
        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="date" class="form-label">التاريخ</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('date') !!}</span>
                            <input type="date" class="form-control" name="date" id="date"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="search_btn">
                            <i class="bi bi-search"></i> بحث
                        </button>
                    </div>
                </div>

                <div class="row mb-4" id="totals_section" style="display: none;">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end gap-3">
                            <div class="bg-primary bg-opacity-10 rounded p-3 d-inline-block">
                                <span class="text-primary fw-bold">عدد المستخدمين: </span>
                                <span class="text-primary fw-bold" id="users_count">0</span>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded p-3 d-inline-block">
                                <span class="text-success fw-bold">إجمالي الفواتير: </span>
                                <span class="text-success fw-bold" id="total_invoices">0</span>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded p-3 d-inline-block">
                                <span class="text-info fw-bold">إجمالي المبلغ: </span>
                                <span class="text-info fw-bold" id="total_amount">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" style="border-top: 3px solid #28a745;">
            <div class="card-header">
                <h3 class="card-title">تقرير يومي بالمستخدمين</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="users_report_table">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">اسم المستخدم</th>
                                <th class="text-center">عدد الفواتير</th>
                                <th class="text-center">إجمالي المبلغ</th>
                            </tr>
                        </thead>
                        <tbody id="report_tbody">
                            <tr>
                                <td colspan="4" class="text-center">اختر تاريخ للبحث</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#search_btn').on('click', function() {
                loadDailyReport();
            });

            // Load report on page load with today's date
            loadDailyReport();

            function loadDailyReport() {
                const date = $('#date').val();
                
                if (!date) {
                    alert('الرجاء اختيار التاريخ');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.reports.daily_by_users_data') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        date: date
                    },
                    success: function(response) {
                        displayReport(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        alert('حدث خطأ أثناء تحميل التقرير');
                    }
                });
            }

            function displayReport(data) {
                const tbody = $('#report_tbody');
                tbody.empty();

                if (data.users && data.users.length > 0) {
                    data.users.forEach(function(user, index) {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td class="fw-bold">${user.user_name}</td>
                                <td class="text-center">${user.invoices_count}</td>
                                <td class="text-center fw-bold text-success">${parseFloat(user.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="4" class="text-center">لا توجد بيانات لهذا التاريخ</td></tr>');
                }

                // Update totals
                if (data.totals) {
                    $('#users_count').text(data.totals.users_count || 0);
                    $('#total_invoices').text(data.totals.total_invoices || 0);
                    $('#total_amount').text(parseFloat(data.totals.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#totals_section').show();
                }
            }
        });
    </script>
@endsection

