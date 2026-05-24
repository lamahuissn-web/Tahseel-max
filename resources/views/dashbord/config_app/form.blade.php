@extends('dashbord.layouts.master')
@section('css')
    @notifyCss
@endsection
@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid" >
        <div class="row col-md-12">
            <div class="col-md-3">
                @include('dashbord.admin.settings.sidebar')
            </div>
            <div class="col-md-9">
                <div id="kt_app_content" class="app-content flex-column-fluid" >
                    <div id="kt_app_content_container" class="" style="padding-top: 20px" >
                        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
                            <div class="card-header">
                                <h3 class="card-title">{{ trans('settings.app_config') }}</h3>
                            </div>

                            <div class="card-body">
                                <form action="{{route('admin.save_app_config')}}" method="POST">
                                    @csrf
                                    @method('POST')

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="phone_service" class="form-label">{{ trans('settings.phone_service') }}</label>
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text" id="basic-addon3">{!! form_icon('phone') !!}</span>
                                                <input type="tel" class="form-control" name="phone_service" id="phone_service" value="{{ old('phone_service', $all_data['phone_service'] ?? '') }}" >
                                            </div>
                                            @error('phone_service')
                                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="currency" class="form-label">{{ trans('settings.currency') }}</label>
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text" id="basic-addon3">{!! form_icon('price') !!}</span>
                                                <input type="text" class="form-control" name="currency" id="currency" value="{{ old('phone_service', $all_data['currency'] ?? '') }}" >
                                            </div>
                                            @error('currency')
                                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    <h5 class="mb-3">إعدادات النسخ الاحتياطي والتصدير</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="auto_backup_enabled" class="form-label">تفعيل النسخ الاحتياطي التلقائي</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="auto_backup_enabled" id="auto_backup_enabled" value="1" {{ old('auto_backup_enabled', $all_data['auto_backup_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="auto_backup_enabled">
                                                    تفعيل النسخ الاحتياطي التلقائي
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="backup_frequency" class="form-label">تردد النسخ الاحتياطي</label>
                                            <select class="form-select" name="backup_frequency" id="backup_frequency">
                                                <option value="daily" {{ old('backup_frequency', $all_data['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>يومي</option>
                                                <option value="weekly" {{ old('backup_frequency', $all_data['backup_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                                <option value="monthly" {{ old('backup_frequency', $all_data['backup_frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>شهري</option>
                                            </select>
                                            @error('backup_frequency')
                                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <label for="backup_path" class="form-label">مسار حفظ ملفات النسخ الاحتياطي</label>
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text" id="basic-addon3">{!! form_icon('folder') !!}</span>
                                                <input type="text" class="form-control" name="backup_path" id="backup_path" value="{{ old('backup_path', $all_data['backup_path'] ?? storage_path('app/backups')) }}" placeholder="مثال: C:\backups أو /var/backups">
                                            </div>
                                            <small class="form-text text-muted">اتركه فارغاً لاستخدام المسار الافتراضي: storage/app/backups</small>
                                            @error('backup_path')
                                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                    <div class="card-footer d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success">
                                            {{ trans('company.save') }}
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection
@section('js')


    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>

@endsection
