@extends('dashbord.layouts.master')
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('client.import_clients');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.clients.index')],
                ['label' => trans('Toolbar.clients'), 'link' => route('admin.clients.index')],
                ['label' => trans('client.import_clients'), 'link' => ''],
            ];
            PageTitle($title, $breadcrumbs);
        @endphp

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            {{ BackButton(route('admin.clients.index')) }}
        </div>
    </div>
@endsection

@section('content')
    <div id="kt_app_content_container" class="t_container">
        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                generateCardHeader('clients.import_clients', 'admin.clients.index', ' ');
            @endphp

            <form action="{{ route('admin.clients.import') }}" method="post" enctype="multipart/form-data" id="import_form">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">{{ trans('clients.import_file') }}</label>
                                <input type="file" class="form-control" name="file" id="file" required>
                                <small class="form-text text-muted">
                                    {{ trans('clients.import_file_help') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subscription_date">{{ trans('clients.subscription_date') }}</label>
                                <input type="date" class="form-control" name="subscription_date" id="subscription_date" required value="{{ old('subscription_date', date('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>{{ trans('clients.import_template_guide') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ trans('clients.name') }}</th>
                                        <th>{{ trans('clients.month_subscription') }}</th>
                                        <th>{{ trans('clients.end_date') }}</th>
                                        <th>{{ trans('clients.notes') }}</th>
                                        <th>{{ trans('clients.subscription_type') }}</th>
                                        <th>{{ trans('clients.address1') }}</th>
                                        <th>{{ trans('clients.is_active') }}</th>
                                        <th>{{ trans('clients.type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>اسم العميل</td>
                                        <td>25</td>
                                        <td>2025-07-19</td>
                                        <td>ملاحظات</td>
                                        <td>10M 25$</td>
                                        <td>العنوان</td>
                                        <td>فعال</td>
                                        <td>انترنت</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> {{ trans('clients.import') }}
                    </button>
                </div>
            </form>
        </div>

        @if(isset($failures) && count($failures) > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('clients.import_errors') }}</h4>
                    <small>{{ trans('clients.import_success_count', ['count' => $successCount]) }}</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ trans('clients.row') }}</th>
                                    <th>{{ trans('clients.errors') }}</th>
                                    <th>{{ trans('clients.data') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failures as $failure)
                                    <tr>
                                        <td>{{ $failure['row'] }}</td>
                                        <td>
                                            <ul class="text-danger">
                                                @foreach($failure['errors'] as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>
                                            <pre>{{ json_encode($failure['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('js')
    {!! JsValidator::formRequest('App\Http\Requests\Admin\clients\ImportClientsRequest', '#import_form') !!}
@endsection
